<?php namespace Indikator\Popup\Models;

use Model;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use System\Classes\PluginManager;

class ABtest extends Model
{
    use \October\Rain\Database\Traits\Validation;

    protected $table = 'indikator_popup_abtest';

    public $rules = [
        'campaign' => 'required|numeric',
        'theme_a'  => 'numeric',
        'theme_b'  => 'numeric',
        'code_a'   => 'unique:indikator_popup_abtest',
        'code_b'   => 'unique:indikator_popup_abtest',
        'status'   => 'required|between:1,2|numeric'
    ];

    public function getCampaignOptions()
    {
        $result = [];
        $sql    = Campaigns::orderBy('name', 'asc')->get()->all();

        foreach ($sql as $item) {
            $result[$item->id] = $item->name;
        }

        return $result;
    }

    public function getThemeAOptions()
    {
        $result = [0 => trans('indikator.popup::lang.form.none')];
        $sql    = Themes::where('status', 1)->orderBy('name', 'asc')->get()->all();

        foreach ($sql as $item) {
            $result[$item->id] = $item->name;
        }

        return $result;
    }

    public function getThemeBOptions()
    {
        $result = [0 => trans('indikator.popup::lang.form.none')];
        $sql    = Themes::where('status', 1)->orderBy('name', 'asc')->get()->all();

        foreach ($sql as $item) {
            $result[$item->id] = $item->name;
        }

        return $result;
    }
}
