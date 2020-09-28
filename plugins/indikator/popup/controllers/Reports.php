<?php namespace Indikator\Popup\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Indikator\Popup\Models\Campaigns;
use Indikator\Popup\Models\Reports as Report;

class Reports extends Controller
{
    public $requiredPermissions = ['indikator.popup.reports'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Indikator.Popup', 'popup', 'reports');
    }

    public function index()
    {
        $this->pageTitle = 'indikator.popup::lang.menu.reports';

        $this->addCss('/plugins/indikator/popup/assets/css/statistics.css');

        $this->vars['campaigns'] = $this->getCampaigns();
        $this->vars['count']     = Campaigns::count();
    }

    public function getCampaigns()
    {
        $campaigns = Campaigns::orderBy('created_at', 'desc')->get();

        foreach ($campaigns as $campaign) {
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
                
                $report = Report::where('campaign', $campaign->id)->where('year', date('y', $date))->where('month', date('n', $date))->where('day', date('j', $date))->first();

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
        }

        return $campaigns;
    }
}
