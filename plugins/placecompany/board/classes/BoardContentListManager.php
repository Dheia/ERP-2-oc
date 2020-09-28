<?php
namespace Placecompany\Board\Classes;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Placecompany\Board\Models\BoardContent;
use Placecompany\Board\Models\BoardSetting;

/**
 * board 게시글 리스트
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardContentListManager {

    private $board_list_sort;
    private $from;
    private $where;
    private $multiple_option_keys;
    private $next_list_page = 1;

    var $board;
    var $board_id;
    var $total;
    var $index;
    var $category1;
    var $category2;
    var $user_id = 0;
    var $compare;
    var $start_date;
    var $end_date;
    var $search_option = array();
    var $sort = 'created_at';
    var $order = 'DESC';
    var $rpp = 10;
    var $page = 1;
    var $status;
    var $stop;
    var $resource;
    var $resource_notice;
    var $resource_reply;
    var $row;
    var $is_loop_start;
    var $is_first;
    var $is_rss = false;
    var $is_latest = false;
    var $dayofweek;
    var $within_days = 0;
    var $random = false;
    var $latest = array();

    public function __construct($board_id=''){
        if($board_id) $this->setBoardID($board_id);
    }

    /**
     * 모든 게시판의 내용을 반환한다.
     * @return BoardContentListManager
     */
    public function init(){
        $this->total = BoardContent::all()->count();
        $this->resource = BoardContent::all()->orderBy('created_at', 'desc')->offset(($this->page-1)*$this->rpp)->limit($this->rpp)->get();
        $this->index = $this->total;
        return $this;
    }

    /**
     * 모든 게시판의 내용을 반환한다.
     * @return BoardContentListManager
     */
    public function initWithKeyword($keyword=''){
        $query = new BoardContent();
        if($keyword){
            $keyword = filter_var($keyword, FILTER_VALIDATE_URL);
            $query->where('title', 'LIKE', "%{$keyword}%")
                ->orWhere('content', 'LIKE', "%{$keyword}%");
        }
        if($this->board_id)
            $query->where('board_id', $this->board_id);
        if($this->status){
            if($this->status == 'published'){
                $query->where('status', '')
                    ->orWhereNull('status');
            }
            else{
                $query->where('status', $this->status);
            }
        }

        $offset = ($this->page-1)*$this->rpp;

        $copy_query = $query;

        $results = $query->orderBy('created_at', 'desc')->offset($offset)->limit($this->rpp)->get();

        foreach($results as $row){
            $select_id[] = intval($row->id);
        }

        if(!isset($select_id)){
            $this->total = 0;
            $this->resource = array();
        }
        else{
            $this->total = $copy_query->count();
            $this->resource = BoardContent::whereIn('id', implode(',', $select_id))->orderBy('created_at', 'desc')->get();
        }

        $this->index = $this->total - $offset;
        return $this;
    }

    /**
     * RSS 피드 출력을 위한 리스트를 반환한다.
     * @param int $board_id
     * @return BoardContentListManager
     */
    public function initWithRSS($board_id=''){
        $query = new BoardContent();
        if($board_id){
            $board_id = intval($board_id);
            $query->where('board_id', 'LIKE', "%{$board_id}%");
        }
        else{
            $read = array();
            $result = BoardSetting::where('permission_read', 'all')->value('id');
            foreach($result as $row){
                $read[] = $row->id;
            }
            if($read){
                $query->whereIn('board_id', "implode(',', $read)");
            }
        }
        $query->whereIn('secret', "");
        $query->where('status', "")
            ->orWhereNull('status');
        $this->total = $query->count();
        $this->resource = $query->orderBy('created_at', 'desc')->offset(($this->page-1)*$this->rpp)->limit($this->rpp)->get();
        $this->index = $this->total;
        $this->is_rss = true;
        return $this;
    }

    /**
     * 게시판 아이디를 입력한다.
     * @param int|array $board_id
     * @return BoardContentListManager
     */
    public function setBoardID($board_id){
        if(is_array($board_id)){
            $this->board = new BoardManager(reset($board_id));
        }
        else if($board_id){
            $this->board = new BoardManager($board_id);
        }
        $this->board_id = $board_id;
        return $this;
    }

    /**
     * 페이지 번호를 입력한다.
     * @param int $page
     * @return BoardContentListManager
     */
    public function page($page){
        $page = intval($page);
        if($page <= 0){
            $this->page = 1;
        }
        else{
            $this->page = $page;
        }
        return $this;
    }

    /**
     * 한 페이지에 표시될 게시글 개수를 입력한다.
     * @param int $rpp
     * @return BoardContentListManager
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
     * 카테고리1을 입력한다.
     * @param string $category
     * @return BoardContentListManager
     */
    public function category1($category){
        if($category) $this->category1 = $category;
        return $this;
    }

    /**
     * 카테고리2를 입력한다.
     * @param string $category
     * @return BoardContentListManager
     */
    public function category2($category){
        if($category) $this->category2 = $category;
        return $this;
    }

    /**
     * 글 작성자 고유 ID값을 입력한다.
     * @param int $user_id
     * @return BoardContentListManager
     */
    public function memberid($user_id){
        if($user_id) $this->user_id = intval($user_id);
        return $this;
    }

    /**
     * 검색 연산자를 입력한다.
     * @param string $compare
     */
    public function setCompare($compare){
        $this->compare = $compare;
    }

    /**
     * 작성일 기간을 입력한다.
     * @param string $start_date
     * @param string $end_date
     */
    public function setDateRange($start_date, $end_date){
        if($start_date){
            $this->start_date = date('Ymd', strtotime($start_date)) . '000000';
        }
        if($end_date){
            $this->end_date = date('Ymd', strtotime($end_date)) . '235959';
        }
    }

    /**
     * 최근 특정 요일의 게시글만 가져오도록 설정한다.
     * @return string $dayofweek
     */
    public function setDayOfWeek($dayofweek){
        if($dayofweek && in_array($dayofweek, array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'))){
            $timestamp = strtotime(sprintf('last %s', $dayofweek), strtotime('tomorrow', Carbon::now()->timestamp));
            $ymd = date('Y-m-d', $timestamp);
            $this->setDateRange($ymd, $ymd);
            $this->dayofweek = $dayofweek;
        }
    }

    /**
     * 표시할 게시글 기간을 설정한다.
     * @return int $within_days
     */
    public function setWithinDays($within_days){
        $this->within_days = intval($within_days);
    }

    /**
     * 결과를 랜점하게 정렬할지 설정한다.
     * @param boolean $random
     */
    public function setRandom($random){
        $this->random = $random ? true : false;
    }

    /**
     * 검색 옵션의 데이터를 반환한다.
     * @param array $associative
     * @param array $search_option
     * @return string
     */
    public function getSearchOptionValue($associative, $search_option=array()){
        if(!$search_option) $search_option = $this->search_option;
        $key = array_shift($associative);
        if(isset($search_option[$key])){
            if(is_array($search_option[$key])){
                return $this->getSearchOptionValue($associative, $search_option[$key]);
            }
            else{
                return $search_option[$key];
            }
        }
        return '';
    }

    /**
     * 검색 옵션을 입력한다.
     * @param array $search_option
     */
    public function setSearchOption($search_option){
        $this->search_option = $search_option;
    }

    /**
     * 검색 조건을 추가한다.
     * @param string $key
     * @param string $value
     * @param string $compare
     */
    public function addSearchOption($key, $value, $compare='='){
        $this->search_option[] = array('key'=>$key, 'compare'=>$compare, 'value'=>$value);
    }

    /**
     * 게시판의 리스트를 반환한다.
     * @param string $keyword
     * @param string $search
     * @param boolean $with_notice
     * @return array $resource
     */
    public function getList($keyword='', $search='title', $with_notice=false){

        if($this->stop){
            $this->total = 0;
            $this->resource = array();
            $this->index = $this->total;
            return $this->resource;
        }

        if($this->getSorting() == 'newest'){
            // 최신순서
            $this->sort = 'created_at';
            $this->order = 'DESC';
        }
        else if($this->getSorting() == 'best'){
            // 추천순서
            $this->sort = 'vote';
            $this->order = 'DESC';
        }
        else if($this->getSorting() == 'viewed'){
            // 조회순서
            $this->sort = 'view';
            $this->order = 'DESC';
        }
        else if($this->getSorting() == 'updated'){
            // 업데이트순서
            $this->sort = 'update';
            $this->order = 'DESC';
        }

        if(is_array($this->board_id)){
            $board_id = Helpers::array2int($this->board_id);
            $board_id = implode(',', $board_id);
            $this->where[] = "`board_id` IN ($board_id)";
        }
        else{
            $allowed_board_id = $this->board_id;
            \Event::fire('placecompany.board.board_allowed_board_id', [&$allowed_board_id, $this->board]);

            if(is_array($allowed_board_id)){
                $board_id = Helpers::array2int($allowed_board_id);
                $board_id = implode(',', $board_id);
                $this->where[] = "`board_id` IN ($board_id)";
            }
            else{
                $allowed_board_id = (int)$allowed_board_id;
                $this->where[] = "`board_id`='$allowed_board_id'";
            }
        }

        if(!in_array($this->compare, array('=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE'))){
            $this->compare = 'LIKE';
        }

        $this->from[] = "placecompany_board_content";

        if(!empty($search) && strpos($search, BoardContentManager::$SKIN_OPTION_PREFIX) !== false && $keyword){
            // 입력 필드 검색후 게시글을 불러온다.
            $this->from[] = "LEFT JOIN `placecompany_board_option` ON `placecompany_board_content`.`id`=`placecompany_board_option`.`content_id`";

            $search = str_replace(BoardContentManager::$SKIN_OPTION_PREFIX, '', $search);

            $keyword_list = preg_split("/(&|\|)/", $keyword, -1, PREG_SPLIT_DELIM_CAPTURE);
            if(is_array($keyword_list) && count($keyword_list) > 0){
                foreach($keyword_list as $keyword){
                    if($keyword == '&'){
                        $sub_where[] = ' AND ';
                    }
                    else if($keyword == '|'){
                        $sub_where[] = ' OR ';
                    }
                    else{
                        if(in_array($this->compare, array('LIKE', 'NOT LIKE'))){
                            $keyword = "%{$keyword}%";
                        }

                        $sub_where[] = "(`option_key`='{$search}' AND `option_value` {$this->compare} '{$keyword}')";
                    }
                }

                if(count($sub_where) > 1){
                    $this->where[] = '(' . implode('', $sub_where) . ')';
                }
                else{
                    $this->where[] = implode('', $sub_where);
                }
            }
        }
        else if($keyword){
            // 일반적인 검색후 게시글을 불러온다.
            $keyword_list = preg_split("/(&|\|)/", $keyword, -1, PREG_SPLIT_DELIM_CAPTURE);

            if(is_array($keyword_list) && count($keyword_list) > 0){
                foreach($keyword_list as $keyword){
                    if($keyword == '&'){
                        $sub_where[] = ' AND ';
                    }
                    else if($keyword == '|'){
                        $sub_where[] = ' OR ';
                    }
                    else{
                        if(in_array($this->compare, array('LIKE', 'NOT LIKE'))){
                            $keyword = "%{$keyword}%";
                        }

                        if($search){
                            $sub_where[] = "`{$search}` {$this->compare} '{$keyword}'";
                        }
                        else{
                            $sub_where[] = "(`title` {$this->compare} '{$keyword}' OR `content` {$this->compare} '{$keyword}')";
                        }
                    }
                }

                if(count($sub_where) > 1){
                    $this->where[] = '(' . implode('', $sub_where) . ')';
                }
                else{
                    $this->where[] = implode('', $sub_where);
                }
            }
        }
        else{
            // 검색이 아니라면 답글이 아닌 일반글만 불러온다.
            $this->where[] = "`parent_id`='0'";
        }

        // 해당 기간에 작성된 게시글만 불러온다.
        \Event::fire('placecompany.board.board_list_date_range', [array('start_date'=>$this->start_date, 'end_date'=>$this->end_date), $this->board_id, $this]);
        $date_range = array('start_date'=>$this->start_date, 'end_date'=>$this->end_date);

        if($date_range['start_date'] && $date_range['end_date']){
            $start_date = $date_range['start_date'];
            $end_date = $date_range['end_date'];
            $this->where[] = "(`created_at` BETWEEN '{$start_date}' AND '{$end_date}')";
        }
        else if($date_range['start_date']){
            $start_date = $date_range['start_date'];
            $this->where[] = "`created_at`>='{$start_date}'";
        }
        else if($date_range['end_date']){
            $end_date = $date_range['end_date'];
            $this->where[] = "`created_at`<='{$end_date}'";
        }

        // 입력 필드 검색 옵션 쿼리를 생성한다.
        \Event::fire('placecompany.board.board_list_search_option', [&$this->search_option, $this->board_id, $this]);
        $search_option = $this->search_option;
        if($search_option){
            $multiple_option_query = $this->multipleOptionQuery($search_option);
            if($multiple_option_query){
                $this->where[] = $multiple_option_query;

                foreach($this->multiple_option_keys as $option_name){
                    $option_key = array_search($option_name, $this->multiple_option_keys);
                    $this->from[] = "INNER JOIN `placecompany_board_option` AS `option_{$option_key}` ON `placecompany_board_content`.`id`=`option_{$option_key}`.`content_id`";
                }
            }
        }

        if($this->category1){
            $category1 = $this->category1;
            $this->where[] = "`category1`='{$category1}'";
        }

        if($this->category2){
            $category2 = $this->category2;
            $this->where[] = "`category2`='{$category2}'";
        }

        if($this->user_id){
            $user_id = $this->user_id;
            $this->where[] = "`user_id`='{$user_id}'";
        }

        if($this->within_days){
            $days = date('Ymd', strtotime("-{$this->within_days} day", Carbon::now()->timestamp));
            $this->where[] = "`created_at`>='{$days}000000'";
        }

        // 공지사항이 아닌 게시글만 불러온다.
        if(!$with_notice) $this->where[] = "`notice`=''";

        // 휴지통에 없는 게시글만 불러온다.
        $this->where[] = "(`status`='' OR `status` IS NULL OR `status`='pending_approval')";

        // 게시글의 id 정보만 가져온다.
        $default_select = "`placecompany_board_content`.`id`";

        // board_list_select, board_list_from, board_list_where, board_list_orderby 워드프레스 필터 실행
        \Event::fire('placecompany.board.board_list_select', [&$default_select, $this->board_id, $this]);
        $select = $default_select;
        \Event::fire('placecompany.board.board_list_from', [implode(' ', $this->from), $this->board_id, $this]);
        $from = implode(' ', $this->from);
        \Event::fire('placecompany.board.board_list_where', [implode(' AND ', $this->where), $this->board_id, $this]);
        $where = implode(' AND ', $this->where);
        \Event::fire('placecompany.board.board_list_orderby', ["`{$this->sort}` {$this->order}", $this->board_id, $this]);
        $orderby = "`{$this->sort}` {$this->order}";

        $offset = ($this->page-1)*$this->rpp;

        if($default_select != $select){
            $this->total = \DB::table($from)->whereRaw($where)->count();
            $this->resource = \DB::table($from)->selectRaw("SELECT {$select}")->where($where)->orderByRaw($orderby)->offset($offset)->limit($this->rpp)->get()->toArray();
        }
        else{
            $results = \DB::table($from)->selectRaw($select)->whereRaw($where)->orderByRaw($orderby)->offset($offset)->limit($this->rpp)->get();
            foreach($results as $row){
                if($row->id){
                    $select_id[] = intval($row->id);
                }
            }

            if(!isset($select_id)){
                $this->total = 0;
                $this->resource = array();
            }
            else{
                $this->total = \DB::table($from)->whereRaw($where)->count();
                $this->resource = \DB::table('placecompany_board_content')
                    ->whereRaw("`id` IN(".implode(',', $select_id).")")
                    ->orderByRaw("FIELD(`id`,".implode(',', $select_id).")")->get()->toArray();
            }
        }

        // 결과를 랜덤하게 정렬한다.
        if($this->random){
            shuffle($this->resource);
        }

        $this->is_loop_start = true;
        \Event::fire('placecompany.board.content_list_total_count', [$this->total, $this->board, $this]);
        \Event::fire('placecompany.board.board_content_list_items', [$this->resource, $this->board, $this]);

        if($this->board && $this->board->meta->list_sort_numbers == 'asc'){
            $this->index = $offset + 1;
        }
        else{
            $this->index = $this->total - $offset;
        }

        return $this->resource;
    }

    /**
     * 검색 옵션 쿼리를 반환한다.
     * @param array $multiple
     * @param string $relation
     * @return string
     */
    public function multipleOptionQuery($multiple, $relation='AND'){
        if(isset($multiple['relation'])){
            if(in_array($multiple['relation'], array('AND', 'OR'))){
                $relation = $multiple['relation'];
            }
            unset($multiple['relation']);
        }

        foreach($multiple as $option){
            if(isset($option['relation'])){
                $where[] = $this->multipleOptionQuery($option);
            }
            else if(is_array($option)){
                if(isset($option['value']) && is_array($option['value'])){
                    $option_value = array();
                    foreach($option['value'] as $value){
                        $option_value[] = filter_var($value, FILTER_SANITIZE_STRING);
                    }

                    $option_value = "'".implode("','", $option_value)."'";
                }
                else{
                    $option_value = isset($option['value']) ? filter_var($option['value'], FILTER_SANITIZE_STRING) : '';
                }

                $option_key = isset($option['key']) ? filter_var($option['key']) : '';
                $option_compare = isset($option['compare']) ? $option['compare'] : '';
                $option_wildcard = isset($option['wildcard']) ? $option['wildcard'] : '';

                if($option_key && $option_value){
                    $this->multiple_option_keys[$option_key] = $option_key;
                    $option_index = array_search($option_key, $this->multiple_option_keys);

                    if(in_array($option_compare, array('IN', 'NOT IN'))){
                        $where[] = "(`option_{$option_index}`.`option_key`='{$option_key}' AND `option_{$option_index}`.`option_value` {$option_compare} ({$option_value}))";
                    }
                    else{
                        if(!in_array($option_compare, array('=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE'))){
                            $option_compare = '=';
                        }

                        switch($option_wildcard){
                            case 'left': $option_value = "%{$option_value}"; break;
                            case 'right': $option_value = "{$option_value}%"; break;
                            case 'both': $option_value = "%{$option_value}%"; break;
                        }

                        $where[] = "(`option_{$option_index}`.`option_key`='{$option_key}' AND `option_{$option_index}`.`option_value` {$option_compare} '{$option_value}')";
                    }
                }
            }
        }

        if(isset($where) && is_array($where)){
            if(count($where) > 1){
                return '(' . implode(" {$relation} ", $where) . ')';
            }
            return implode(" {$relation} ", $where);
        }
        return '';
    }

    /**
     * 모든 게시글 리스트를 반환한다.
     * @return array
     */
    public function getAllList(){

        if(is_array($this->board_id)){
            foreach($this->board_id as $key=>$value){
                $value = intval($value);
                $board_ids[] = "'{$value}'";
            }
            $board_ids = implode(',', $board_ids);
            $where[] = "`board_id` IN ($board_ids)";
        }
        else{
            $this->board_id = intval($this->board_id);
            $where[] = "`board_id`='$this->board_id'";
        }
        $where = implode(' AND ', $where);

        $this->total = \DB::table('placecompany_board_content')->whereRaw($where)->count();

        $page = 1;
        $limit = 1000;
        $offset = ($page-1)*$limit;

        while($results = \DB::table('placecompany_board_content')->whereRaw($where)->orderByRaw($this->sort." ".$this->order)->offset($offset)->limit($limit)->get()){
            foreach($results as $row){
                $this->resource[] = $row;
            }
            $page++;
            $offset = ($page-1)*$limit;
        }

        $this->is_loop_start = true;

        if($this->board && $this->board->meta->list_sort_numbers == 'asc'){
            $this->index = 1;
        }
        else{
            $this->index = $this->total - $offset;
        }

        return $this->resource;
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

        if(is_array($this->board_id)){
            foreach($this->board_id as $key=>$value){
                $value = intval($value);
                $board_ids[] = "'{$value}'";
            }
            $board_ids = implode(',', $board_ids);
            $where[] = "`board_id` IN ($board_ids)";
        }
        else{
            $this->board_id = intval($this->board_id);
            $where[] = "`board_id`='$this->board_id'";
        }
        $where = implode(' AND ', $where);

        $offset = ($this->next_list_page-1)*$this->rpp;

        $this->total = \DB::table('placecompany_board_content')->whereRaw($where)->count();
        $this->resource = \DB::table('placecompany_board_content')->whereRaw($where)->orderBy($this->sort, $this->order)->offset($offset)->limit($this->rpp);

        if($this->resource){
            $this->next_list_page++;
        }
        else{
            $this->next_list_page = 1;
        }

        $this->is_loop_start = true;

        if($this->board && $this->board->meta->list_sort_numbers == 'asc'){
            $this->index = $offset + 1;
        }
        else{
            $this->index = $this->total - $offset;
        }

        return $this->resource;
    }

    /**
     * 리스트에서 다음 게시글을 반환한다.
     * @return BoardContentManager
     */
    public function hasNext(){
        if(!$this->resource) return '';
        $this->row = current($this->resource);

        if($this->row){
            if(!$this->is_loop_start){
                if($this->board && $this->board->meta->list_sort_numbers == 'asc'){
                    $this->index++;
                }
                else{
                    $this->index--;
                }
                $this->is_first = false;
            }
            else{
                $this->is_loop_start = false;
                $this->is_first = true;
            }

            next($this->resource);
            $content = new BoardContentManager();
            $content->initWithRow($this->row);

            return $content;
        }
        else{
            unset($this->resource);
            return '';
        }
    }

    /**
     * 리스트의 현재 인덱스를 반환한다.
     * @return int
     */
    public function index(){
        return $this->index;
    }

    /**
     * 공지사항 리스트를 반환한다.
     * @return resource
     */
    public function getNoticeList(){
        if(is_array($this->board_id)){
            foreach($this->board_id as $key=>$value){
                $value = intval($value);
                $board_ids[] = "'{$value}'";
            }
            $board_ids = implode(',', $board_ids);
            $where[] = "`board_id` IN ($board_ids)";
        }
        else{
            $this->board_id = intval($this->board_id);
            $where[] = "`board_id`='$this->board_id'";
        }

        if($this->category1){
            $category1 = $this->category1;
            $where[] = "`category1`='{$category1}'";
        }

        if($this->category2){
            $category2 = $this->category2;
            $where[] = "`category2`='{$category2}'";
        }

        $where[] = "`notice`!=''";

        // 휴지통에 없는 게시글만 불러온다.
        $where[] = "(`status`='' OR `status` IS NULL OR `status`='pending_approval')";

        \Event::fire('placecompany.board.board_notice_list_orderby', ["`{$this->sort}` {$this->order}", $this->board_id, $this]);
        $orderby = "`{$this->sort}` {$this->order}";

        $this->resource_notice = \DB::table('placecompany_board_content')->whereRaw(implode(' AND ', $where))->orderByRaw($orderby)->get();

        return $this->resource_notice;
    }

    /**
     * 공지사항 리스트에서 다음 게시물을 반환한다.
     * @deprecated
     * @see BoardContentListManager::hasNextNotice()
     * @return BoardContentManager
     */
    public function hasNoticeNext(){
        return $this->hasNextNotice();
    }

    /**
     * 공지사항 리스트에서 다음 게시물을 반환한다.
     * @return BoardContentManager
     */
    public function hasNextNotice(){
        if(!$this->resource_notice) $this->getNoticeList();
        $this->row = current($this->resource_notice);

        if($this->row){
            next($this->resource_notice);
            $content = new BoardContentManager();
            $content->initWithRow($this->row);
            return $content;
        }
        else{
            unset($this->resource_notice);
            return '';
        }
    }

    /**
     * 답글 리스트를 반환한다.
     * @return array
     */
    public function getReplyList($parent_id){
        if(!$parent_id) {
            return [];
        }

        $where[] = "`parent_id`='$parent_id'";

        // 휴지통에 없는 게시글만 불러온다.
        $where[] = "(`status`='' OR `status` IS NULL OR `status`='pending_approval')";

        $this->resource_reply = \DB::table('placecompany_board_content')->whereRaw(implode(' AND ', $where))->orderBy('created_at', 'asc')->get()->toArray();

        return $this->resource_reply;
    }

    /**
     * 답글 리스트에서 다음 게시물을 반환한다.
     * @return BoardContentManager
     */
    public function hasNextReply(){
        if(!$this->resource_reply) return '';
        $this->row = current($this->resource_reply);

        if($this->row){
            next($this->resource_reply);
            $content = new BoardContentManager();
            $content->initWithRow($this->row);
            return $content;
        }
        else{
            unset($this->resource_reply);
            return '';
        }
    }

    /**
     * 정렬 순서를 반환한다.
     * @return string
     */
    public function getSorting(){
        if($this->board_list_sort){
            return $this->board_list_sort;
        }

        if(!is_array($this->board_id)) {
            $this->board_list_sort = \Cookie::get("board_list_sort_{$this->board_id}") ? : $this->getDefaultSorting();
            $this->board_list_sort = session("board_list_sort_{$this->board_id}") ? : $this->board_list_sort;
            $this->board_list_sort = get('board_list_sort') ? get('board_list_sort') : $this->board_list_sort;
        }

        if(!in_array($this->board_list_sort, array('newest', 'best', 'viewed', 'updated'))){
            $this->board_list_sort = $this->getDefaultSorting();
        }

        if(!is_array($this->board_id)) {
            $_SESSION["board_list_sort_{$this->board_id}"] = $this->board_list_sort;
        }

        return $this->board_list_sort;
    }

    /**
     * 정렬 순서를 설정한다.
     * @param string $sort
     */
    public function setSorting($sort){
        if($sort == 'newest'){
            // 최신순서
            $this->board_list_sort = $sort;
        }
        else if($sort == 'best'){
            // 추천순서
            $this->board_list_sort = $sort;
        }
        else if($sort == 'viewed'){
            // 조회순서
            $this->board_list_sort = $sort;
        }
        else if($sort == 'updated'){
            // 업데이트순서
            $this->board_list_sort = $sort;
        }
    }

    /**
     * 기본 정렬 순서를 반환한다.
     * @return string
     */
    public function getDefaultSorting(){
        return \Event::fire('placecompany.board.board_list_default_sorting', ['newest', $this->board_id, $this]);
    }

    /**
     * 정렬 순서를 내림차순(DESC)로 변경한다.
     * @param string $sort
     * @return BoardContentListManager
     */
    public function orderDESC($sort=''){
        if($sort) $this->sort = $sort;
        $this->order = 'DESC';
        return $this;
    }

    /**
     * 정렬 순서를 오름차순(ASC)로 변경한다.
     * @param string $sort
     * @return BoardContentListManager
     */
    public function orderASC($sort=''){
        if($sort) $this->sort = $sort;
        $this->order = 'ASC';
        return $this;
    }
}
