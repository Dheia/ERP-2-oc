<?php namespace Placecompany\Board\Classes;
use stdClass;

/**
 * KBoard 게시글 옵션
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardContentOption {

    private $content_id;
    private $row;

    public function __construct($content_id=''){
        $this->row = new stdClass();
        if($content_id) $this->initWithContentUID($content_id);
    }

    public function __get($key){
        $key = Helpers::sanitize_key($key);
        if(isset($this->row->{$key})){
            return $this->row->{$key};
        }
        return '';
    }

    public function __set($key, $value){
        if($this->content_id){
            $key = Helpers::sanitize_key($key);
            $this->row->{$key} = $value;
            if($value){
                $count = \DB::table('placecompany_board_option')->whereRaw("`content_id`='$this->content_id' AND `option_key`='$key'")->count();
                if(is_array($value)){
                    if($count){
                        \DB::table('placecompany_board_option')->whereRaw("`content_id`='$this->content_id' AND `option_key`='$key'")->delete();
                    }
                    foreach($value as $option){
                        \DB::table('placecompany_board_option')->insert(
                            [
                                'content_id' => $this->content_id,
                                'option_key' => $key,
                                'option_value' => $option,
                            ]
                        );
                    }
                }
                else{
                    if($count){
                        \DB::table('placecompany_board_option')->where('content_id', $this->content_id)->where('option_key', $key)
                            ->update(['option_value' => $value]);
                    }
                    else{
                        \DB::table('placecompany_board_option')
                            ->insert(
                                [
                                    'content_id' => $this->content_id,
                                    'option_key' => $key,
                                    'option_value' => $value
                                ]
                            );
                    }
                }
            }
            else{
                \DB::table('placecompany_board_option')->where('content_id', $this->content_id)->where('option_key', $key)->delete();
            }
        }
    }

    public function initWithContentUID($content_id){
        $this->row = new stdClass();
        $this->content_id = intval($content_id);
        $results = \DB::table('placecompany_board_option')->where('content_id', $this->content_id)->orderBy('id', 'asc')->get();

        $option_list = array();
        foreach($results as $row){
            if(!isset($option_list[$row->option_key])) $option_list[$row->option_key] = array();
            $option_list[$row->option_key][] = $row->option_value;
        }

        foreach($option_list as $option_key=>$option_value){
            if(count($option_value) > 1){
                $this->row->{$option_key} = $option_value;
            }
            else{
                $this->row->{$option_key} = $option_value[0];
            }
        }
    }

    public function toArray(){
        if($this->content_id){
            return get_object_vars($this->row);
        }
        return array();
    }
}
?>
