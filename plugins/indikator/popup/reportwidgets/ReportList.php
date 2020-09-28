<?php namespace Indikator\Popup\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Exception;
use Indikator\Popup\Models\Campaigns;
use Indikator\Popup\Models\Reports;

class ReportList extends ReportWidgetBase
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
                'default'           => 'indikator.popup::lang.widget.list_title',
                'type'              => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error'
            ],
            'active' => [
                'title'   => 'indikator.popup::lang.widget.active',
                'default' => true,
                'type'    => 'checkbox'
            ]
        ];
    }

    protected function loadData()
    {
        if ($this->property('active')) {
            $campaigns = Campaigns::where('status', 1)->orderBy('created_at', 'desc')->get()->all();
        }
        else {
            $campaigns = Campaigns::orderBy('created_at', 'desc')->get()->all();
        }

        $this->vars['list'] = [];

        foreach ($campaigns as $campaign) {
            $view   = Reports::where('campaign', $campaign->id)->sum('view');
            $action = Reports::where('campaign', $campaign->id)->sum('action');

            if ($action == 0) {
                $rate = 0;
            }
            else {
                $rate = 100 / ($view / $action);
            }

            $this->vars['list'][$campaign->name] = round($rate, 1);
        }
    }
}
