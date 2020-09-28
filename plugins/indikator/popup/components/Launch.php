<?php namespace Indikator\Popup\Components;

use Cms\Classes\ComponentBase;
use Indikator\Popup\Classes\BotDetection;
use Indikator\Popup\Classes\MobileDetect;
use Indikator\Popup\Models\Campaigns;
use Indikator\Popup\Models\Themes;
use Indikator\Popup\Models\Reports;
use Indikator\Popup\Models\ABtest;
use Indikator\Popup\Models\ABtestReports;
use Backend;
use BackendAuth;
use Request;
use Validator;
use ValidationException;
use System\Classes\PluginManager;
use Db;

class Launch extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'indikator.popup::lang.component.launch_name',
            'description' => 'indikator.popup::lang.component.launch_description'
        ];
    }

    public function defineProperties()
    {
        return [
            'showMobile' => [
                'title'   => 'indikator.popup::lang.component.launch_mobile',
                'type'    => 'checkbox',
                'default' => false
            ]
        ];
    }

    public function onRun()
    {
        // Hide the popup
        $detect = new MobileDetect;
        if ((!$this->property('showMobile') && $detect->isMobile()) || BotDetection::isRobot()) {
            $this->page['show'] = false;
            return;
        }

        // Today
        $date = [
            'year'  => date('y'),
            'month' => date('n'),
            'day'   => date('j')
        ];

        // Code statistics
        if (!BackendAuth::check()) {
            $uri = substr(str_replace(Request::url(), '', Request::fullUrl()), 1);

            // Special parameter
            if (substr_count($uri, 'abtest=') > 0) {
                if (substr_count($uri, '&') > 0) {
                    $params = explode('&', $uri);

                    foreach ($params as $values) {
                        if (substr_count($values, 'abtest=') > 0) {
                            $param = str_replace('abtest=', '', $values);
                            break;
                        }
                    }
                }
                else {
                    $param = str_replace('abtest=', '', $uri);
                }

                // Statistics
                if (ABtest::where('code_a', $param)->whereOr('code_b', $param)->count() == 1) {
                    $abtest = ABtest::where('code_a', $param)->whereOr('code_b', $param)->first();

                    if ($abtest->code_a == $param) {
                        $code = 'code_a';
                        $code_a = 1;
                        $code_b = 0;
                    }
                    else {
                        $code = 'code_b';
                        $code_a = 0;
                        $code_b = 1;
                    }

                    // Create
                    if (ABtestReports::where(['year' => $date['year'], 'month' => $date['month'], 'day' => $date['day'], 'abtest' => $abtest->id])->count() == 0) {
                        ABtestReports::insertGetId([
                            'year'       => $date['year'],
                            'month'      => $date['month'],
                            'day'        => $date['day'],
                            'abtest'     => $abtest->id,
                            'view_a'     => 0,
                            'view_b'     => 0,
                            'action_a'   => 0,
                            'action_b'   => 0,
                            'code_a'     => $code_a,
                            'code_b'     => $code_b,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }

                    // Update
                    else {
                        ABtestReports::where(['year' => $date['year'], 'month' => $date['month'], 'day' => $date['day'], 'abtest' => $abtest->id])->increment($code);
                    }
                }
            }
        }

        // Active popups
        $campaigns = Campaigns::where('status', '!=', 2)->orderBy('status', 'desc')->get()->all();

        // Path
        $url = '/'.Request::path();

        // Home page
        if ($url == '//') {
            $url = '/';
        }

        // Loop
        foreach ($campaigns as $campaign) {
            // Date range
            if (($campaign->start_at != null && $campaign->start_at > date('Y-m-d H:i:s')) || ($campaign->end_at != null && $campaign->end_at < date('Y-m-d H:i:s'))) {
                continue;
            }

            // Selected pages
            if (!$campaign->pages_all && (!$campaign->pages_selected || !in_array($url, $campaign->pages_selected))) {
                continue;
            }

            // Campaign
            $this->page['campaign'] = $campaign->id;

            // Test mode
            if ($campaign->status == 3) {
                $this->page['param'] = '';
            }

            // Active
            else {
                $this->page['param'] = ', maxamount:1, cookie:true';
            }

            // Theme
            if (ABtest::where(['campaign' => $campaign->id, 'status' => 1])->count() == 0) {
                $theme = Themes::whereId($campaign->theme)->first();
                $this->page['abtest'] = 'c';
            }
            else {
                // A/B test
                $abtest = ABtest::where(['campaign' => $campaign->id, 'status' => 1])->first();

                // No themes
                if ($abtest->theme_a == 0 && $abtest->theme_b == 0) {
                    $theme = Themes::whereId($campaign->theme)->first();
                    $this->page['abtest'] = 'c';
                }

                // Different themes
                else {
                    if (rand(1, 2) == 1) {
                        $theme = Themes::whereId($abtest->theme_a)->first();
                        $this->page['abtest'] = 'a';
                    }
                    else {
                        $theme = Themes::whereId($abtest->theme_b)->first();
                        $this->page['abtest'] = 'b';
                    }
                }
            }

            // No theme
            if (!$theme || !$theme->id) {
                $this->page['content'] = '';
                $this->page['speed']   = 400;
                $this->page['open']    = 0;
                break;
            }

            // Name of style
            $sName = [
                1 => 'popup',
                2 => 'slide'
            ];

            // Assets
            $this->addCss('/plugins/indikator/popup/assets/css/'.$sName[$theme->style].'.css');
            $this->addJs('/plugins/indikator/popup/assets/js/'.$sName[$theme->style].'.js');

            // Animations
            $aName = [
                1 => 'default',
                2 => 'horizontal',
                3 => '3drotate',
                4 => 'rotate',
                5 => 'vibration',
                6 => 'zoom'
            ];

            // Animation
            if ($theme->style == 1) {
                $this->addCss('/plugins/indikator/popup/assets/css/popup-'.$aName[$theme->animation].'.css');
            }

            // Own styles
            $this->addCss('/plugins/indikator/popup/assets/css/theme-'.$theme->id.'.css');

            // Parameters
            $this->page['show']    = true;
            $this->page['content'] = $theme->html;
            $this->page['speed']   = $theme->speed;
            $this->page['staturl'] = Backend::url('indikator/popup/stat');

            // Timing
            if ($theme->timing) {
                $this->page['open'] = $theme->open;
            }
            else {
                $this->page['open'] = 0;
            }

            // Exit
            return;
        }

        // No popup
        $this->page['show'] = false;
    }

    public function onPopup()
    {
        // Data
        $data = post();

        // Campaign ID
        if (!isset($data['campaign']) || !is_numeric($data['campaign']) || Campaigns::where('id', $data['campaign'])->where('status', '!=', 2)->count() == 0) {
            return;
        }

        // Validation
        $rules = [
            'name'       => 'between:2,64',
            'first_name' => 'between:1,32',
            'last_name'  => 'between:1,32',
            'email'      => 'required|email|between:8,64'
        ];

        $validation = Validator::make($data, $rules);
        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        // Today
        $date = [
            'year'  => date('y'),
            'month' => date('n'),
            'day'   => date('j')
        ];

        // Campaign details
        $campaign = Campaigns::where('id', $data['campaign'])->first();

        // Campaign Manager
        if ($campaign->integration == 2 && PluginManager::instance()->hasPlugin('Responsiv.Campaign')) {
            // Check duplicate
            if (\Responsiv\Campaign\Models\Subscriber::where('email', $data['email'])->count() == 0) {
                // Validation data
                if (!isset($data['first_name'])) {
                    $data['first_name'] = '';
                }

                if (isset($data['name'])) {
                    $data['first_name'] = $data['name'];
                }

                if (!isset($data['last_name'])) {
                    $data['last_name'] = '';
                }

                // Insert subscriber
                \Responsiv\Campaign\Models\Subscriber::insertGetId([
                    'first_name'           => $data['first_name'],
                    'last_name'            => $data['last_name'],
                    'email'                => $data['email'],
                    'unsubscribed_at'      => null,
                    'created_at'           => date('Y-m-d H:i:s'),
                    'updated_at'           => date('Y-m-d H:i:s'),
                    'created_ip_address'   => Request::ip(),
                    'confirmed_ip_address' => null,
                    'confirmed_at'         => null,
                    'message_type'         => 'html'
                ]);
            }

            // Insert list
            if ($campaign->campaign_list > 0) {
                $subscriber_id = \Responsiv\Campaign\Models\Subscriber::where('email', $data['email'])->value('id');

                if (Db::table('responsiv_campaign_lists_subscribers')->where(['list_id' => $campaign->campaign_list, 'subscriber_id' => $subscriber_id])->count() == 1) {
                    return;
                }

                Db::table('responsiv_campaign_lists_subscribers')->insertGetId([
                    'list_id'       => $campaign->campaign_list,
                    'subscriber_id' => $subscriber_id
                ]);
            }
            else {
                return;
            }

            // Statistics
            if (!BackendAuth::check()) {
                // Main
                Reports::where(['year' => $date['year'], 'month' => $date['month'], 'day' => $date['day'], 'campaign' => $date['campaign']])->increment('action');

                // A/B test
                if (isset($date['abtest']) && ($date['abtest'] == 'a' || $date['abtest'] == 'b')) {
                    ABtestReports::where(['year' => $date['year'], 'month' => $date['month'], 'day' => $date['day'], 'abtest' => $date['abtest']])->increment('action_'.$date['abtest']);
                }
            }
        }

        // News & Newsletter
        else if ($campaign->integration == 3 && PluginManager::instance()->hasPlugin('Indikator.News')) {
            // Check duplicate
            if (\Indikator\News\Models\Subscribers::where('email', $data['email'])->count() == 1) {
                return;
            }

            // Validation data
            if (!isset($data['name'])) {
                $data['name'] = '';
            }

            // Insert subscriber
            \Indikator\News\Models\Subscribers::insertGetId([
                'name'              => $data['name'],
                'email'             => $data['email'],
                'common'            => '',
                'created'           => 1,
                'statistics'        => 0,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
                'status'            => 3,
                'locale'            => 'en',
                'registered_at'     => null,
                'registered_ip'     => null,
                'confirmed_at'      => null,
                'confirmed_ip'      => null,
                'confirmation_hash' => null,
                'unsubscribed_at'   => null,
                'unsubscribed_ip'   => null
            ]);

            // Statistics
            if (!BackendAuth::check()) {
                // Main
                Reports::where(['year' => $date['year'], 'month' => $date['month'], 'day' => $date['day'], 'campaign' => $date['campaign']])->increment('action');

                // A/B test
                if (isset($date['abtest']) && ($date['abtest'] == 'a' || $date['abtest'] == 'b')) {
                    ABtestReports::where(['year' => $date['year'], 'month' => $date['month'], 'day' => $date['day'], 'abtest' => $date['abtest']])->increment('action_'.$date['abtest']);
                }
            }
        }
    }
}
