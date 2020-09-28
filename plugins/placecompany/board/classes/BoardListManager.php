<?php namespace Placecompany\Board\Classes;
use Placecompany\Board\Models\BoardMeta;
use Placecompany\Board\Models\BoardSetting;

/**
 * KBoard 게시판 리스트
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardListManager {

    var $resource;
    var $rpp = 10;
    var $page = 1;
    var $total;
    var $row;

    /**
     * 게시판 리스트를 초기화한다.
     * @return BoardListManager
     */
    public function init(){
        $this->total = BoardSetting::all()->count();
        $this->resource = BoardSetting::all();
        $this->resource = $this->resource->getIterator();
        return $this;
    }

    /**
     * 게시판 이름을 검색해 리스트를 초기화한다.
     * @param string $keyword
     * @return BoardListManager
     */
    public function initWithKeyword($keyword=''){
        if($keyword){
            $keyword = e($keyword);
            $where = "`board_name` LIKE '%$keyword%'";
        }
        else{
            $where = '1=1';
        }
        $this->total = \DB::table('placecompany_board_setting')->whereRaw($where)->count();
        $this->resource = \DB::table('placecompany_board_setting')->whereRaw($where)->orderBy('id', 'desc')->offset(($this->page-1)*$this->rpp)->limit($this->rpp)->get();
        return $this;
    }

    /**
     * 생성된 게시판 숫자를 반환한다.
     * @return int
     */
    public function getCount(){
        return $this->total;
    }

    /**
     * 다음 게시판 정보를 불러온다.
     * @return object|string
     */
    public function hasNext(){
        if(!$this->resource) return '';
        $this->row = current($this->resource);
        if($this->row){
            next($this->resource);
            $board = new BoardManager();
            $board->initWithRow($this->row);

            return $board;
        }
        else{
            unset($this->resource);
            return '';
        }
    }

    /**
     * 관리자 페이지에서 게시판 보기 리스트를 반환한다.
     * @return array
     */
    public function getActiveAdmin(){
        $results = BoardMeta::select('board_id')->where('key', 'add_menu_page')->get();
        foreach($results as $row){
            $active[] = $row->board_id;
        }
        return isset($active) ? $active : array();
    }
}
?>
