<?php namespace Placecompany\Board\Classes;
use Illuminate\Support\Facades\Lang;

/**
 * BoardManager 워드프레스 게시판 URL
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardUrlManager {

    private $path;
    private $data;

    var $board;
    var $is_latest = false;

    public function __construct($path=''){
        $this->board = new BoardManager();

        if($path){
            $this->setPath($path);
        }
        else{
            $this->path = '';
        }

        return $this->init();
    }

    /**
     * MOD, ID 값 초기화, URL을 재사용 할 때 오류를 방지한다.
     * @return BoardUrlManager
     */
    public function init(){
        $this->data = get();
        $this->data['mod'] = '';
        $this->data['id'] = '';
        $this->data['rpp'] = '';
        $this->data['sort'] = '';
        $this->data['skin'] = '';
        $this->data['action'] = '';
        $this->data['base_url'] = '';
        $this->data['security'] = '';
        $this->data['board_id'] = '';
        $this->data['order_id'] = '';
        $this->data['parent_id'] = '';
        $this->data['execute_id'] = '';
        $this->data['ajax_builder_type'] = '';
        $this->data['board_list_sort'] = '';
        $this->data['board_list_sort_remember'] = '';
        $this->data['board_comments_sort'] = '';
        $this->data['board-content-remove-nonce'] = '';
        return $this;
    }

    /**
     * 데이터를 비운다.
     * @return BoardUrlManager
     */
    public function clear(){
        $this->data = array();
        return $this;
    }

    /**
     * 게시판을 입력 받는다.
     * @param int|BoardManager $board
     */
    public function setBoard($board){
        if(is_numeric($board)){
            $this->board = new BoardManager($board);
        }
        else{
            $this->board = $board;
        }
    }

    /**
     * 경로를 입력받는다.
     * @param string $path
     * @return BoardUrlManager
     */
    public function setPath($path){
        if($path){
            $url = parse_url($path);
            if(isset($url['query'])){
                $query  = explode('&', html_entity_decode($url['query']));
                foreach($query as $value){
                    list($key, $value) = explode('=', $value);
                    // 중복된 get 값이 있으면 덮어 씌운다.
                    if($value) $this->set($key, $value);
                }
            }
        }
        $this->path = $path;
        return $this;
    }

    /**
     * 안전한 쿼리스트링을 반환한다.
     * @return array $query_string
     */
    public function getCleanQueryString(){
        $query_string = array();
        foreach($this->data as $key=>$value){
            if($value){
                $query_string[$key] = $value;
            }
        }
        return $query_string;
    }

    /**
     * GET 데이터를 입력한다.
     * @param string $key
     * @param string $value
     * @return BoardUrlManager
     */
    public function set($key, $value){
        $key = Helpers::sanitize_key($key);
        $value = filter_var($value, FILTER_SANITIZE_STRING);
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * URL 반환한다.
     * @return string
     */
    public function toString(){
        $query_string = $this->getCleanQueryString();
        $this->init();
        if($this->path){
            return url($this->path, $query_string);
        }
        else if($this->is_latest){
            return $this->getDocumentRedirect($query_string['id']);
        }
        else{
            return url()->current()."?".http_build_query($query_string);
        }
    }

    /**
     * 경로를 입력받아 URL 반환한다.
     * @return string
     */
    public function toStringWithPath($path){
        // 경로가 없을경우
        if(!$path && $this->data['id']){
            return $this->getDocumentRedirect($this->data['id']);
        }

        $this->setPath($path);

        $query_string = $this->getCleanQueryString();
        $this->init();

        return url($this->path, $query_string);
    }

    /**
     * INPUT으로 반환한다.
     * @return string
     */
    public function toInput(){
        foreach($this->data as $key=>$value){
            if(is_array($value)){

            }
            else if($value){
                $input[] = '<input type="hidden" name="' . Helpers::sanitize_key($key) .'" value="' . filter_var($value, FILTER_SANITIZE_STRING) . '">';
            }
        }
        $this->init();
        return isset($input) ? implode('', $input) : '';
    }

    /**
     * 첨부파일 다운로드 URL을 반환한다.
     * @param int $content_id
     * @param string $file_key
     * @return string
     */
    public function getDownloadURLWithAttach($content_id, $file_key){
        $content_id = intval($content_id);
        if($content_id){
            $url = url('/', [
                'action' => 'board_file_download',
                'id' => $content_id,
                'file' => $file_key,
            ]);
        }
        else{
            $url = '';
        }
        \Event::fire('placecompany.board.board_url_file_download', [&$url, $content_id, $file_key, $this->board]);
        return $url;
    }

    /**
     * 첨부파일 삭제 URL을 반환한다.
     * @param int $content_id
     * @param string $file_key
     * @return string
     */
    public function getDeleteURLWithAttach($content_id, $file_key='thumbnail'){
        $content_id = intval($content_id);
        if($content_id){
            $url = url('/', [
                'action' => 'board_file_delete',
                'id' => $content_id,
                'file' => $file_key,
            ]);
        }
        else{
            $url = '';
        }
        \Event::fire('placecompany.board.board_url_file_delete', [&$url, $content_id, $file_key, $this->board]);
        return $url;
    }

    /**
     * 첨부파일 다운로드 URL을 반환한다.
     * @param int $content_id
     * @param string $file_key
     * @param int $order_item_id
     * @return string
     */
    public function getDownloadURLWithAttachAndOderItemID($content_id, $file_key, $order_item_id){
        $content_id = intval($content_id);
        if($content_id){
            $url = url('/', [
                'action' => 'board_file_download',
                'id' => $content_id,
                'file' => $file_key,
                'order_item_id' => $order_item_id,
            ]);
        }
        else{
            $url = '';
        }
        \Event::fire('placecompany.board.board_url_file_download_order', [&$url, $content_id, $file_key, $order_item_id, $this->board]);
        return $url;
    }

    /**
     * 게시글 주소를 반환한다.
     * @param int $content_id
     * @return string
     */
    public function getDocumentURLWithID($content_id){
        $content_id = intval($content_id);
        if($content_id){
            $this->data['id'] = $content_id;
            $this->data['mod'] = 'document';
            $url = $this->toString();
        }
        else{
            $url = "javascript:alert('".Lang::get('placecompany.board::lang.No document.')."')";
        }
        \Event::fire('placecompany.board.board_url_document_id', [&$url, $content_id, $this->board]);
        return $url;
    }

    /**
     * 라우터를 이용해 글게시 본문으로 이동한다.
     * @param int $content_id
     * @return string
     */
    public function getDocumentRedirect($content_id){
        $content_id = intval($content_id);
        if($content_id){
            $url = url('/', ['board_content_redirect' => $content_id]);
        }
        else{
            $url = '';
        }
        \Event::fire('placecompany.board.board_url_document_redirect', [&$url, $content_id, $this->board]);
        return $url;
    }

    /**
     * 라우터를 이용해 게시판으로 이동한다.
     * @param int $board_id
     * @return string
     */
    public function getBoardRedirect($board_id){
        $board_id = intval($board_id);
        if($board_id){
            $url = url('/', ['board_redirect' => $board_id]);
        }
        else{
            $url = '';
        }
        \Event::fire('placecompany.board.board_url_board_redirect', [&$url, $board_id, $this->board]);
        return $url;
    }

    /**
     * 글 저장 페이지 URL을 반환한다.
     */
    public function getContentEditorExecute(){
        return '';
    }

    /**
     * 주문 저장 페이지 URL을 반환한다.
     */
    public function getOrderExecute(){
        return '';
    }

    /**
     * 소셜댓글 플러그인에서 사용할 게시글 주소를 반환한다.
     * @param int $content_id
     * @return string
     */
    public function getCommentsPluginURLWithID($content_id){
        $content_id = intval($content_id);
        if($content_id){
            return $this->getDocumentRedirect($content_id);
        }
        return '';
    }

    /**
     * 게시글을 프린트하기 위한 주소를 반환한다.
     * @param int $content_id
     * @return string
     */
    public function getDocumentPrint($content_id){
        $content_id = intval($content_id);
        if($content_id){
            $url = url('/', ['action' => 'board_document_print', 'id' => $content_id]);
        }
        else{
            $url = '';
        }
        \Event::fire('board_url_document_print', [&$url, $content_id, $this->board]);
        return $url;
    }

    /**
     * 게시글 삭제 주소를 반환한다.
     * @param int $content_id
     * @return string
     */
    public function getContentRemove($content_id){
        $content_id = intval($content_id);
        if($content_id){
            $this->data['id'] = $content_id;
            $this->data['mod'] = 'remove';
            $url = url($this->toString());
        }
        else{
            $url = '';
        }
        \Event::fire('placecompany.board.board_url_content_remove', [&$url, $content_id, $this->board]);
        return $url;
    }

    /**
     * 게시글 작성 주소를 반환한다.
     * @param string $content_id
     * @return string
     */
    public function getContentEditor($content_id=''){
        $content_id = intval($content_id);

        if($content_id){
            $this->data['id'] = $content_id;
            $this->data['mod'] = 'editor';
            $url = $this->toString();
        }
        else{
            $this->data['mod'] = 'editor';
            $url = $this->toString();
        }
        \Event::fire('placecompany.board.Board_url_content_editor', [&$url, $content_id, $this->board]);
        return $url;
    }

    /**
     * 게시글 목록 주소를 반환한다.
     * @return string
     */
    public function getBoardList(){
        $this->data['mod'] = 'list';
        $url = $this->toString();

        \Event::fire('placecompany.board.Board_url_board_list', [&$url, $this->board]);
        return $url;
    }

    /**
     * 게시글의 비밀번호를 다시 확인하는 주소를 반환한다.
     * @param int $content_id
     * @return string
     */
    public function getConfirmExecute($content_id){
        $content_id = intval($content_id);
        if(isset($_GET['BoardManager-content-remove-nonce']) && $_GET['BoardManager-content-remove-nonce']){
            $url = $this->getContentRemove($content_id);
        }
        else{
            $this->data['mod'] = Helpers::board_mod();
            $this->data['id'] = $content_id;
            $url = $this->toString();
        }
        \Event::fire('placecompany.board.Board_url_content_editor', [&$url, $content_id, $this->board]);
        return $url;
    }
}
