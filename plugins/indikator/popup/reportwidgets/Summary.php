<?php namespace Indikator\Popup\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Exception;
use Indikator\Popup\Models\Campaigns;
use Indikator\Popup\Models\ABtest;
use Indikator\Popup\Models\Themes;

class Summary extends ReportWidgetBase
{
    public function render()
    {
        try {
            $this->loadData();
        }
        catch (Exception $ex) {
            $this->vars['error'] = $ex->getMessage();
        }

        return $this->makePartial('widget');
    }

    public function defineProperties()
    {
        return [
            'title' => [
                'title'             => 'backend::lang.dashboard.widget_title_label',
                'default'           => 'indikator.popup::lang.widget.summary',
                'type'              => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error'
            ],
            'show_campaigns' => [
                'title'   => 'indikator.popup::lang.widget.show_campaigns',
                'default' => true,
                'type'    => 'checkbox'
            ],
            'show_abtest' => [
                'title'   => 'indikator.popup::lang.widget.show_abtest',
                'default' => true,
                'type'    => 'checkbox'
            ],
            'show_themes' => [
                'title'   => 'indikator.popup::lang.widget.show_themes',
                'default' => true,
                'type'    => 'checkbox'
            ]
        ];
    }

    protected function loadData()
    {
        $this->vars['campaigns'] = Campaigns::count();
        $this->vars['abtest']    = ABtest::count();
        $this->vars['themes']    = Themes::count();
    }
}
