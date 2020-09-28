<?php namespace Placecompany\Board\Classes;
use Backend\Facades\BackendAuth;
use Cms\Classes\Controller;
use Cms\Classes\Page;
use Illuminate\Support\Facades\Lang;
use Indikator\DevTools\FormWidgets\Help;
use Placecompany\Board\Components\Board;
use Placecompany\Board\Models\BoardContent;
use Placecompany\Board\Models\Settings;
use Placecompany\Board\Plugin;

/**
 * board 워드프레스 게시판 생성
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardBuilderManager {
    var $controller;
    var $mod;
    var $board;
    var $board_id;
    var $meta;
    var $id;
    var $skin;
    var $skin_name;
    var $category1;
    var $category2;
    var $rpp;
    var $sort;
    var $url;
    var $dayofweek;
    var $within_days;
    var $random;
    var $view_iframe;
    var $is_ajax = false;

    public function __construct($board_id='', $is_latest=false){
        global $board_builder;
        $board_builder = $this;

        $this->category1 = Helpers::board_category1();
        $this->category2 = Helpers::board_category2();
        $this->id = Helpers::board_id();
        $this->sort = 'newest';

        $this->setSkin('default');

        if($board_id){
            $this->setBoardID($board_id, $is_latest);
        }

        $this->controller = Controller::getController();
    }

    /**
     * 게시판 뷰(View)를 설정한다. (List/Document/Editor/Remove/Order/Complete/History/Sales)
     * @param string $mod
     */
    public function setMOD($mod){
        $this->mod = $mod;
    }

    /**
     * 게시판 스킨을 설정한다.
     * @param string $skin
     */
    public function setSkin($skin){
        $this->skin = BoardSkinManager::getInstance();
        \Event::fire('placecompany.board.board_builder_set_skin', [$skin, $this]);
        $this->skin_name = $skin;
    }

    /**
     * 게시판 ID를 설정한다.
     * @param int $board_id
     */
    public function setBoardID($board_id, $is_latest=false){

        $this->board_id = $board_id;
        $this->board = new BoardManager($this->board_id);
        $this->meta = $this->board->meta;

        if(!$is_latest){
            $default_build_mod = $this->meta->default_build_mod;
            if(!$default_build_mod){
                $default_build_mod = 'list';
            }
            \Event::fire('placecompany.board.board_default_build_mod', [&$default_build_mod, $this->board_id]);
            $this->mod = Helpers::board_mod($default_build_mod);

            // 외부 요청을 금지하기 위해서 사용될 게시판 id는 세션에 저장한다.
            \Session::put('board_board_id', $this->board_id);

            $script = \JavaScript::put([
                'board_current' => [
                    'board_id'          => $this->board_id,
                    'content_id'       => $this->id,
                    'use_tree_category' => $this->meta->use_tree_category,
                    'tree_category'     => json_decode($this->meta->tree_category, true),
                    'mod'               => $this->mod,
                    'add_media_url'     => route('placecompany.board::media', [
                        'board_id'    => $this->board_id,
                        'media_group' => Helpers::board_media_group(),
                        'content_id' => ($this->mod=='editor' ? $this->id : '')
                    ]),
                    'use_editor' => $this->board->use_editor,
                ]
            ]);

            \Event::listen('cms.page.render', function (Controller $controller, $pageContents) use ($script) {
                echo "<script>{$script}</script>";
            });

            // font-awesome 출력
            if(!Settings::get('board_fontawesome')){
                asset(plugins_path('/placecompany/board/assets/plugins/font-awesome/css/font-awesome.min.css'));
                asset(plugins_path('/placecompany/board/assets/plugins/font-awesome/css/font-awesome-ie7.min.css'));
            }
        }
    }

    /**
     * 페이지당 게시글 개수를 설정한다.
     * @param int $rpp
     */
    public function setRpp($rpp){
        $this->rpp = intval($rpp);
    }

    /**
     * 게시글 정렬 순서를 설정한다.
     * @param string $sort
     */
    public function setSorting($sort){
        $this->sort = filter_var($sort, FILTER_SANITIZE_STRING);
    }

    /**
     * 게시판 실제 주소를 설정한다.
     * @param string $url
     */
    public function setURL($url){
        $this->url = filter_var($url, FILTER_SANITIZE_URL);
    }

    /**
     * 최신글 숏코드 최근 특정 요일을 설정한다.
     * @param int $within_days
     */
    public function setDayOfWeek($dayofweek){
        $this->dayofweek = filter_var($dayofweek, FILTER_SANITIZE_STRING);
    }

    /**
     * 최신글 숏코드 기간을 설정한다.
     * @param int $within_days
     */
    public function setWithinDays($within_days){
        $this->within_days = intval($within_days);
    }

    /**
     * 최신글 숏코드 목록의 결과를 랜점하게 정렬할지 설정한다.
     * @param boolean $random
     */
    public function setRandom($random){
        $this->random = $random ? true : false;
    }

    /**
     * 게시판 리스트를 반환한다.
     * @return BoardContentListManager
     */
    public function getList(){
        $list = new BoardContentListManager($this->board_id);
        $list->category1($this->category1);
        $list->category2($this->category2);

        if($this->board->isPrivate()){
            if(BackendAuth::check()){
                $list->memberid(BackendAuth::getUser()->id);
            }
            else{
                $list->stop = true;
            }
        }

        $list->rpp($this->rpp);
        $list->page(Helpers::board_pageid());
        $list->setCompare(Helpers::board_compare());
        $list->setDateRange(Helpers::board_start_date(), Helpers::board_end_date());
        $list->setSearchOption(Helpers::board_search_option());
        $list->getList(Helpers::board_keyword(), Helpers::board_target(), Helpers::board_with_notice());

        return $list;
    }

    /**
     * 게시판 리스트를 배열로 반환한다.
     * @return array
     */
    public function getListArray(){
        // boardBuilder 클래스에서 실행된 게시판의 mod 값을 설정한다.
        Helpers::board_builder_mod('list');

        $list = $this->getList();
        $data = array();

        while($content = $list->hasNext()){
            $url = new BoardUrlManager();
            $url->setBoard($this->board);
            $url->setPath(\Request::server('HTTP_REFERER'));

            $_data = array();
            $_data['id'] = $content->id;
            $_data['user_id'] = $content->user_id;
            $_data['user_display'] = $content->user_display;
            $_data['title'] = $content->title;
            $_data['content'] = $content->secret!='true'?$content->content:'';
            $_data['date'] = $content->date;
            $_data['view'] = $content->view;
            $_data['comment'] = $content->comment;
            $_data['like'] = $content->like;
            $_data['unlike'] = $content->unlike;
            $_data['vote'] = $content->vote;
            $_data['thumbnail_file'] = $content->thumbnail_file;
            $_data['thumbnail_name'] = $content->thumbnail_name;
            $_data['category1'] = $content->category1;
            $_data['category2'] = $content->category2;
            $_data['secret'] = $content->secret;
            $_data['search'] = $content->search;
            $_data['attach'] = $content->attach;
            $_data['option'] = $content->option->toArray();

            if($this->view_iframe){
                $url->set('board_id', $content->board_id);
                $url->set('view_iframe', '1');
            }

            $_data['urls']['document'] = $url->getDocumentURLWithID($content->id);
            $_data['urls']['editor'] = $url->getContentEditor($content->id);
            $_data['urls']['remove'] = $url->getContentRemove($content->id);

            $data[] = $_data;
        }
        return $data;
    }

    /**
     * 게시판 리스트 페이지의 HTML 코드를 반환한다.
     * @return string
     */
    public function getListHTML(){
        // boardBuilder 클래스에서 실행된 게시판의 mod 값을 설정한다.
        Helpers::board_builder_mod('list');

        ob_start();
        $this->builderList();
        return ob_get_clean();
    }

    /**
     * 게시판 페이지를 생성하고 반환한다.
     * @return string
     */
    public function create(){
        if($this->meta->permission_list && $this->meta->permission_access && !BackendAuth::check()){
            echo '<script>alert("'.Lang::get('placecompany.board::lang.Please Log in to continue.').'");</script>';
            echo '<script>top.window.location.href="' . url('/') . '";</script>';
        }
        else{
            if(($this->meta->view_iframe || \App::runningInBackend()) && !Helpers::board_id()){
                $view_iframe = true;
            }
            else{
                $view_iframe = false;
            }

            if($this->meta->editor_view_iframe && !\App::runningInBackend()){
                if($this->mod == 'editor' && !Helpers::board_id()){
                    $view_iframe = true;
                }
                else if($this->mod != 'editor' && Helpers::board_id() && !$this->meta->view_iframe){
                    $url = new BoardUrlManager();
                    echo '<script>top.window.location.href="' . $url->set('board_id', '')
                            ->set('id', Helpers::board_id())->set('mod', Helpers::board_mod())->set('category1', Helpers::board_category1())
                            ->set('category2', Helpers::board_category2())->set('keyword', Helpers::board_keyword())
                            ->set('target', Helpers::board_target())->set('view_iframe', '')->set('iframe_id', '')->toString() . '";</script>';
                    exit;
                }
            }
            \Event::fire('placecompany.board.board_builder_view_iframe', [&$view_iframe, $this]);

            if($view_iframe){
                $url = new BoardUrlManager();
                $iframe_id = uniqid();
                return '<iframe id="board-iframe-' . $iframe_id . '" class="board-iframe board-iframe-' . $this->board_id . '" src="' . $url->set('board_id', $this->board_id)->set('id', Helpers::board_id())->set('parent_id', Helpers::board_parent_id())->set('mod', Helpers::board_mod())->set('category1', Helpers::board_category1())->set('category2', Helpers::board_category2())->set('keyword', Helpers::board_keyword())->set('target', Helpers::board_target())->set('view_iframe', '1')->set('iframe_id', $iframe_id)->toString() . '" style="width:100%" scrolling="no" frameborder="0"></iframe>';
            }

            // boardBuilder 클래스에서 실행된 게시판의 mod 값을 설정한다.
            Helpers::board_builder_mod($this->mod);
            $this->mod = ucfirst($this->mod);
            if($this->meta->pass_autop == 'enable'){
                \Event::fire('placecompany.board.board_skin_header', [$this]);
                call_user_func(array($this, 'builder'.ucfirst($this->mod)));
                \Event::fire('placecompany.board.board_skin_footer', [$this]);
                return '';
            }
            else{
                ob_start();
                \Event::fire('placecompany.board.board_skin_header', [$this]);
                call_user_func(array($this, 'builder'.ucfirst($this->mod)));
                \Event::fire('placecompany.board.board_skin_footer', [$this]);
                return ob_get_clean();
            }
        }
    }

    /**
     * 게시판 리스트 페이지를 생성한다.
     */
    public function builderList(){
        $url = new BoardUrlManager();
        $url->setBoard($this->board);
        $url->setPath($this->url);

        $vars = array(
            'list' => $this->getList(),
            'url' => $url,
            'skin' => $this->skin,
            'skin_path' => $this->skin->url($this->skin_name),
            'skin_dir' => $this->skin->dir($this->skin_name),
            'board' => $this->board,
            'boardBuilder' => $this,
            'category1' => $this->category1,
            'category2' => $this->category2,
        );

        if($vars['board']->use_category == 'yes'){
            if($vars['board']->isTreeCategoryActive()){
                $category_type = 'tree-select';
            }
            else{
                $category_type = 'default';
            }
            \Event::fire('placecompany.board.board_skin_category_type', [&$category_type, $vars['board'], $this]);
            $vars['category'] = $vars['skin']->load($vars['board']->skin, "list-category-{$category_type}.htm", $vars);
        }

        $vars['board_id'] = Helpers::board_id();
        $vars['board_target'] = Helpers::board_target();
        $vars['board_keyword'] = Helpers::board_keyword();
        $vars['pagination'] = PaginationHelpers::board_pagination($vars['list']->page, $vars['list']->total, $vars['list']->rpp);

        echo $this->skin->load($this->skin_name, 'list.htm', $vars);
    }

    /**
     * 답글 리스트를 생성한다.
     * @param int $parent_id
     */
    public function builderReply($parent_id, $depth=0){
        $list = new BoardContentListManager();
        $list->getReplyList($parent_id);

        $url = new BoardUrlManager();
        $url->setBoard($this->board);
        $url->setPath($this->url);

        $vars = array(
            'list' => $list,
            'depth' => $depth,
            'url' => $url,
            'skin' => $this->skin,
            'skin_path' => $this->skin->url($this->skin_name),
            'skin_dir' => $this->skin->dir($this->skin_name),
            'board' => $this->board,
            'board_id' => Helpers::board_id(),
            'boardBuilder' => $this,
        );

        echo $this->skin->load($this->skin_name, 'reply-template.htm', $vars);
    }

    /**
     * 게시판 본문 페이지를 생성한다.
     */
    public function builderDocument(){
        $url = new BoardUrlManager();
        $url->setBoard($this->board);
        $url->setPath($this->url);

        $content = new BoardContentManager();
        $content->initWithid($this->id);

        if(!$content->id){
            echo '<script>alert("'.Lang::get('placecompany.board::lang.Invalid URL address.').'");</script>';
            echo '<script>window.location.href="' . $url->set('mod', 'list')->toString() . '";</script>';
            exit;
        }

        if($content->isTrash()){
            echo '<script>alert("'.Lang::get('placecompany.board::lang.This post has been removed.').'");</script>';
            echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
            exit;
        }

        if($this->isNotAllowed($content->board_id)){
            $message = Lang::get('placecompany.board::lang.This post has been moved.');
            if($message){
                echo '<script>alert("'.$message.'");</script>';
            }
            echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
            exit;
        }

        if($this->board->isPrivate()){
            if(BackendAuth::check()){
                if(!$content->notice && $content->user_id != BackendAuth::getUser()->id && $content->getTopContent()->user_id != BackendAuth::getUser()->id){
                    echo '<script>alert("'.Lang::get('placecompany.board::lang.This post can only be read by the owner.').'");</script>';
                    echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
                    exit;
                }
            }
            else{
                echo '<script>alert("'.Lang::get('placecompany.board::lang.This post can only be read by the owner.').'");</script>';
                echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
                exit;
            }
        }

        $board = $this->board;
        $content->board = $board;
        $board->content = $content;

        $vars = array(
            'content' => $content,
            'url' => $url,
            'skin' => $this->skin,
            'skin_path' => $this->skin->url($this->skin_name),
            'skin_dir' => $this->skin->dir($this->skin_name),
            'board' => $board,
            'boardBuilder' => $this,
            'top_content_id' => $content->getNextID(),
            'bottom_content_id' => $content->getPrevID(),
        );

        if($vars['top_content_id']) {
            $top_content = new BoardContentManager();
            $top_content->initWithID($vars['top_content_id']);
            $vars['top_content'] = $top_content;
        }

        if($vars['bottom_content_id']) {
            $bottom_content = new BoardContentManager();
            $bottom_content->initWithID($vars['bottom_content_id']);
            $vars['bottom_content'] = $bottom_content;
        }

        $allow_document = false;
        if(!$content->isReader()){
            if($this->board->permission_read != 'all' && !BackEndAuth::check()){
                if($this->meta->view_iframe){
                    \Event::fire('placecompany.board.board_cannot_read_document', ['go_login', $url->getDocumentRedirect($content->id), $content, $board, $this]);
                }
                else{
                    \Event::fire('placecompany.board.board_cannot_read_document', ['go_login', $_SERVER['REQUEST_URI'], $content, $board, $this]);
                }
            }
            else if($content->secret){
                if(!$content->isConfirm()){
                    if($content->parent_id){
                        $parent = new BoardContentManager();
                        $parent->initWithid($content->getTopContentid());
                        if($this->board->isReader($parent->user_id, $content->secret) || $parent->isConfirm()){
                            $allow_document = true;
                        }
                        else{
                            echo $this->skin->load($this->skin_name, 'confirm.htm', $vars);
                        }
                    }
                    else{
                        echo $this->skin->load($this->skin_name, 'confirm.htm', $vars);
                    }
                }
                else{
                    $allow_document = true;
                }
            }
            else{
                \Event::fire('placecompany.board.board_cannot_read_document', 'go_back', $url->set('mod', 'list')->toString(), $content, $board, $this);
            }
        }
        else{
            $allow_document = true;
        }

        // 글읽기 감소 포인트
        if($allow_document && $board->meta->document_read_down_point){
            /**
             * @todo 글읽기 감소 포인트 구현 필요
             */
//            if(function_exists('mycred_add')){
//                if(!BackendAuth::check()){
//                    if($this->meta->view_iframe){
//                        \Event::fire('placecompany.board.board_cannot_read_document', ['go_login', $url->getDocumentRedirect($content->id), $content, $board, $this]);
//                    }
//                    else{
//                        \Event::fire('placecompany.board.board_cannot_read_document', ['go_login', $_SERVER['REQUEST_URI'], $content, $board, $this]);
//                    }
//                    $allow_document = false;
//                }
//                else if($content->user_id != BackendAuth::getUser()->id){
//                    $log_args['user_id'] = BackendAuth::getUser()->id;
//                    $log_args['ref'] = 'document_read_down_point';
//                    $log_args['ref_id'] = $content->id;
//                    $log = new myCRED_Query_Log($log_args);
//
//                    if(!$log->have_entries()){
//                        $balance = mycred_get_users_balance(BackendAuth::getUser()->id);
//                        if($board->meta->document_read_down_point > $balance){
//                            \Event::fire('placecompany.board.board_builder_mod', ['not_enough_points', $url->set('mod', 'list')->toString(), $content, $board, $this]);
//                            $allow_document = false;
//                        }
//                        else{
//                            $point = intval(get_user_meta(BackendAuth::getUser()->id, 'board_document_mycred_point', true));
//                            update_user_meta(BackendAuth::getUser()->id, 'board_document_mycred_point', $point + ($board->meta->document_read_down_point*-1));
//
//                            mycred_add('document_read_down_point', BackendAuth::getUser()->id, ($board->meta->document_read_down_point*-1), __('Reading decrease points', 'board'), $content->id);
//                        }
//                    }
//                }
//            }
        }

        if($allow_document){
            $content->increaseView();

            // 에디터를 사용하지 않고, autolink가 활성화면 자동으로 link를 생성한다.
            if(!$board->use_editor && $this->meta->autolink){

                // 댓글 내용에 자동으로 link를 생성한다.
                \Event::listen('board_comments_content', 'AutoLinkHelpers::board_autoLink', 10, 1);
                \Event::fire('placecompany.board.board_content_paragraph_breaks', [AutoLinkHelpers::board_autolink($content->getContent()), $this]);
                $content->content = AutoLinkHelpers::board_autolink($content->getContent());
            }
            else{
                // 유튜브, 비메오 동영상 URL을 iframe 코드로 변환한다.
                \Event::listen('board_content', function($content) {
                    Helpers::board_video_url_to_iframe($content->content);
                });
                \Event::listen('board_comments_content', function($content) {
                    Helpers::board_video_url_to_iframe($content->content);
                });
                \Event::fire('placecompany.board.board_content_paragraph_breaks', [$content->getContent(), $this]);
                $content->content = $content->getContent();
            }

            // board_content 필터 실행
            \Event::fire('placecompany.board.board_content', [$content->getContent(), $content->id, $this->board_id]);
            $content->content = $content->getContent();

            // 게시글 숏코드(Shortcode) 실행
            if($this->meta->shortcode_execute == 1){
                // @todo 숏코드 확인 필요
                //$content->content = do_shortcode($content->getContent());
            }
            else{
                $content->content = str_replace('[', '&#91;', $content->getContent());
                $content->content = str_replace(']', '&#93;', $content->getContent());
            }

            echo $this->skin->load($this->skin_name, 'document.htm', $vars);

            if(\Event::fire('placecompany.board.board_always_view_list', [$board->meta->always_view_list, $this])){

                \Event::fire('placecompany.board.board_skin_always_view_list', [$this]);
                $this->builderList();
            }
        }
    }

    /**
     * 게시판 에디터 페이지를 생성한다.
     */
    public function builderEditor(){
        $url = new BoardUrlManager();
        $url->setBoard($this->board);
        $url->setPath($this->url);

        if($this->board->isWriter() && $this->board->permission_write=='all' && isset($_POST['title']) && $_POST['title']){
            $next_url = $url->set('id', $this->id)->set('mod', 'editor')->toString();
            if(!BackEndAuth::check() && !post('password')){
                echo '<script>alert("'.Lang::get('placecompany.board::lang.Please enter your password.').'");</script>';
                echo '<script>window.location.href="' . $next_url . '";</script>';
                exit;
            }
        }

        $content = new BoardContentManager($this->board_id);
        $content->initWithid($this->id);

        if($content->id){
            if($content->isTrash()){
                echo '<script>alert("'.Lang::get('placecompany.board::lang.This post has been removed.').'");</script>';
                echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
                exit;
            }

            if($this->isNotAllowed($content->board_id)){
                $message = Lang::get('placecompany.board::lang.This post has been moved.');
                if($message){
                    echo '<script>alert("'.$message.'");</script>';
                }
                echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
                exit;
            }
        }

        $board = $this->board;
        $content->board = $board;
        $board->content = $content;

        $vars = array(
            'content' => $content,
            'url' => $url,
            'skin' => $this->skin,
            'skin_path' => $this->skin->url($this->skin_name),
            'skin_dir' => $this->skin->dir($this->skin_name),
            'board' => $board,
            'boardBuilder' => $this,
        );

        $confirm_view = false;
        if(!$content->id && !$this->board->isWriter()){
            if(BackEndAuth::check()){
                echo '<script>alert("'.Lang::get('placecompany.board::lang.You do not have permission.').'");</script>';
                echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
            }
            else{
                $login_url = wp_login_url($_SERVER['REQUEST_URI']);
                echo '<script>alert("'.Lang::get('placecompany.board::lang.You do not have permission.').'");</script>';
                echo "<script>top.window.location.href='{$login_url}';</script>";
            }
            exit;
        }
        else if($content->id && !$content->isEditor()){
            if($this->board->permission_write=='all' && !$content->user_id){
                if(!$content->isConfirm()){
                    $confirm_view = true;
                }
            }
            else{
                if(BackendAuth::check()){
                    echo '<script>alert("'.Lang::get('placecompany.board::lang.You do not have permission.').'");</script>';
                    echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
                }
                else{
                    $login_url = wp_login_url($_SERVER['REQUEST_URI']);
                    echo '<script>alert("'.Lang::get('placecompany.board::lang.You do not have permission.').'");</script>';
                    echo "<script>top.window.location.href='{$login_url}';</script>";
                }
                exit;
            }
        }

        if($confirm_view){
            echo $this->skin->load($this->skin_name, 'confirm.htm', $vars);
        }
        else{
            // 글쓰기 감소 포인트 체크
            // @todo 포인트 감소 구현 필요
//            if($content->execute_action == 'insert' && $board->meta->document_insert_down_point){
//                if(function_exists('mycred_add')){
//                    if(!BackEndAuth::check()){
//                        $login_url = wp_login_url($_SERVER['REQUEST_URI']);
//                        echo '<script>alert("'.__('You do not have permission.', 'board').'");</script>';
//                        echo "<script>top.window.location.href='{$login_url}';</script>";
//                        exit;
//                    }
//                    else{
//                        $balance = mycred_get_users_balance(BackendAuth::getUser()->id);
//                        if($board->meta->document_insert_down_point > $balance){
//                            echo '<script>alert("'.__('You have not enough points.', 'board').'");</script>';
//                            echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
//                            exit;
//                        }
//                    }
//                }
//            }

            // 임시저장된 데이터로 초기화 한다.
            if($content->execute_action == 'insert'){
                $content->initWithTemporary();
            }

            // 내용이 없으면 등록된 기본 양식을 가져온다.
            if(!$content->id && !$content->content){
                $content->content = $this->meta->default_content;
            }
            // 새로운 글 작성 시 기본적으로 비밀글로 설정한다.
            if(!$content->id && $this->meta->secret_checked_default){
                $content->secret = 'true';
            }

            // 새로운 답글 쓰기에서만 실행한다.
            if(Helpers::board_parent_id() && !$content->id && !$content->parent_id){
                $parent = new BoardContentManager();
                $parent->initWithid(Helpers::board_parent_id());

                // 부모 고유번호가 있으면 답글로 등록하기 위해서 부모 고유번호를 등록한다.
                $content->parent_id = $parent->id;

                // 부모의 제목을 가져온다.
                $content->title = 'Re:' . $parent->title;

                // 답글 기본 내용을 설정한다.
                if($this->meta->reply_copy_content=='1'){
                    $content->content = $parent->getContent();
                }
                else if($this->meta->reply_copy_content=='2'){
                    $content->content = $this->meta->default_content;
                }
                else{
                    $content->content = '';
                }
            }

            // 숏코드(Shortcode)를 실행하지 못하게 변경한다.
            $content->content = str_replace('[', '&#91;', $content->getContent());
            $content->content = str_replace(']', '&#93;', $content->getContent());

            if($board->use_editor == 'snote'){ // summernote
                $this->controller->addCss(url('/plugins/placecompany/board/assets/plugins/summernote/summernote-lite.css'),'board');
                $this->controller->addJs(url('/plugins/placecompany/board/assets/plugins/summernote/summernote-lite.js'),'board');

                if(\Session::get('rainlab.translate.locale') == 'kr'){
                    $this->controller->addJs(url('/plugins/placecompany/board/assets/plugins/summernote/lang/summernote-ko-KR.js'),'board');
                }
            }

            $vars['parent'] = isset($parent) ? $parent : new BoardContentManager();

            echo $this->skin->load($this->skin_name, 'editor.htm', $vars);
        }
    }

    /**
     * 게시글 삭제 페이지를 생성한다. (완료 후 바로 리다이렉션)
     */
    public function builderRemove(){
        $url = new BoardUrlManager();
        $url->setBoard($this->board);
        $url->setPath($this->url);

        $content = new BoardContentManager($this->board_id);
        $content->initWithid($this->id);

        if(!$content->id){
            echo '<script>alert("'.Lang::get('placecompany.board::lang.Invalid URL address.').'");</script>';
            echo '<script>window.location.href="' . $url->set('mod', 'list')->toString() . '";</script>';
            exit;
        }

        if($this->isNotAllowed($content->board_id)){
            $message = Lang::get('placecompany.board::lang.This post has been moved.');
            if($message){
                echo '<script>alert("'.$message.'");</script>';
            }
            echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
            exit;
        }

        $confirm_view = false;
        if(!$content->isEditor()){
            if($this->board->permission_write=='all' && !$content->user_id){
                if(!$content->isConfirm(true)){
                    $confirm_view = true;
                }
            }
            else{
                if(\Request::header('referer')){
                    echo '<script>alert("'.Lang::get('placecompany.board::lang.You do not have permission.', 'board').'");history.go(-1);</script>';
                }
                else{
                    echo '<script>alert("'.Lang::get('placecompany.board::lang.You do not have permission.').'");</script>';
                    echo "<script>window.location.href='{$url->set('mod', 'document')->set('id', $content->id)->toString()}';</script>";
                }
                exit;
            }
        }

        if($confirm_view){
            $board = $this->board;
            $content->board = $board;
            $board->content = $content;

            $vars = array(
                'content' => $content,
                'url' => $url,
                'skin' => $this->skin,
                'skin_path' => $this->skin->url($this->skin_name),
                'skin_dir' => $this->skin->dir($this->skin_name),
                'board' => $board,
                'boardBuilder' => $this,
            );

            echo $this->skin->load($this->skin_name, 'confirm.htm', $vars);
        }
        else{
            $delete_immediately = Settings::get('board_content_delete_immediately');

            if($delete_immediately){
                $content->remove();
            }
            else{
                $content->status = 'trash';
                $content->updateContent();
            }

            // 삭제뒤 게시판 리스트로 이동한다.
            echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
            exit;
        }
    }

    /**
     * 최신글 리스트를 생성한다.
     * @param boolean $with_notice
     * @param array $args
     * @return string
     */
    public function createLatest($with_notice=true, $args=array()){
        ob_start();

        $list = new BoardContentListManager($this->board_id);

        if(!is_array($this->board_id) && $this->board->isPrivate()){
            if(BackendAuth::check()){
                $list->memberid(BackendAuth::getUser()->id);
            }
            else{
                $list->stop = true;
            }
        }

        $list->is_latest = true;
        $list->latest = $args;
        $list->category1($this->category1);
        $list->category2($this->category2);
        $list->setSorting($this->sort);
        $list->rpp($this->rpp);
        $list->setDayOfWeek($this->dayofweek);
        $list->setWithinDays($this->within_days);
        $list->setRandom($this->random);
        $list->getList('', '', $with_notice);

        $url = new BoardUrlManager();
        $url->is_latest = true;
        $url->setBoard($this->board);
        $url->setPath($this->url);

        $vars = array(
            'latest' => $args,
            'board_url' => $this->url,
            'list' => $list,
            'url' => $url,
            'skin' => $this->skin,
            'skin_path' => $this->skin->url($this->skin_name),
            'skin_dir' => $this->skin->dir($this->skin_name),
            'board' => $this->board,
            'boardBuilder' => $this,
        );

        echo $this->skin->load($this->skin_name, 'latest.htm', $vars);

        return ob_get_clean();
    }

    public function isNotAllowed($board_id){
        $not_allowed = false;
        $allowed_board_id = $this->board_id;
        \Event::fire('placecompany.board.board_allowed_board_id', [&$allowed_board_id, $this->board]);
        if(is_array($allowed_board_id)){
            if(!in_array($board_id, $allowed_board_id)){
                $not_allowed = true;
            }
        }
        else if($board_id != $allowed_board_id){
            $not_allowed = true;
        }
        return $not_allowed;
    }
}
