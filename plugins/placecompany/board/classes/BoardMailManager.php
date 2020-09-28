<?php namespace Placecompany\Board\Classes;
use Backend\Models\BrandSetting;
use Illuminate\Support\Facades\Mail;

/**
 * KBoard 메일
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardMailManager {

    var $headers;
    var $from_name;
    var $from;
    var $to;
    var $title;
    var $content;
    var $url;
    var $url_name;
    var $attachments = array();

    public function __construct(){
        $this->from_name = BrandSetting::get('app_name');
        $this->from = \App::make('config')->get('mail.from.address', 'admin@domain.tld');;
    }

    public function send(){
        $content_dir_name = base_path();
        $board_attched_dir = "{$content_dir_name}/uploads/board_attached";

        $message = Helpers::board_content_paragraph_breaks($this->content);
        $message = str_replace($board_attched_dir, url($board_attched_dir), $message);
        $message = str_replace('<p>', "<p style=\"font-family: 'Apple SD Gothic Neo','Malgun Gothic',arial,sans-serif; font-size: 14px; font-weight: normal; margin: 0; margin-bottom: 15px;\">", $message);

        $call_to_actions = array();
        if($this->url){
            $call_to_actions = array(
                ($this->url_name ? $this->url_name : $this->url) => e($this->url)
            );
        }

        $args = [
            'subject' => post('subject'),
            'message' => $message,
            'call_to_actions' => $call_to_actions
        ];

        $result = Mail::send('placecompany.board::mail.default', $args, function($message) {
            $message->to($this->from);
            $message->subject($this->title);
            foreach($this->attachments as $attachment) {
                $message->attach($attachment);
            }
        });

        return $result;
    }
}

