<?php namespace Placecompany\BoardComment\Classes;


use Backend\Facades\BackendAuth;
use Carbon\Carbon;
use Placecompany\Board\Classes\BoardContentManager;
use Placecompany\Board\Classes\BoardManager;
use Placecompany\Board\Classes\Helpers;
use Placecompany\Board\Classes\SecurityHelpers;
use Placecompany\Board\Models\BoardContent;
use Placecompany\Board\Models\BoardVote;
use Placecompany\BoardComment\Models\BoardComment;
use stdClass;

class BoardCommentManager
{

    private $abspath;

    var $board;
    var $row;
    var $option;
    var $attach;
    var $login_is_required_for_reading;
    var $you_do_not_have_permission_for_reading;
    var $remaining_time_for_reading;

    public function __construct(){
        $this->abspath = rtrim(base_path());
        $this->board = new BoardManager();
        $this->row = new stdClass();
        $this->option = new BoardCommentOptionManager();
    }

    public function __get($name){
        $value = '';
        if(isset($this->row->{$name})){
            if($name == 'content'){
                $content = $this->row->{$name};
                \Event::fire('placecompany.board.board_comments_content', [&$content, $this->row->id, $this->row->content_id]);
                $content = str_replace('[', '&#91;', $content);
                $content = str_replace(']', '&#93;', $content);
                $value = $content;
            }
            else{
                $value = $this->row->{$name};
            }
        }
        \Event::fire('placecompany.board.board_comments_value', [&$value, $name, $this]);
        return $value;
    }

    public function __set($name, $value){
        $this->row->{$name} = $value;
    }

    public function __isset($name)
    {
        $name = strtolower($name);
        $name = preg_replace('/[^a-z0-9_\-]/', '', $name);

        if(property_exists($this->row, $name)){
            return true;
        }

        return false;
    }

    /**
     * 댓글 고유번호를 입력받아 정보를 초기화한다.
     * @param int $id
     * @return BoardCommentManager
     */
    public function initWithID($id){
        $id = intval($id);
        $this->row = BoardComment::find($id);
        $this->option = new BoardCommentOptionManager($this->id);
        $this->initAttachedFiles();
        return $this;
    }

    /**
     * 댓글 정보를 입력받아 초기화한다.
     * @param object $comment
     * @return BoardCommentManager
     */
    public function initWithRow($comment){
        $this->row = $comment;
        $this->option = new BoardCommentOptionManager($this->id);
        $this->initAttachedFiles();
        return $this;
    }

