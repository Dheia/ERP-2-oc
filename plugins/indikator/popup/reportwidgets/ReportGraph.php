<?php namespace Indikator\Popup\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Exception;
use Indikator\Popup\Models\Campaigns;
use Indikator\Popup\Models\Reports;

class ReportGraph extends ReportWidgetBase
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
                'default'           => 'indikator.popup::lang.widget.graph_title',
                'type'              => 'string',
                'validationPattern' => '^.+$',
                'validationMessage' => 'backend::lang.dashboard.widget_title_error'
            ],
            'campaign' => [
                'title'   => 'indikator.popup::lang.widget.campaign',
                'default' => 0,
                'type'    => 'dropdown'
            ]
        ];
    }

    public function getCampaignOptions()
    {
        $result = [0 => e(trans('indikator.popup::lang.widget.select'))];

        foreach (Campaigns::orderBy('created_at', 'desc')->get()->all() as $item) {
            $result[$item->id] = $item->name;
        }

        return $result;
    }

    protected function loadData()
    {
        if ($this->property('campaign') == 0 || Campaigns::where('id', $this->property('campaign'))->count() == 0) {
            $this->vars['show'] = false;
        }
        else {
            $this->vars['show'] = true;

            $campaign = Campaigns::where('id', $this->property('campaign'))->first();

            // Status
            if ($campaign->status == 1) {
                $campaign->status_icon = 'text-info';
            }
            else if ($campaign->status == 3) {
                $campaign->status_icon = 'text-warning';
            }
            else {
                $campaign->status_icon = 'text-danger';
            }

            // Statistics
            $stat_action = $stat_view = '';

            for ($i = 0; $i < 20; $i++) {
                if ($i == 0) {
                    $date = time();
                }
                else if ($i == 1) {
                    $date = strtotime('-1 day');
                }
                else {
                    $date = strtotime('-'.$i.' days');
                }

                $report = Reports::where('campaign', $campaign->id)->where('year', date('y', $date))->where('month', date('n', $date))->where('day', date('j', $date))->first();

                if (!isset($report['action'])) {
                    $report['action'] = 0;
                }

                if (!isset($report['view'])) {
                    $report['view'] = 0;
                }

                $stat_action .= '['.$date.'000,'.$report['action'].'],';
                $stat_view   .= '['.$date.'000,'.$report['view'].'],';
            }

            $campaign->stat_action = substr($stat_action, 0, -1);
            $campaign->stat_view   = substr($stat_view, 0, -1);

            $this->vars['campaign'] = $campaign;
        }
    }
}
