<?php

App::before(function($request)
{
    // Backend URL
    Route::group(['prefix' => Config::get('cms.backendUri', 'backend')], function()
    {
        // Statistics
        Route::any('indikator/popup/stat', function()
        {
            // Is admin
            if (BackendAuth::check()) {
                return;
            }

            // Campaign's ID
            $getCampaign = get('campaign');

            // Security check
            if (!is_numeric($getCampaign) || \Indikator\Popup\Models\Campaigns::where('id', $getCampaign)->where('status', '!=', 2)->count() == 0) {
                return;
            }

            // Campaign
            $campaign = \Indikator\Popup\Models\Campaigns::whereId($getCampaign)->first();

            // Date range
            if (($campaign->start_at != null && $campaign->start_at > date('Y-m-d H:i:s')) || ($campaign->end_at != null && $campaign->end_at < date('Y-m-d H:i:s'))) {
                return;
            }

            // Today
            $date = [
                'year'  => date('y'),
                'month' => date('n'),
                'day'   => date('j')
            ];

            // Create
            if (\Indikator\Popup\Models\Reports::where(['year' => $date['year'], 'month' => $date['month'], 'day' => $date['day'], 'campaign' => $campaign->id])->count() == 0) {
                \Indikator\Popup\Models\Reports::insertGetId([
                    'year'       => $date['year'],
                    'month'      => $date['month'],
                    'day'        => $date['day'],
                    'campaign'   => $campaign->id,
                    'view'       => 1,
                    'action'     => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Update
            else {
                \Indikator\Popup\Models\Reports::where(['year' => $date['year'], 'month' => $date['month'], 'day' => $date['day'], 'campaign' => $campaign->id])->increment('view');
            }

            // A/B test type
            $getAbtest = get('abtest');

            // Is active
            if ($getAbtest == 'a' || $getAbtest == 'b') {
                // Security check
                if (\Indikator\Popup\Models\ABtest::where(['campaign' => $campaign->id, 'status' => 1])->count() == 0) {
                    return;
                }

                // A/B test
                $abtest = \Indikator\Popup\Models\ABtest::where('campaign', $campaign->id)->first();

                // Values
                if ($getAbtest == 'a') {
                    $view = 'view_a';
                    $view_a = 1;
                    $view_b = 0;
                }
                else {
                    $view = 'view_b';
                    $view_a = 0;
                    $view_b = 1;
                }

                // Create
                if (\Indikator\Popup\Models\ABtestReports::where(['year' => $date['year'], 'month' => $date['month'], 'day' => $date['day'], 'abtest' => $abtest->id])->count() == 0) {
                    \Indikator\Popup\Models\ABtestReports::insertGetId([
                        'year'       => $date['year'],
                        'month'      => $date['month'],
                        'day'        => $date['day'],
                        'abtest'     => $abtest->id,
                        'view_a'     => $view_a,
                        'view_b'     => $view_b,
                        'action_a'   => 0,
                        'action_b'   => 0,
                        'code_a'     => 0,
                        'code_b'     => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }

                // Update
                else {
                    \Indikator\Popup\Models\ABtestReports::where(['year' => $date['year'], 'month' => $date['month'], 'day' => $date['day'], 'abtest' => $abtest->id])->increment($view);
                }
            }
        });
    });
});