    /**
     * 댓글 첨부파일 정보를 초기화 한다.
     * @return stdClass
     */
    public function initAttachedFiles(){
        $this->attach = new stdClass();
        if($this->id){
            $url = new BoardCommentUrlManager($this->id);
            $url->setBoard($this->getBoard());
            $result = \DB::table('placecompany_board_attached')->where('comment_id', $this->id)->get();
            foreach($result as $row){
                $this->attach->{$row->file_key} = array($row->file_path, $row->file_name, $url->getDownloadURLWithAttach($row->file_key), intval($row->file_size), intval($row->download_count), $row->metadata);
            }
        }
        return $this->attach;
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
     * 관리 권한이 있는지 확인한다.
     * @return boolean
     */
    public function isEditor(){
        if($this->id && BackendAuth::check()){
            $board = $this->getBoard();
            if($board->isAdmin()){
                // 게시판 관리자 허용
                return true;
            }

            if($this->getUserID() == BackendAuth::getUser()->id){
                // 본인인 경우 허용
                return true;
            }
        }
        return false;
    }

    /**
     * 보기 권한이 있는지 확인한다.
     * @return boolean
     */
    public function isReader(){
        if($this->id){
            $board = $this->getBoard();
            if($board->isAdmin()){
                // 게시판 관리자 허용
                return true;
            }

            if($board->meta->permission_comment_read == 'author'){
                if(BackendAuth::check()){
                    return true;
                }
                $this->login_is_required_for_reading = true;
            }
            else if($board->meta->permission_comment_read == 'comment_owner'){
                if(BackendAuth::check()){
                    if($this->getUserID() == BackendAuth::getUser()->id){
                        // 본인인 경우 허용
                        return true;
                    }

                    $content = new BoardContentManager();
                    $content->initWithID($this->content_id);
                    if($content->isEditor()){
                        // 게시글 작성자 허용
                        return true;
                    }
                    $this->you_do_not_have_permission_for_reading = true;
                }
                else{
                    $this->login_is_required_for_reading = true;
                }
            }
            else{
                if(!BackendAuth::check() && $board->meta->permission_comment_read_minute){
                    $this->remaining_time_for_reading = ($board->meta->permission_comment_read_minute * 60) - (Carbon::now()->timestamp - strtotime($this->created));
                    if($this->remaining_time_for_reading <= 0){
                        return true;
                    }
                }
                else{
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 댓글 정보를 업데이트한다.
     */
    public function update(){
        if($this->id){
            foreach($this->row as $key=>$value){
                if($key == 'id') continue;
                else if($key == 'user_display' || $key == 'password'){
                    $value = e($value);
                }
                else if($key == 'content'){
                    $value = SecurityHelpers::board_safeiframe(SecurityHelpers::board_xssfilter($value));
                }
                $key = e(Helpers::sanitize_key($key));
                $value = e($value);
                $update[$key] = $value;
            }

            $comment = BoardComment::find($this->id);
            $comment->save($update);

            // 댓글 수정 액션 훅 실행
            \Event::fire('placecompany.board.board_comments_update', [$this->id, $this->content_id, $this->getBoard()]);
        }
    }

    /**
     * 댓글을 삭제한다.
     * @param boolean $delete_action
     */
    public function delete($delete_action=true){
        if($this->id){
            $board = $this->getBoard();

            if($delete_action){
                // 댓글 삭제 액션 훅 실행
                \Event::fire('placecompany.board.board_comments_delete', [$this->id, $this->content_id, $board]);

                // 댓글삭제 증가 포인트
                if($board->meta->comment_delete_up_point){
                    // @todo 포인트 구현 필요
//                    if($this->user_id){
//                        if(function_exists('mycred_add')){
//                            $point = intval(get_user_meta($this->user_id, 'board_comments_mycred_point', true));
//                            update_user_meta($this->user_id, 'board_comments_mycred_point', $point + $board->meta->comment_delete_up_point);
//
//                            mycred_add('comment_delete_up_point', $this->user_id, $board->meta->comment_delete_up_point, __('Deleted comment increment points', 'board-comments'));
//                        }
//                    }
                }

                // 댓글삭제 감소 포인트
                if($board->meta->comment_delete_down_point){
                    // @todo 포인트 구현 필요
//                    if($this->user_id){
//                        if(function_exists('mycred_add')){
//                            $point = intval(get_user_meta($this->user_id, 'board_comments_mycred_point', true));
//                            update_user_meta($this->user_id, 'board_comments_mycred_point', $point + ($board->meta->comment_delete_down_point*-1));
//
//                            mycred_add('comment_delete_down_point', $this->user_id, ($board->meta->comment_delete_down_point*-1), __('Deleted comment decrease points', 'board-comments'));
//                        }
//                    }
                }
            }

            // 댓글 정보 삭제
            BoardComment::destroy($this->id);

            // 추천 정보 삭제
            BoardVote::where('target_id', $this->id)->where('target_type', 'comment')->delete();

            // 게시글의 댓글 숫자를 변경한다.
            $content = BoardContent::find($this->content_id);
            $content->comment -= 1;
            $content->save();

            $this->deleteAllAttached();

            // 미디어 파일을 삭제한다.
            $media = new BoardCommentMediaManager();
            $media->deleteWithCommentID($this->id);

            // 자식 댓글을 삭제한다.
            $this->deleteChildren();
        }
    }

    /**
     * 내용을 반환한다.
     * @return string
     */
    public function getContent(){
        if(isset($this->row->content)){
            return $this->row->content;
        }
        return '';
    }

    /**
     * 자식 댓글을 삭제한다.
     * @param int $parent_id
     */
    public function deleteChildren($parent_id=''){
        if($this->id){
            if($parent_id){
                $parent_id = intval($parent_id);
            }
            else{
                $parent_id = $this->id;
            }

            $results = BoardComment::where('parent_id', $parent_id)->get();
            foreach($results as $key=>$child){
                BoardComment::find($this->id)->delete();

                // 게시글의 댓글 숫자를 변경한다.
                $content = BoardContent::find($child->content_id);
                $content->comment -= 1;
                $content->save();

                $this->deleteAllAttached($child->id);

                // 미디어 파일을 삭제한다.
                $media = new BoardCommentMediaManager();
                $media->deleteWithCommentID($child->id);

                // 자식 댓글을 삭제한다.
                $this->deleteChildren($child->id);
            }
        }
    }

    /**
     * 작성자 ID를 반환한다.
     * @return int
     */
    public function getUserID(){
        if($this->id && $this->user_id){
            return intval($this->user_id);
        }
        return 0;
    }

    /**
     * 작성자 이름을 반환한다.
     * @return string
     */
    public function getUserName(){
        if($this->id && $this->user_display){
            return $this->user_display;
        }
        return '';
    }

    /**
     * 작성자 이름을 반환한다.
     * @param string $user_display
     * @return string
     */
    public function getUserDisplay($user_display=''){
        global $board_comment_builder;

        if($this->id){
            if(!$user_display){
                $user_display = $this->getUserName();
            }

            $user_id = $this->getUserID();
            $user_name = $this->getUserName();
            $type = 'board-comments';
            $builder = $board_comment_builder;

            \Event::fire('placecompany.board.board_user_display', [&$user_display, $user_id, $user_name, $type, $builder]);
        }
        return $user_display;
    }

    /**
     * 작성자 이름을 읽을 수 없도록 만든다.
     * @param string $replace
     * @return string
     */
    public function getObfuscateName($replace='*'){
        if($this->id && $this->user_display){
            $strlen = mb_strlen($this->user_display, 'utf-8');

            if($strlen > 3){
                $showlen = 2;
            }
            else{
                $showlen = 1;
            }

            $obfuscate_name = mb_substr($this->user_display, 0, $showlen, 'utf-8') . str_repeat($replace, $strlen-$showlen);
            \Event::fire('placecompany.board.board_obfuscate_name', [&$obfuscate_name, $this->user_display, $this->getBoard()]);
            return $obfuscate_name;
        }
        \Event::fire('placecompany.board.board_obfuscate_name', [$this->getBoard()]);
        return '';
    }

    /**
     * 댓글의 모든 첨부파일을 삭제한다.
     */
    public function deleteAllAttached($comment_id=''){
        $comment_id = $comment_id ? intval($comment_id) : $this->id;
        if($comment_id){
            $result = \DB::table('placecompany_board_attached')->select('file_path')->where('comment_id', $comment_id)->get();
            foreach($result as $file){
                Helpers::board_delete_resize($this->abspath . $file->file_path);
                @unlink($this->abspath . $file->file_path);
            }
            \DB::table('placecompany_board_attached')->where('comment_id', $comment_id)->delete();
        }
    }

    /**
     * 첨부파일을 삭제한다.
     * @param string $key
     */
    public function deleteAttached($key){
        if($this->id){
            $key = Helpers::sanitize_key($key);
            $key = e($key);
            $file = \DB::table('placecompany_board_attached')->select('file_path')->where('comment_id', $this->id)->where('file_key', $key)->get();
            if($file){
                Helpers::board_delete_resize($this->abspath . $file);
                @unlink($this->abspath . $file);
                \DB::table('placecompany_board_attached')->where('comment_id', $this->id)->delete();
            }
        }
    }
}
