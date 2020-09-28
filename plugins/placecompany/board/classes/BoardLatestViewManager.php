<?php namespace Placecompany\Board\Classes;

use Placecompany\Board\Models\BoardLatestView;
use Placecompany\Board\Models\BoardLatestViewLink;

/**
 * board 최신글 모아보기
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardLatestViewManager {

    var $row;

    public function __construct($id=''){
        $this->row = new \stdClass();
        if($id) $this->initWithID($id);
    }

    public function __get($name){
        if(isset($this->row->{$name})){
            if($name == 'sort' && !$this->row->{$name}) return 'newest';
            return stripslashes($this->row->{$name});
        }
        return '';
    }

    public function __set($name, $value){
        $this->row->{$name} = $value;
    }

    /**
     * 고유번호로 초기화 한다.
     * @param int $id
     * @return BoardLatestViewManager
     */
    public function initWithID($id){
        $id = intval($id);
        $this->row = BoardLatestView::find($id);
        return $this;
    }

    /**
     * 값을 입력받으 초기화 한다.
     * @param object $row
     */
    public function initWithRow($row){
        $this->row = $row;
        return $this;
    }

    /**
     * 모아보기를 생성한다.
     */
    public function create(){
        $latest_view = new BoardLatestView;
        $latest_view->name = '';
        $latest_view->skin = '';
        $latest_view->rpp = '';
        $latest_view->sort = '';
        $latest_view->save();
        $this->id = $latest_view->id;
        return $this->id;
    }

    /**
     * 모아보기 정보를 수정한다.
     */
    public function update(){
        if($this->id){
            foreach($this->row as $key=>$value){
                if($key != 'id'){
                    $key = Helpers::sanitize_key($key);
                    $value = e($value);
                    $data[] = "`$key`='$value'";
                }
            }
            if($data) \DB::table('placecompany_board_latestview')->where('id', $this->id)->updateRaw(implode(',', $data));
        }
    }

    /**
     * 모아보기 정보를 삭제한다.
     */
    public function delete(){
        if($this->id){
            BoardLatestView::find($this->id)->delete();
        }
    }

    /**
     * 모아볼 게시판을 추가한다.
     * @param int $board_id
     */
    public function pushBoard($board_id){
        $board_id = intval($board_id);
        if($this->id && !$this->isLinked($board_id)){
            $board_latest_view_link = new BoardLatestViewLink;
            $board_latest_view_link->latestview_id = $this->id;
            $board_latest_view_link->board_id = $board_id;
            $board_latest_view_link->save();
        }
    }

    /**
     * 게시판을 제거한다.
     * @param int $board_id
     */
    public function popBoard($board_id){
        $board_id = intval($board_id);
        if($this->id){
            BoardLatestViewLink::find(['latestview_id' => $this->id, 'board_id' => $board_id])->delete();
        }
    }

    /**
     * 모아볼 게시판들을 반환한다.
     */
    public function getLinkedBoard(){
        if($this->id){
            $result = BoardLatestViewLink::where('latestview_id', $this->id)->get();
            foreach($result as $row){
                $list[] = $row->board_id;
            }
        }
        return isset($list)?$list:array();
    }

    /**
     * 연결된 게시판인지 확인한다.
     * @return boolean
     */
    public function isLinked($board_id){
        $board_id = intval($board_id);
        if($this->id){
            $count = BoardLatestViewLink::where(['latestview_id' => $this->id, 'board_id' => $board_id])->count();
            if(intval($count)){
                return true;
            }
        }
        return false;
    }
}
?>
