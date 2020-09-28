<?php namespace Placecompany\BoardComment\Classes;

use Placecompany\Board\Classes\Helpers;
use Placecompany\BoardComment\Models\BoardComment;
use Placecompany\BoardComment\Models\BoardCommentOption;
use stdClass;

/**
 * KBoard 댓글 옵션
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */

class BoardCommentOptionManager
{

    private $comment_id;
    private $row;

    public function __construct($comment_id=''){
        $this->row = new stdClass();
        if($comment_id) $this->initWithCommentID($comment_id);
    }

    public function __get($key){
        $key = Helpers::sanitize_key($key);
        if(isset($this->row->{$key})){
            return $this->row->{$key};
        }
        return '';
    }

    public function __set($key, $value){
        if($this->comment_id){
            $key = Helpers::sanitize_key($key);
            $value = e($value);
            if($value){
                BoardCommentOption::create([
                    'comment_id' => $this->comment_id,
                    'option_key' => $key,
                    'option_value' => $value,
                ]);
                BoardCommentOption::firstOrCreate(['comment_id' => $this->board_id, 'key' => $key], ['comment_id' => $this->comment_id, 'key' => $key, 'value' => $value]);
            }
            else{
                BoardCommentOption::where('comment_id', $this->comment_id)->where('key', $key)->delete();
            }
            $this->row->{$key} = $value;
        }
    }

    public function initWithCommentID($comment_id){
        $this->row = new stdClass();
        $this->comment_id = intval($comment_id);
        $results = BoardCommentOption::where('comment_id', $this->comment_id)->get()->toArray();
        foreach($results as $row){
            $this->row->{$row->option_key} = $row->option_value;
        }
    }
}
