<?php namespace Placecompany\BoardComment\Classes;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cookie;
use Placecompany\Board\Classes\BoardManager;
use Placecompany\Board\Classes\SecurityHelpers;
use Placecompany\Board\Models\BoardContent;
use Placecompany\BoardComment\Models\BoardComment;

/**
 * KBoard 워드프레스 게시판 댓글 리스트
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardCommentListManager
{

    private $next_list_page = 1;

    var $board;
    var $total;
    var $content_id;
    var $parent_id;
    var $resource;
    var $row;
    var $sort = 'vote';
    var $order = 'DESC';
    var $rpp = 20;
    var $page = 1;

    public function __construct($content_id=''){
        $this->board = new BoardManager();

        if($this->getSorting() == 'best'){
            // 인기순서
            $this->sort = 'vote';
            $this->order = 'DESC';
        }
        else if($this->getSorting() == 'oldest'){
            // 작성순서
            $this->sort = 'created';
            $this->order = 'ASC';
        }
        else if($this->getSorting() == 'newest'){
            // 최신순서
            $this->sort = 'created';
            $this->order = 'DESC';
        }

        if($content_id) $this->setContentID($content_id);
    }

    /**
     * 댓글 목록을 초기화 한다.
     * @return BoardCommentListManager
     */
    public function init(){
        if($this->content_id){
            \Event::fire('placecompany.board.board_view_iframe', [$this->sort, $this->order, $this]);

            $this->resource = \DB::table('placecompany_board_comment')->where('content_id', $this->content_id)->where(function ($query) {
                $query->where('parent_id', '<=', 0)->orWhereNull('parent_id');
            })->orderBy($this->sort, $this->order)->get()->all();
        }
        else {
            // 전체 댓글을 불러올땐 최신순서로 정렬한다.
            $this->sort = 'created';
            $this->order = 'DESC';
            $this->resource = BoardComment::orderBy($this->sort, $this->order)->offset(($this->page - 1) * $this->rpp)->limit($this->rpp)->get()->all();
        }
        return $this;
    }

    /**
     * 고유번호로 댓글 목록을 초기화 한다.
     * @param int $content_id
     * @return BoardCommentListManager
     */
    public function initWithID($content_id){
        $this->setContentID($content_id);
        $this->init();
        return $this;
    }

    /**
     * 부모 고유번호로 초기화 한다.
     * @param int $parent_id
     * @return BoardCommentListManager
     */
    public function initWithParentID($parent_id){
        $this->parent_id = $parent_id;

        \Event::fire('placecompany.board.board_view_iframe', [$this->sort, $this->order, $this]);

        $this->resource = BoardComment::where('parent_id', $this->parent_id)->orderBy($this->sort, $this->order)->get();
        $this->total = BoardComment::where('parent_id', $this->parent_id)->count();
        return $this;
    }

    /**
     * 한 페이지에 표시될 댓글 개수를 입력한다.
     * @param int $rpp
     * @return BoardCommentListManager
     */
    public function rpp($rpp){
        $rpp = intval($rpp);
        if($rpp <= 0){
            $this->rpp = 10;
        }
        else{
            $this->rpp = $rpp;
        }
        return $this;
    }

    /**
     * 댓글을 검색해 리스트를 초기화한다.
     * @param string $keyword
     * @return BoardCommentListManager
     */
    public function initWithKeyword($keyword=''){
        if($keyword){
            $keyword = e($keyword);
            $where = "`content` LIKE '%$keyword%'";
        }
        else{
            $where = '1=1';
        }

        $offset = ($this->page-1)*$this->rpp;

        $results = BoardComment::whereRaw($where)->orderBy('id', 'desc')->offset($offset)->limit($this->rpp)->get();
        foreach($results as $row){
            $select_id[] = intval($row->id);
        }

        if(!isset($select_id)){
            $this->total = 0;
            $this->resource = array();
        }
        else{
            $this->total = BoardComment::whereRaw($where)->count();
            $this->resource = BoardComment::whereIn('id', implode(',', $select_id))->orderBy('id', 'desc')->get();
        }

        return $this;
    }

    /**
     * 게시판 정보를 반환한다.
     * @return BoardManager
     */
    public function getBoard(){
        if(isset($this->board->id) && $this->board->id){
            return $this->board;
        }
        else if($this->content_id){
            $this->board = new BoardManager();
            $this->board->initWithContentID($this->content_id);
            return $this->board;
        }
        return new BoardManager();
    }

    /**
     * 게시물 고유번호를 입력받는다.
     * @param int $content_id
     */
    public function setContentID($content_id){
        $this->content_id = intval($content_id);
    }

    /**
     * 총 댓글 개수를 반환한다.
     * @return int
     */
    public function getCount(){
        if(is_null($this->total)){
            if($this->content_id){
                $this->total = BoardComment::where('content_id', $this->content_id)->count();
            }
            else{
                $this->total = BoardComment::all()->count();
            }
        }
        return intval($this->total);
    }

    /**
     * 리스트를 초기화한다.
     */
    public function initFirstList(){
        $this->next_list_page = 1;
    }

    /**
     * 다음 리스트를 반환한다.
     * @return array
     */
    public function hasNextList(){
        $offset = ($this->next_list_page-1)*$this->rpp;

        $this->resource = BoardComment::where('content_id', $this->content_id)->orderBy($this->sort, $this->order)->offset($offset)->limit($this->rpp)->get();

        if($this->resource){
            $this->next_list_page++;
        }
        else{
            $this->next_list_page = 1;
        }

        return $this->resource;
    }

    /**
     * 다음 댓글을 반환한다.
     * @return BoardCommentManager|string
     */
    public function hasNext(){
        if(!$this->resource) return '';
        $this->row = current($this->resource);

        if($this->row){
            next($this->resource);
            $comment = new BoardCommentManager();
            $comment->initWithRow($this->row);
            $comment->board = $this->board;
            return $comment;
        }
        else{
            unset($this->resource);
            return '';
        }
    }

    /**
     * 댓글 고유번호를 입력받아 해당 댓글을 반환한다.
     * @param int $id
     * @return BoardCommentManager
     */
    public function getComment($id){
        $comment = new BoardCommentManager();
        $comment->initWithID($id);
        return $comment;
    }

    /**
     * 댓글 정보를 입력한다.
     * @param int $parent_id
     * @param int $user_id
     * @param string $user_display
     * @param string $content
     * @param string $password
     * @return mixed|string
     */
    public function add($parent_id, $user_id, $user_display, $content, $password=''){

        $content_id = $this->content_id;
        $parent_id = intval($parent_id);
        $user_id = intval($user_id);
        $user_display = e($user_display);
        $content = e(SecurityHelpers::board_safeiframe(SecurityHelpers::board_xssfilter($content)));
        $like = 0;
        $unlike = 0;
        $vote = 0;
        $password = e($password);

        $comment = BoardComment::create([
            'content_id' => $content_id,
            'parent_id' => $parent_id,
            'user_id' => $user_id,
            'user_display' => $user_display,
            'content' => $content,
            'like' => $like,
            'unlike' => $unlike,
            'vote' => $vote,
            'password' => $password,
        ]);
        $insert_id = $comment->id;

        // 댓글 숫자를 게시물에 등록한다.
        $content = BoardContent::find($content_id);
        $content->comment += 1;
        $content->save();

        // 댓글 입력 액션 훅 실행
        \Event::fire('placecompany.board.board_comments_insert', [&$insert_id, $content_id, $this->getBoard()]);

        return $insert_id;
    }

    /**
     * 댓글을 삭제한다.
     * @param int $id
     */
    public function delete($id){
        $comment = new BoardCommentManager();
        $comment->initWithID($id);
        $comment->delete();
    }

    /**
     * 정렬 순서를 반환한다.
     * @return string
     */
    public function getSorting(){
        static $board_comments_sort;

        if($board_comments_sort){
            return $board_comments_sort;
        }

        $board_comments_sort = Cookie::get('board_comments_sort') ? $_COOKIE['board_comments_sort'] : 'best';

        if(!in_array($board_comments_sort, array('best', 'oldest', 'newest'))){
            $board_comments_sort = 'best';
        }

        return $board_comments_sort;
    }
}
