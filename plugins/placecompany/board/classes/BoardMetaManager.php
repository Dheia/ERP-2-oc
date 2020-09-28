<?php namespace Placecompany\Board\Classes;

use Placecompany\Board\Models\BoardContent;
use Placecompany\Board\Models\BoardMeta;
use Placecompany\Board\Models\BoardSetting;
use stdClass;

/**
 * KBoard 워드프레스 게시판 스킨
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardMetaManager {


    private $board_id;
    private $meta;

    public function __construct($board_id=''){
        $this->meta = new stdClass();
        $this->board_id = 0;
        if($board_id) $this->setBoardID($board_id);
    }

    public function __get($name){
        $name = strtolower($name);
        $name = preg_replace('/[^a-z0-9_\-]/', '', $name);

        if(isset($this->meta->{$name})){
            return $this->meta->{$name};
        }
        return '';
    }

    public function __set($name, $value){
        if($this->board_id){
            $name = strtolower($name);
            $name = preg_replace('/[^a-z0-9_\-]/', '', $name);
            $value = e($value);

            if($value){
                $board_meta = BoardMeta::firstOrCreate(['board_id' => $this->board_id, 'key' => $name], ['board_id' => $this->board_id, 'key' => $name, 'value' => $value]);
                $board_meta->value = $value;
                $board_meta->save();
            }
            else{
                BoardMeta::where('board_id', $this->board_id)->where('key', $name)->delete();
            }
            $this->meta->{$name} = $value;
        }
    }

    public function __isset($name)
    {
        $name = strtolower($name);
        $name = preg_replace('/[^a-z0-9_\-]/', '', $name);

        if(property_exists($this->meta, $name)){
            return true;
        }

        return false;
    }

    /**
     * 게시판 아이디를 입력받는다.
     * @param int $board_id
     */
    public function setBoardID($board_id){
        $this->meta = new stdClass();
        $this->board_id = 0;
        if(is_array($board_id)){
            $board_id = implode(',', $board_id);
            $results = BoardMeta::whereIn('board_id', $board_id)->where(function ($q) {
                return $q->where('key', 'comments_plugin_id')
                    ->orWhere('key', 'use_comments_plugin');
            })->get();
            foreach($results as $row){
                $this->meta->{$row->key} = $row->value;
            }
        }
        else{
            $this->board_id = intval($board_id);
            $results = BoardMeta::where('board_id', $this->board_id)->get();
            foreach($results as $row){
                $this->meta->{$row->key} = $row->value;
            }
        }
    }

    /**
     * 게시판 메타데이터를 등록한다.
     * @param BoardSetting $board_setting
     * @param string $key
     * @param string $value
     * @return null
     */
    public static function registerMeta(BoardSetting $board_setting, $key, $value)
    {
        $board_meta = BoardMeta::firstOrCreate(['board_id' => $board_setting->id, 'key' => $key], ['board_id' => $board_setting->id, 'key' => $key, 'value' => $value]);;
        $board_meta->board_id = $board_setting->id;
        $board_meta->key = $key;
        $board_meta->value = $value;
        $board_meta->save();

        return null;
    }

    /**
     * 게시판 메타데이터 배열을 등록한다.
     * @param BoardSetting $board
     * @param array $meta_array
     * @return null
     */
    public static function registerMetaList(BoardSetting $board, $meta_array)
    {
        foreach ($meta_array as $key => $value) {
            static::registerMeta($board, $key, $value);
        }

        return null;
    }
}
