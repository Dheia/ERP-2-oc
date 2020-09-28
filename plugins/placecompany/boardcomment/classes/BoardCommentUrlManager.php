<?php namespace Placecompany\BoardComment\Classes;


use Placecompany\Board\Classes\BoardManager;

class BoardCommentUrlManager
{

    var $comment_id;
    var $board;

    public function __construct($comment_id=''){
        if($comment_id) $this->setCommentID($comment_id);
    }

    /**
     * 댓글 ID를 입력한다.
     * @param string $comment_id
     * @return BoardCommentUrlManager
     */
    public function setCommentID($comment_id){
        $this->comment_id = intval($comment_id);
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
     * 댓글 입력 실행 URL
     * @return string
     */
    public function getInsertURL(){
        $url = url("/", ['action' => 'board_comment_insert']);
        \Event::fire('placecompany.board.board_comments_url_insert', [&$url, $this->board]);
        return $url;
    }

    /**
     * 댓글 삭제 실행 URL
     * @return string
     */
    public function getDeleteURL(){
        $url = url("/",['action' => 'board_comment_delete', 'id' => $this->comment_id]);
        \Event::fire('placecompany.board.board_comments_url_delete', [&$url, $this->comment_id, $this->board]);
        return $url;
    }

    /**
     * 댓글 비밀번호 확인 페이지 URL
     * @return string
     */
    public function getConfirmURL(){
        $url = url("/", ['action' => 'board_comment_confirm', 'id' => $this->comment_id]);
        \Event::fire('placecompany.board.board_comments_url_confirm', [&$url, $this->comment_id, $this->board]);
        return $url;
    }

    /**
     * 댓글 수정 페이지 URL
     * @return string
     */
    public function getEditURL(){
        $url = url("/", ['action' => 'board_comment_edit', 'id' => $this->comment_id]);
        \Event::fire('placecompany.board.board_comments_url_edit', [&$url, $this->comment_id, $this->board]);
        return $url;
    }

    /**
     * 댓글 업데이트 실행 URL
     * @return string
     */
    public function getUpdateURL(){
        $url = url("/", ['action' => 'board_comment_update', 'id' => $this->comment_id]);
        \Event::fire('placecompany.board.board_comments_url_update', [&$url, $this->comment_id, $this->board]);
        return $url;
    }

    /**
     * 첨부파일 다운로드 URL을 반환한다.
     * @param string $file_key
     * @return string
     */
    public function getDownloadURLWithAttach($file_key){
        if($this->comment_id){
            $url = url("/", ['action' => 'board_file_download', 'comment_id' => $this->comment_id, 'file' => $file_key]);
        }
        else{
            $url = '';
        }
        \Event::fire('placecompany.board.board_comments_url_file_download', [&$url, $this->comment_id, $file_key, $this->board]);
        return $url;
    }
}
