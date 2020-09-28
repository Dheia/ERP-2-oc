<?php namespace Indikator\Popup\Models;

use Model;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use System\Classes\PluginManager;

class Campaigns extends Model
{
    use \October\Rain\Database\Traits\Validation;

    protected $table = 'indikator_popup_campaigns';

    public $rules = [
        'name'   => ['required', 'unique:indikator_popup_campaigns'],
        'theme'  => 'numeric',
        'status' => 'required|between:1,3|numeric'
    ];

    protected $jsonable = [
        'pages_selected'
    ];

    protected $dates = [
        'start_at',
        'end_at'
    ];

    public function getPagesSelectedOptions()
    {
        $result = [];
        $pages  = Page::sortBy('baseFileName')->all();

        foreach ($pages as $page) {
            $result[$page->url] = $page->title;
        }

        $pluginManager = PluginManager::instance()->findByIdentifier('RainLab.Pages');
        if (!$pluginManager || $pluginManager->disabled) {
            return $result;
        }

        $pages = \RainLab\Pages\Classes\Page::listInTheme(Theme::getActiveTheme());

        foreach ($pages as $page) {
            if (array_key_exists('title', $page->viewBag)) {
                $result[$page->viewBag['url']] = $page->viewBag['title'];
            }
        }

        natsort($result);

        return $result;
    }

    public function getThemeOptions()
    {
        $result = [0 => trans('indikator.popup::lang.form.none')];
        $sql    = Themes::where('status', 1)->orderBy('name', 'asc')->get()->all();

        foreach ($sql as $item) {
            $result[$item->id] = $item->name;
        }

        return $result;
    }

    public function getCampaignListOptions()
    {
        $result        = [0 => trans('indikator.popup::lang.form.none')];
        $pluginManager = PluginManager::instance()->findByIdentifier('Responsiv.Campaign');

        if ($pluginManager && !$pluginManager->disabled) {
            $sql = \Responsiv\Campaign\Models\SubscriberList::orderBy('name', 'asc')->get()->all();

            foreach ($sql as $item) {
                $result[$item->id] = $item->name;
            }
        }

        return $result;
    }
}
