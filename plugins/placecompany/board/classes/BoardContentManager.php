<?php namespace Placecompany\Board\Classes;

use Backend\Facades\BackendAuth;
use Carbon\Carbon;
use File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Indikator\DevTools\FormWidgets\Help;
use October\Rain\Support\Facades\Flash;
use Placecompany\Board\Models\BoardContent;
use Placecompany\Board\Models\BoardVote;
use Placecompany\BoardComment\Classes\BoardCommentListManager;
use stdClass;
use ToughDeveloper\ImageResizer\Classes\Image;

/**
 * KBoard 게시글
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardContentManager {

    private $upload_attach_files;
    private $filter_keys;
    private $abspath;

    // 스킨에서 사용 할 첨부파일 input[type=file] 이름의 prefix를 정의한다.
    static $SKIN_ATTACH_PREFIX = 'board_attach_';
    // 스킨에서 사용 할 사용자 정의 옵션 input, textarea, select 이름의 prefix를 정의한다.
    static $SKIN_OPTION_PREFIX = 'board_option_';

    var $board;
    var $board_id;
    var $option;
    var $attach;
    var $attach_store_path;
    var $thumbnail_store_path;
    var $row;
    var $execute_action;
    var $thumbnail;
    var $previous_status;
    var $previous_board_id;
    var $tree_category_depth;
    var $new_password;

    public function __construct($board_id=''){
        $this->abspath = base_path();
        $this->row = new stdClass();
        $this->execute_action = 'insert';
        if($board_id) $this->setBoardID($board_id);
    }

    public function __get($name){
        $value = '';

        if(isset($this->row->{$name})){
            if(in_array($name, array('title', 'content'))){
                if(isset($this->row->status) && $this->row->status == 'pending_approval' && in_array(Helpers::board_mod(), array('list', 'document'))){
                    if($this->isEditor()){
                        switch($name){
                            case 'title':
                                $message = Lang::get('placecompany.board::lang.&#91;Pending&#93; :title', ['title'=>$this->row->title]);
                                \Event::fire('placecompany.board.board_pending_approval_title', [&$message, $this]);
                                return $message;
                                break;
                            case 'content':
                                $message = Lang::get('placecompany.board::lang.&#91;Pending&#93; :title', ['title'=>$this->row->content]);
                                \Event::fire('placecompany.board.board_pending_approval_content', [&$message, $this]);
                                return $message;
                                break;
                        }
                    }
                    else{
                        switch($name){
                            case 'title':
                                $message = Lang::get('placecompany.board::lang.&#91;Pending&#93; Waiting for administrator Approval.');
                                \Event::fire('placecompany.board.board_pending_approval_title', [&$message, $this]);
                                return $message;
                                break;
                            case 'content':
                                $message = Lang::get('placecompany.board::lang.&#91;Waiting for administrator Approval.&#93;');
                                \Event::fire('placecompany.board.board_pending_approval_content', [&$message, $this]);
                                return $message;
                                break;
                        }
                    }
                }
            }
            $value = $this->row->{$name};
        }
        \Event::fire('placecompany.board.board_content_value', [&$value, $name, $this]);

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
     * 게시판 ID를 입력받는다.
     * @param int $board_id
     */
    public function setBoardID($board_id){
        $this->board_id = intval($board_id);
        $this->board = new BoardManager($this->board_id);

        // 첨부파일 업로드 경로를 만든다.
        $upload_dir = storage_path();
        $this->attach_store_path = str_replace($this->abspath, '', $upload_dir) . "/placecompany/board/board_attached/{$this->board_id}/" . date('Ym', Carbon::now()->timestamp) . '/';
        $this->thumbnail_store_path = str_replace($this->abspath, '', $upload_dir) . "/placecompany/board/board_thumbnails/{$this->board_id}/" . date('Ym', Carbon::now()->timestamp) . '/';
    }

    /**
     * 게시글 고유번호를 입력받아 정보를 초기화한다.
     * @param int $id
     * @return BoardContentManager
     */
    public function initWithID($id){
        $id = intval($id);
        if($id){
            $this->row = \DB::table('placecompany_board_content')->find($id);
            if($this->row){
                $this->setBoardID($this->row->board_id);
            }
            else{
                $this->row = new stdClass();
            }
        }
        else{
            $this->row = new stdClass();
        }
        $this->initOptions();
        $this->initAttachedFiles();
        $this->setExecuteAction();
        $this->previous_status = $this->status;
        $this->previous_board_id = $this->board_id;
        $this->new_password = $this->password;
        return $this;
    }

    /**
     * 게시글 정보를 입력받는다.
     * @param object $row
     * @return BoardContentManager
     */
    public function initWithRow($row){
        if($row){
            $this->row = $row;
            $this->setBoardID($this->row->board_id);
        }
        else{
            $this->row = new stdClass();
        }

        $this->initOptions();
        $this->initAttachedFiles();
        $this->setExecuteAction();
        $this->previous_status = $this->status;
        $this->previous_board_id = $this->board_id;
        $this->new_password = $this->password;
        return $this;
    }

    /**
     * 실행 액션을 설정한다.
     * @param string $action
     */
    public function setExecuteAction($action=''){
        if($action){
            $this->execute_action = $action;
        }
        else if($this->id && $this->updated_at){
            $this->execute_action = 'update';
        }
        else{
            $this->execute_action = 'insert';
        }
    }

    /**
     * 게시글을 등록/수정한다.
     * @return int
     */
    public function execute(){
        $board = $this->getBoard();

        if($this->execute_action == 'update'){
            /*
             * 기존 게시글 업데이트
             */

            // 게시글 수정 전에 액션 훅 실행
            \Event::fire('placecompany.board.board_pre_document_update', [$this->id, $this->board_id, $this, $board]);

            $this->initUploadAttachFiles();
            $this->updateContent();
            $this->setThumbnail();
            $this->updateOptions();
            $this->updateAttach();
            $this->addMediaRelationships();

            // 게시글 수정 액션 훅 실행
            \Event::fire('placecompany.board.board_document_update', [$this->id, $this->board_id, $this, $board]);

            // 임시저장 데이터 삭제
            $this->cleanTemporary();

            return $this->id;
        }
        else if($this->execute_action == 'insert'){
            /*
             * 신규 게시글 등록
             */

            // Captcha 검증
            if($board->useCAPTCHA()){
                $fields = $board->fields()->getSkinFields();
                if(isset($fields['captcha'])){
                    $captcha = new BoardCaptchaManager();

                    if(!$captcha->validate()){
                        Flash::error(Lang::get('placecompany.board::lang.CAPTCHA is invalid.'));
                        return redirect()->back();
                    }
                }
            }

            if($board->meta->permit){
                // 게시글 승인 대기
                $this->status = 'pending_approval';
            }

            // 글쓴이의 id값 등록
            $this->user_id = BackendAuth::getUser() ? BackendAuth::getUser()->id : 0;

            // 게시글 입력 전에 액션 훅 실행
            \Event::fire('placecompany.board.board_pre_document_insert', [0, $this->board_id, $this, $board]);

            $this->initUploadAttachFiles();
            if($this->insertContent()){
                $this->setThumbnail();
                $this->updateOptions();
                $this->updateAttach();
                $this->addMediaRelationships();

                // 게시판 설정에 알림 이메일이 설정되어 있으면 메일을 보낸다.
                if($board->meta->latest_alerts){
                    $this->initAttachedFiles();

                    /*
                     * http://www.cosmosfarm.com/threads/document/3025
                     * 메일 제목에 게시글이 등록된 게시판 이름 추가해서 보낸다.
                     */
                    $url = new BoardUrlManager();
                    $mail = new BoardMailManager();
                    $mail->to = explode(',', $board->meta->latest_alerts);
                    \Event::fire('placecompany.board.board_latest_alerts_subject', ['['.Lang::get('placecompany.board::lang.Board new document').'] '.$board->board_name.' - '.$this->title, $this]);
                    $mail->title = '['.Lang::get('placecompany.board::lang.Board new document').'] '.$board->board_name.' - '.$this->title;
                    \Event::fire('placecompany.board.board_latest_alerts_message', [$this->getDocumentOptionsHTML() . $this->content, $this]);
                    $mail->content = $this->getDocumentOptionsHTML();
                    $mail->url = $url->getDocumentRedirect($this->id);
                    $mail->url_name = Lang::get('placecompany.board::lang.Go to Homepage');
                    $mail->attachments = $this->getMailAttachments();
                    $mail->send();

                    $this->deleteMailAttachments();
                }

                // 게시글 입력 액션 훅 실행
                \Event::fire('placecompany.board.board_document_insert', [$this->id, $this->board_id, $this, $board]);

                // 임시저장 데이터 삭제
                $this->cleanTemporary();
            }

            return $this->id;
        }
        return 0;
    }

    /**
     * 게시글을 등록한다.
     * @return int
     */
    public function insertContent($data = array()){

        if(!$data){
            $data['board_id'] = $this->board_id;
            $data['parent_id'] = $this->parent_id;
            $data['user_id'] = $this->user_id;
            $data['user_display'] = $this->user_display;
            $data['title'] = $this->title;
            $data['content'] = $this->content;
            $data['view'] = 0;
            $data['comment'] = 0;
            $data['like'] = 0;
            $data['unlike'] = 0;
            $data['vote'] = 0;
            $data['category1'] = $this->category1;
            $data['category2'] = $this->category2;
            $data['secret'] = $this->secret;
            $data['notice'] = $this->notice;
            $data['search'] = $this->search;
            $data['thumbnail_file'] = '';
            $data['thumbnail_name'] = '';
            $data['status'] = $this->status;
            $data['password'] = $this->new_password;
        }

        // 입력할 데이터 필터
        \Event::fire('placecompany.board.board_insert_data', [&$data, $this->board_id]);

        if(!$data['user_display']){
            $data['user_display'] = 'Anonymous';
        }

        if(!in_array($data['status'], array('trash', 'pending_approval'))){
            $data['status'] = '';
        }

        $data['title'] = $this->titleStripTags($data['title']);
        $data['title'] = $this->encodeEmoji($data['title']);

        $data['content'] = $this->encodeEmoji($data['content']);

        // 불필요한 데이터 필터링
        $data = Helpers::board_array_filter($data, array('board_id', 'parent_id', 'user_id', 'user_display', 'title', 'content', 'view', 'comment', 'like', 'unlike', 'vote', 'category1', 'category2', 'secret', 'notice', 'search', 'thumbnail_file', 'thumbnail_name', 'status', 'password'));

        if($data['board_id'] && $data['title']){
            foreach($data as $key=>$value){
                $this->{$key} = $value;

                if(empty($value) && $value != 0) {
                    $data[$key] = '';
                }
            }

            $board = $this->getBoard();
            $board_total = $board->getTotal();
            $board_list_total = $board->getListTotal();

            if($this->status != 'trash'){
                $board->meta->total = $board_total + 1;
                $board->meta->list_total = $board_list_total + 1;
            }
            else{
                $board->meta->total = $board_total + 1;
            }

            $content = BoardContent::create($data);
            $this->id = $content->id;

            return $this->id;
        }
        return 0;
    }

    /**
     * 게시글 정보를 수정한다.
     */
    public function updateContent($data = array()){

        if($this->id){
            if(!$data){
                $data['board_id'] = $this->board_id;
                $data['parent_id'] = $this->parent_id?$this->parent_id:0;
                $data['user_id'] = $this->user_id;
                $data['user_display'] = $this->user_display;
                $data['title'] = $this->title;
                $data['content'] = $this->content;
                $data['date'] = $this->date;
                $data['update'] = $this->update;
                $data['view'] = $this->view;
                $data['comment'] = $this->comment;
                $data['like'] = $this->like;
                $data['unlike'] = $this->unlike;
                $data['vote'] = $this->vote;
                $data['category1'] = $this->category1;
                $data['category2'] = $this->category2;
                $data['secret'] = $this->secret;
                $data['notice'] = $this->notice;
                $data['search'] = $this->search;
                $data['thumbnail_file'] = $this->thumbnail_file;
                $data['thumbnail_name'] = $this->thumbnail_name;
                $data['status'] = $this->status;
                if($this->user_id || $this->password) $data['password'] = $this->new_password;
            }

            // 수정할 데이터 필터
            \Event::fire('placecompany.board.board_update_data', [$data, $this->board_id]);

            // sanitize
            if(isset($data['board_id'])) $data['board_id'] = intval($data['board_id']);
            if(isset($data['parent_id'])) $data['parent_id'] = intval($data['parent_id']);
            if(isset($data['user_id'])) $data['user_id'] = intval($data['user_id']);
            if(isset($data['user_display'])) $data['user_display'] = e($data['user_display']);
            if(isset($data['title'])) $data['title'] = SecurityHelpers::board_safeiframe(SecurityHelpers::board_xssfilter($data['title']));
            if(isset($data['content'])) $data['content'] = SecurityHelpers::board_safeiframe(SecurityHelpers::board_xssfilter($data['content']));
            if(isset($data['view'])) $data['view'] = intval($data['view']);
            if(isset($data['comment'])) $data['comment'] = intval($data['comment']);
            if(isset($data['like'])) $data['like'] = intval($data['like']);
            if(isset($data['unlike'])) $data['unlike'] = intval($data['unlike']);
            if(isset($data['vote'])) $data['vote'] = intval($data['vote']);
            if(isset($data['category1'])) $data['category1'] = e($data['category1']);
            if(isset($data['category2'])) $data['category2'] = e($data['category2']);
            if(isset($data['secret'])) $data['secret'] = Helpers::sanitize_key($data['secret']);
            if(isset($data['notice'])) $data['notice'] = Helpers::sanitize_key($data['notice']);
            if(isset($data['search'])) $data['search'] = intval($data['search']);
            if(isset($data['thumbnail_file'])) $data['thumbnail_file'] = e($data['thumbnail_file']);
            if(isset($data['thumbnail_name'])) $data['thumbnail_name'] = e($data['thumbnail_name']);
            if(isset($data['status'])) $data['status'] = Helpers::sanitize_key($data['status']);
            if(isset($data['password'])) $data['password'] = e($data['password']);

            if(isset($data['user_display']) && !$data['user_display']){
                $data['user_display'] = Lang::get('placecompany.board::lang.Anonymous');
            }

            if(isset($data['status']) && !in_array($data['status'], array('trash', 'pending_approval'))){
                $data['status'] = '';
            }

            if(isset($data['title'])){
                $data['title'] = $this->titleStripTags($data['title']);
                $data['title'] = $this->encodeEmoji($data['title']);
            }

            if(isset($data['content'])){
                $data['content'] = $this->encodeEmoji($data['content']);
            }

            // 불필요한 데이터 필터링
            $data = Helpers::board_array_filter($data, array('board_id', 'parent_id', 'user_id', 'user_display', 'title', 'content', 'view', 'comment', 'like', 'unlike', 'vote', 'category1', 'category2', 'secret', 'notice', 'search', 'thumbnail_file', 'thumbnail_name', 'status', 'password'));

            if(isset($data['status']) && $this->previous_status != $data['status']){
                if($data['status'] == 'trash'){
                    $this->moveReplyToTrash($this->id);
                }
                else if($this->previous_status == 'trash'){
                    $this->restoreReplyFromTrash($this->id);
                }
            }

            BoardContent::find($this->id)->update($data);

            if(isset($data['board_id']) && $this->previous_board_id != $data['board_id']){
                $this->changeBoardID($data['board_id']);
            }

        }
    }

    /**
     * 게시글의 조회수를 증가한다.
     */
    public function increaseView(){
        if($this->id && !@in_array($this->id, session('increased_document_id'))){
            \Session::push('increased_document_id', $this->id);
            \DB::table('placecompany_board_content')->where('id', $this->id)
                ->increment('view', 1);
            $this->view++;
        }
    }

    /**
     * 게시글 옵션 정보를 초기화 한다.
     */
    public function initOptions(){
        $this->option = new BoardContentOption($this->id);
    }

    /**
     * 게시글 첨부파일 정보를 초기화 한다.
     * @return string
     */
    public function initAttachedFiles(){
        $this->attach = new stdClass();
        if($this->id){
            $url = new BoardUrlManager();
            $result = \DB::table('placecompany_board_attached')->where('content_id', $this->id)->get();
            foreach($result as $file){
                $file_info = array(
                    0 => $file->file_path,
                    1 => $file->file_name,
                    2 => $url->getDownloadURLWithAttach($this->id, $file->file_key),
                    3 => intval($file->file_size),
                    4 => intval($file->download_count),
                    'file_path' => $file->file_path,
                    'file_name' => $file->file_name,
                    'file_size' => intval($file->file_size),
                    'download_url' => $url->getDownloadURLWithAttach($this->id, $file->file_key),
                    'download_count' => intval($file->download_count),
                    'metadata' => ($file->metadata ? unserialize($file->metadata) : array())
                );

                \Event::fire('placecompany.board.board_content_file_info', [&$file_info, $file, $this]);

                $this->attach->{$file->file_key} = $file_info;
            }
        }
        return $this->attach;
    }

    /**
     * 첨부파일을 초기화한다.
     */
    public function initUploadAttachFiles(){
        if(!$this->attach_store_path) {
            Flash::error(Lang::get('placecompany.board::lang.No upload path. Please enter board ID and initialize.'));
            return redirect()->back();
        }

        // 업로드된 파일이 있는지 확인한다. (없으면 중단)
        $upload_checker = false;

        foreach($_FILES as $key=>$value){
            if(strpos($key, BoardContentManager::$SKIN_ATTACH_PREFIX) === false) continue;
            if($_FILES[$key]['tmp_name']){
                $upload_checker = true;
                break;
            }
        }

        if($upload_checker){
            $file = new BoardFileHandler($this->attach_store_path);

            foreach($_FILES as $key=>$value){
                if(strpos($key, BoardContentManager::$SKIN_ATTACH_PREFIX) === false) continue;
                $key = str_replace(BoardContentManager::$SKIN_ATTACH_PREFIX, '', $key);
                $key = Helpers::sanitize_key($key);

                $upload = $file->upload(BoardContentManager::$SKIN_ATTACH_PREFIX . $key);

                $file_path = $upload['path'] . $upload['stored_name'];
                $file_name = $upload['original_name'];
                $metadata = $upload['metadata'];

                if($file_name){
                    $attach_file = new stdClass();
                    $attach_file->key = $key;
                    $attach_file->path = $file_path;
                    $attach_file->name = $file_name;
                    $attach_file->metadata = $metadata;
                    $this->upload_attach_files[] = $attach_file;
                }
            }
        }
    }

    /**
     * 게시글의 첨부파일을 업데이트한다. (입력/수정)
     */
    public function updateAttach(){
        if(!$this->attach_store_path) die(__('No upload path. Please enter board ID and initialize.', 'board'));

        if($this->id && $this->upload_attach_files && is_array($this->upload_attach_files)){
            foreach($this->upload_attach_files as $file){
                $file_key = $file->key;
                $file_path = $file->path;
                $file_name = $file->name;
                $file_size = intval(filesize($this->abspath . $file_path));

                \Event::fire('placecompany.board.board_content_file_metadata', [&$file->metadata, $file, $this]);
                $metadata = $file->metadata;
                $metadata = serialize($metadata);

                $present_file = \DB::table('placecompany_board_attached')->where('content_id', $this->id)->where('file_key', $file_key)->get();
                if($present_file){
                    @unlink($this->abspath . $present_file);
                    $date = date('YmdHis', Carbon::now()->timestamp);
                    \DB::table('placecompany_board_attached')->where('content_id', $this->id)->where('file_key', $file_key)
                        ->update(
                            [
                                'file_path' => $file_path,
                                'file_name' => $file_name,
                                'file_size' => $file_size,
                                'metadata' => $metadata,
                                'updated_at' => $date,
                            ]
                        );
                }
                else{
                    $date = date('YmdHis', Carbon::now()->timestamp);
                    \DB::table('placecompany_board_attached')
                        ->insert(
                            [
                                'content_id' => $this->id,
                                'comment_id' => 0,
                                'file_key' => $file_key,
                                'created_at' => $date,
                                'file_path' => $file_path,
                                'file_name' => $file_name,
                                'file_size' => $file_size,
                                'download_count' => 0,
                                'metadata' => $metadata
                            ]
                        );
                }
            }
        }
        else if($this->upload_attach_files && is_array($this->upload_attach_files)){
            foreach($this->upload_attach_files as $file){
                Helpers::board_delete_resize($this->abspath . $file->path);
                @unlink($this->abspath . $file->path);
            }
        }
    }

    /**
     * 게시글의 모든 첨부파일을 삭제한다.
     */
    private function _deleteAllAttached(){
        if($this->id){
            $result = \DB::table('placecompany_board_attached')->select('file_path')->where('content_id', $this->id)->get();
            foreach($result as $file){
                Helpers::board_delete_resize($this->abspath . $file->file_path);
                @unlink($this->abspath . $file->file_path);
            }
            \DB::table('placecompany_board_attached')->where('content_id', $this->id)->delete();
        }
    }

    /**
     * 첨부파일을 삭제한다.
     * @param string $key
     */
    public function removeAttached($key){
        if($this->id){
            $key = Helpers::sanitize_key($key);
            $file = \DB::table('placecompany_board_attached')->select('file_path')->where('content_id', $this->id)->where('file_key', $key)->get();
            if($file){
                Helpers::board_delete_resize($this->abspath . $file);
                @unlink($this->abspath . $file);
                \DB::table('placecompany_board_attached')->where('content_id', $this->id)->where('file_key', $key)->delete();
            }
        }
    }

    /**
     * 게시글의 옵션값을 반환한다.
     * @param string $option_name
     * @return string|array
     */
    public function getOptionValue($option_name){
        return $this->option->{$option_name};
    }

    /**
     * 게시글의 옵션을 저장한다.
     * @param array $options
     */
    public function updateOptions($options=array()){
        if($this->id){
            if(!$options) $options = $_POST;
            $this->option = new BoardContentOption($this->id);
            foreach($options as $key=>$value){
                if(strpos($key, BoardContentManager::$SKIN_OPTION_PREFIX) !== false){
                    $key = str_replace(BoardContentManager::$SKIN_OPTION_PREFIX, '', $key);
                    $key = Helpers::sanitize_key($key);
                    $value = e($value);
                    $value = SecurityHelpers::board_safeiframe($value);
                    $this->option->{$key} = $value;
                }
            }
        }
    }

    /**
     * 옵션을 삭제한다.
     */
    private function _deleteAllOptions(){
        if($this->id){
            \DB::table('placecompany_board_option')->where('content_id', $this->id)->get();
        }
    }

    /**
     * 썸네일을 등록한다.
     */
    public function setThumbnail(){
        if(!$this->thumbnail_store_path) die(__('No upload path. Please enter board ID and initialize.', 'board'));
        if($this->id && isset($_FILES['thumbnail']) && $_FILES['thumbnail']['tmp_name']){
            $file = new BoardFileHandler();
            $file->setPath($this->thumbnail_store_path);
            $upload = $file->upload('thumbnail');
            $thumbnail_name = e($upload['original_name']);
            $thumbnail_file = e($upload['path'] . $upload['stored_name']);
            if($thumbnail_name){
                $thumbnail_size = array(1200, 1200);
                \Event::fire('placecompany.board.board_thumbnail_size', [&$thumbnail_size]);
                if($thumbnail_size){
                    // 업로드된 원본 이미지 크기를 줄인다.
                    $upload_dir = storage_path();
                    $basedir = str_replace(base_path(), '', $upload_dir);
                    $file_path = explode("/{$basedir}", $upload['path'] . $upload['stored_name']);
                    $file_path = strtolower($upload_dir . end($file_path));
                    $image_editor = new Image($file_path);
                    if($image_editor){
                        $image_editor->resize($thumbnail_size[0], $thumbnail_size[1]);
                    }
                }
                $this->removeThumbnail(false);
                \DB::table('placecompany_board_content')->where('id', $this->id)->update(['thumbnail_file'=>$thumbnail_file, 'thumbnail_name'=>$thumbnail_name]);
            }
        }
    }

    /**
     * 썸네일 주소를 반환한다.
     * @param string $width
     * @param string $height
     * @return string
     */
    public function getThumbnail($width='', $height=''){
        $size = array('width'=>$width, 'height'=>$height);
        \Event::fire('placecompany.board.board_content_get_thumbnail_size', [&$size, $this]);
        $width = isset($size['width']) ? intval($size['width']) : '';
        $height = isset($size['height']) ? intval($size['height']) : '';

        $thumbnail_url = '';
        if(isset($this->thumbnail["{$width}x{$height}"]) && $this->thumbnail["{$width}x{$height}"]){
            $thumbnail_url = $this->thumbnail["{$width}x{$height}"];
        }
        else if($this->thumbnail_file){
            if($width && $height){
                $this->thumbnail["{$width}x{$height}"] = Helpers::board_resize($this->thumbnail_file, $width, $height);
            }
            else{
                $this->thumbnail["{$width}x{$height}"] = url($this->thumbnail_file);
            }
            $thumbnail_url = $this->thumbnail["{$width}x{$height}"];
        }
        else if($this->id){
            $media = new BoardContentMediaManager();
            $media->content_id = $this->id;
            foreach($media->getList() as $media_item){
                if($thumbnail_url) break;
                if(isset($media_item->file_path) && $media_item->file_path){
                    if($width && $height){
                        $this->thumbnail["{$width}x{$height}"] = Helpers::board_resize($media_item->file_path, $width, $height);
                    }
                    else{
                        $this->thumbnail["{$width}x{$height}"] = url($media_item->file_path);
                    }
                    $thumbnail_url = $this->thumbnail["{$width}x{$height}"];
                }
            }
            if(!$thumbnail_url){
                foreach($this->attach as $attach){
                    if($thumbnail_url) break;
                    $extension = strtolower(pathinfo($attach[0], PATHINFO_EXTENSION));
                    if(in_array($extension, array('gif','jpg','jpeg','png'))){
                        if($width && $height){
                            $this->thumbnail["{$width}x{$height}"] = Helpers::board_resize($attach[0], $width, $height);
                        }
                        else{
                            $this->thumbnail["{$width}x{$height}"] = url($attach[0]);
                        }
                        $thumbnail_url = $this->thumbnail["{$width}x{$height}"];
                    }
                }
            }
        }
        \Event::fire('placecompany.board.board_content_get_thumbnail', [&$thumbnail_url, $width, $height, $this]);
        return $thumbnail_url;
    }

    /**
     * 게시글을 삭제한다.
     * @param boolean $delete_action
     */
    public function delete($delete_action=true){
        $this->remove($delete_action);
    }

    /**
     * 게시글을 삭제한다.
     * @param boolean $delete_action
     */
    public function remove($delete_action=true){
        if($this->id){
            $board = $this->getBoard();

            if($delete_action){
                // 게시글 삭제 전에 액션 실행
                \Event::fire('placecompany.board.board_pre_document_delete', [$this->id, $this->board_id, $this, $board]);

                // 글삭제 증가 포인트
                if($board->meta->document_delete_up_point){
                    /**
                     * @todo 포인트 구현 필요
                     */
//                    if($this->user_id){
//                        if(function_exists('mycred_add')){
//                            $point = intval(get_user_meta($this->user_id, 'board_document_mycred_point', true));
//                            update_user_meta($this->user_id, 'board_document_mycred_point', $point + $board->meta->document_delete_up_point);
//
//                            mycred_add('document_delete_up_point', $this->user_id, $board->meta->document_delete_up_point, __('Deleted increment points', 'board'));
//                        }
//                    }
                }

                // 글쓰기 감소 포인트
                if($board->meta->document_delete_down_point){
                    /**
                     * @todo 포인트 구현 필요
                     */
//                    if($this->user_id){
//                        if(function_exists('mycred_add')){
//                            $point = intval(get_user_meta($this->user_id, 'board_document_mycred_point', true));
//                            update_user_meta($this->user_id, 'board_document_mycred_point', $point + ($board->meta->document_delete_down_point*-1));
//
//                            mycred_add('document_delete_down_point', $this->user_id, ($board->meta->document_delete_down_point*-1), __('Deleted decrease points', 'board'));
//                        }
//                    }
                }
            }

            $board->meta->total = $board->getTotal() - 1;
            if($this->status != 'trash'){
                $board->meta->list_total = $board->getListTotal() - 1;
            }

            $this->_deleteAllOptions();
            $this->_deleteAllAttached();
            $this->removeThumbnail(false);
            $this->deleteReply($this->id);

            if(defined('KBOARD_COMMNETS_VERSION')){
                $comment_list = new BoardCommentListManager($this->id);
                $comment_list->rpp(1000);
                $comment_list->initFirstList();

                while($comment_list->hasNextList()){
                    while($comment = $comment_list->hasNext()){
                        $comment->delete(false);
                    }
                    $comment_list->initFirstList();
                }
            }

            // 미디어 파일을 삭제한다.
            $media = new BoardContentMediaManager();
            $media->deleteWithContentID($this->id);

            // 게시글 정보 삭제
            BoardContent::find($this->id)->delete();

            // 추천 정보 삭제
            BoardVote::where('target_id', $this->id)->where('target_type', 'document')->delete();

            if($delete_action){
                // 게시글 삭제 액션 실행
                \Event::fire('placecompany.board.board_document_delete', [$this->id, $this->board_id, $this, $board]);
            }
        }
    }

    /**
     * 썸네일 파일을 삭제한다.
     * @param boolean $update
     */
    public function removeThumbnail($update=true){
        if($this->id && $this->thumbnail_file){
            Helpers::board_delete_resize($this->abspath . $this->thumbnail_file);
            @unlink($this->abspath . $this->thumbnail_file);

            if($update){
                BoardContent::find($this->id)->update(['thumbnail_file' => '', 'thumbnail_name' => '']);
            }
        }
    }

    /**
     * 답글을 삭제한다.
     * @param int $parent_id
     */
    public function deleteReply($parent_id){
        $parent_id = intval($parent_id);
        $results = BoardContent::where('parent_id', $parent_id)->get();
        foreach($results as $row){
            $content = new BoardContentManager();
            $content->initWithRow($row);
            $content->remove(false);
        }
    }

    /**
     * 휴지통으로 이동할 때 실행한다.
     * @param string $content_id
     */
    public function moveReplyToTrash($parent_id){
        $board = $this->getBoard();
        $board->meta->list_total = $board->getListTotal() - 1;

        $results = BoardContent::where('parent_id', $parent_id)->get();
        foreach($results as $row){
            if($row->status != 'trash'){
                $this->moveReplyToTrash($row->id);
            }
        }
    }

    /**
     * 휴지통에서 복구할 때 실행한다.
     * @param string $content_id
     */
    public function restoreReplyFromTrash($parent_id){
        $board = $this->getBoard();
        $board->meta->list_total = $board->getListTotal() + 1;

        $results = BoardContent::where('parent_id', $parent_id)->get();
        foreach($results as $row){
            if($row->status != 'trash'){
                $this->restoreReplyFromTrash($row->id);
            }
        }
    }

    /**
     * 게시글의 댓글 개수를 반환한다.
     * @param string $prefix
     * @param string $endfix
     * @param string $default
     * @return string
     */
    public function getCommentsCount($prefix='(', $endfix=')', $default=null){
        if($this->id){
            if($this->comment || $default !== null){
                $count = $this->comment?:$default;
                return "{$prefix}{$count}{$endfix}";
            }
        }
        return '';
    }

    /**
     * 게시글의 댓글 개수를 반환한다.
     * @param string $prefix
     * @param string $endfix
     * @return string
     */
    public function getCommentsCountOld($prefix='(', $endfix=')'){
        if($this->id && defined('KBOARD_COMMNETS_VERSION')){
            $commentList = new BoardCommentListManager($this->id);
            $commentsCount = $commentList->getCount();
            if($commentsCount) return "{$prefix}{$commentsCount}{$endfix}";
        }
        return '';
    }

    /**
     * 게시글의 답글 개수를 반환하나.
     * @param string $format
     * @return string
     */
    public function getReplyCount($format='(%s)'){
        if($this->id){
            $count = BoardContent::where('parent_id', $this->id)->count();
            if($count){
                return sprintf($format, $count);
            }
        }
        return '';
    }

    /**
     * 다음 게시물의 ID를 반환한다.
     */
    public function getNextID(){
        if($this->id){
            $category1 = Helpers::board_category1();
            $category2 = Helpers::board_category2();

            $where[] = "`board_id`='{$this->board_id}'";
            $where[] = "`id`>'{$this->id}'";

            // 휴지통에 없는 게시글만 불러온다.
            $where[] = "(`status`='' OR `status` IS NULL OR `status`='pending_approval')";

            if($category1){
                $category1 = e($category1);
                $where[] = "`category1`='{$category1}'";
            }
            if($category2){
                $category2 = e($category2);
                $where[] = "`category2`='{$category2}'";
            }

            $where = implode(' AND ', $where);
            $row = \DB::table('placecompany_board_content')->select('id')->whereRaw($where)->orderBy('id', 'asc')->first();

            return $row ? intval($row->id) : null;
        }
        return 0;
    }

    /**
     * 이전 게시물의 ID를 반환한다.
     */
    public function getPrevID(){
        if($this->id){
            $category1 = Helpers::board_category1();
            $category2 = Helpers::board_category2();

            $where[] = "`board_id`='{$this->board_id}'";
            $where[] = "`id`<'{$this->id}'";

            // 휴지통에 없는 게시글만 불러온다.
            $where[] = "(`status`='' OR `status` IS NULL OR `status`='pending_approval')";

            if($category1){
                $category1 = e($category1);
                $where[] = "`category1`='{$category1}'";
            }
            if($category2){
                $category2 = e($category2);
                $where[] = "`category2`='{$category2}'";
            }

            $where = implode(' AND ', $where);
            $row = \DB::table('placecompany_board_content')->select('id')->whereRaw($where)->orderBy('id', 'asc')->first();

            return $row ? intval($row->id) : null;
        }
        return 0;
    }

    /**
     * 최상위 부모 ID를 반환한다.
     * @return int
     */
    public function getTopContentID(){
        if($this->parent_id){
            $content = new BoardContentManager();
            $content->initWithID($this->parent_id);
            return $content->getTopContentID();
        }
        return $this->id;
    }

    /**
     * 최상위 부모 object를 반환한다.
     * @return BoardContentManager
     */
    public function getTopContent(){
        if($this->parent_id){
            $content = new BoardContentManager();
            $content->initWithID($this->parent_id);
            return $content->getTopContent();
        }
        return $this;
    }

    /**
     * 게시글과 미디어의 관계를 입력한다.
     */
    public function addMediaRelationships(){
        if($this->id){
            $media = new BoardContentMediaManager();
            $media->board_id = $this->board_id;
            $media->content_id = $this->id;
            $media->media_group = post('media_group') ? Helpers::sanitize_key(post('media_group')) : '';
            $media->createRelationships();
        }
    }

    /**
     * 게시글에 등록된 미디어 목록을 반환한다.
     * @return array
     */
    public function getMediaList(){
        $media_list = array();
        if($this->id){
            $media = new BoardContentMediaManager();
            $media->board_id = $this->board_id;
            $media->content_id = $this->id;
            $media_list = $media->getList();
        }
        return $media_list;
    }

    /**
     * 게시글에서 댓글을 보여줄지 확인한다.
     */
    public function visibleComments(){
        $visible = false;

        $board = $this->getBoard();
        $visible = $board->isComment();

        if($this->notice && $board->meta->notice_invisible_comments){
            $visible = false;
        }
        \Event::fire('placecompany.board.board_visible_comments', [&$visible, $this]);
        return $visible;
    }

    /**
     * 새글인지 확인한다.
     * @return boolean
     */
    public function isNew(){
        $is_new = false;
        if($this->id){
            $notify_time = Helpers::board_new_document_notify_time();
            if((Carbon::now()->timestamp-strtotime($this->date)) <= $notify_time && $notify_time != '1'){
                $is_new = true;
            }
        }
        \Event::fire('placecompany.board.board_content_is_new', [&$is_new, $this]);
        return $is_new;
    }

    /**
     * 게시판 정보를 반환한다.
     * @return BoardManager
     */
    public function getBoard(){
        if(isset($this->board->id) && $this->board->id){
            return $this->board;
        }
        else if($this->board_id){
            $this->board = new BoardManager($this->board_id);
            return $this->board;
        }
        return new BoardManager();
    }

    /**
     * 첨부파일이 있는지 확인한다.
     * @return boolean
     */
    public function isAttached(){
        $is_attached = false;
        if($this->id){
            if(count((array)$this->getAttachmentList()) > 0){
                $is_attached = true;
            }
        }
        \Event::fire('placecompany.board.board_content_is_attached', [&$is_attached, $this, $this->getBoard()]);
        return $is_attached;
    }

    /**
     * 날짜를 반환한다.
     * @return string
     */
    public function getDate(){
        $date = '';
        if(isset($this->row->created_at)){

            if(date('Ymd', Carbon::now()->timestamp) == date('Ymd', strtotime($this->row->created_at))){
                $date = date('H:i', strtotime($this->row->created_at));
            }
            else{
                $date = date('Y.m.d', strtotime($this->row->created_at));
            }
        }
        \Event::fire('placecompany.board.board_content_date', [&$date, $this, $this->getBoard()]);
        return $date;
    }

    /**
     * 제목을 반환한다.
     * @return string
     */
    public function getTitle(){
        if(isset($this->row->title)){
            return $this->row->title;
        }
        return '';
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
     * 게시글 정보를 세션에 저장한다.
     */
    public function saveTemporary(){
        $this->parent_id = post('parent_id') ? intval(post('parent_id')) : 0;
        $this->user_id = post('user_id') ? intval(post('user_id')) : 0;
        $this->user_display = post('user_display') ? e(post('user_display')) :'';
        $this->title = post('title') ? SecurityHelpers::board_safeiframe(SecurityHelpers::board_xssfilter(post('title'))):'';
        $this->content = post('board_content') ? SecurityHelpers::board_safeiframe(SecurityHelpers::board_xssfilter(post('board_content'))):'';
        $this->date = post('date') ? Helpers::sanitize_key(post('date')):'';
        if(post('view')) $this->view = intval(post('view'));
        if(post('comment')) $this->comment = intval(post('comment'));
        if(post('like')) $this->like = intval(post('like'));
        if(post('unlike')) $this->unlike = intval(post('unlike'));
        if(post('vote')) $this->view = intval(post('vote'));
        $this->category1 = post('category1') ? e(post('category1')) : '';
        $this->category2 = post('category2') ? e(post('category2')) : '';
        $this->secret = post('secret') ? Helpers::sanitize_key(post('secret')) : '';
        $this->notice = post('notice') ? Helpers::sanitize_key(post('notice')) : '';
        $this->search = post('wordpress_search')?intval(($this->secret && post('wordpress_search')==1) ? '2':post('wordpress_search')) : '1';
        if(post('status')) $this->status = Helpers::sanitize_key(post('status'));

        if(BackEndAuth::check() && !$this->user_display){
            $current_user = BackEndAuth::getUser();
            $this->user_display = $current_user->first_name;
        }

        $option = new stdClass();
        foreach($_POST as $key=>$value){
            if(strpos($key, BoardContentManager::$SKIN_OPTION_PREFIX) !== false){
                $key = Helpers::sanitize_key(str_replace(BoardContentManager::$SKIN_OPTION_PREFIX, '', $key));
                $value = SecurityHelpers::board_safeiframe(e($value));
                $option->{$key} = $value;
            }
        }

        $temporary = $this->row;
        $temporary->option = $option;
        \Session::put('board_temporary_content', $temporary);

        $this->setExecuteAction();
    }

    /**
     * 세션에 저장된 게시글 정보로 초기화 한다.
     */
    public function initWithTemporary(){
        if(session('board_temporary_content')){
            $temporary = session('board_temporary_content');

            // 민감한 정보 제거
            if($temporary->id){
                $temporary->id = '';
            }
            if($temporary->password){
                $temporary->password = '';
            }
            session('board_temporary_content', $temporary);
            $this->row = $temporary;
        }
        else{
            $this->row = new stdClass();
        }
        if(!isset($temporary->option) || !(array)$temporary->option){
            $this->option = new BoardContentOption();
        }
        else{
            $this->option = $temporary->option;
        }
    }

    /**
     * 세션에 저장된 게시글 정보를 비운다.
     */
    public function cleanTemporary(){
        session()->forget('board_temporary_content');
    }

    /**
     * 글 읽기 권한이 있는 사용자인지 확인한다.
     * @return boolean
     */
    public function isReader(){
        if($this->id){
            $board = $this->getBoard();
            if($board->isReader($this->user_id, $this->secret)){
                return true;
            }
        }
        return false;
    }

    /**
     * 글 수정 권한이 있는 사용자인지 확인한다.
     * @return boolean
     */
    public function isEditor(){
        if($this->id){
            $board = $this->getBoard();
            if($board->isEditor($this->user_id)){
                return true;
            }
        }
        return false;
    }

    /**
     * 게시글 비밀번호와 일치하는지 확인한다.
     * @param boolean $reauth
     * @return boolean
     */
    public function isConfirm($reauth=false){
        if($this->id){
            $board = $this->getBoard();
            if($board->isConfirm($this->password, $this->id, $reauth)){
                return true;
            }
        }
        return false;
    }

    /**
     * 첨부파일 다운로드 권한이 있는 사용자인지 확인한다.
     * @return boolean
     */
    public function isAttachmentDownload(){
        if($this->id){
            $board = $this->getBoard();
            if($board->isAttachmentDownload()){
                return true;
            }
        }
        return false;
    }

    /**
     * 휴지통에 있는지 확인한다.
     * @return boolean
     */
    public function isTrash(){
        if($this->status == 'trash'){
            return true;
        }
        return false;
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
        global $board_builder;

        if($this->id){
            if(!$user_display){
                $user_display = $this->getUserName();
            }

            $user_id = $this->getUserID();
            $user_name = $this->getUserName();
            $type = 'board';
            $builder = $board_builder;

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
            \Event::fire('placecompany.board.board_obfuscate_name', [&$obfuscate_name, $this->user_display, $this, $this->getBoard()]);
            return $obfuscate_name;
        }
        \Event::fire('placecompany.board.board_obfuscate_name', [&$this, $this->getBoard()]);
        return $this;
    }

    /**
     * 게시글에 저장된 카테고리의 값을 반환한다.
     * @param string $format
     * @return array
     */
    public function getCategoryValues($format='%s'){
        $values = array();
        if($this->id){
            if($this->category1){
                $values[] = sprintf($format, $this->category1);
            }
            if($this->category2){
                $values[] = sprintf($format, $this->category2);
            }
        }
        return $values;
    }

    /**
     * 게시글에 저장된 트리 카테고리의 깊이를 반환한다.
     * @return int
     */
    public function getTreeCategoryDepth(){
        $this->tree_category_depth = 0;
        if($this->tree_category_depth){
            return $this->tree_category_depth;
        }
        if($this->id){
            $tree_category_count = $this->getBoard()->tree_category->getCount();
            for($i=1; $i<=$tree_category_count; $i++){
                if(!$this->option->{'tree_category_'.$i}) break;
                $this->tree_category_depth++;
            }
        }
        return $this->tree_category_depth;
    }

    /**
     * 게시글에 저장된 트리 카테고리의 값을 반환한다.
     * @param string $format
     * @return array
     */
    public function getTreeCategoryValues($format='%s'){
        $values = array();
        if($this->id){
            $depth = $this->getTreeCategoryDepth();
            for($i=1; $i<=$depth; $i++){
                $values['tree_category_'.$i] = sprintf($format, $this->option->{'tree_category_'.$i});
            }
        }
        return $values;
    }

    /**
     * 게시글 본문 페이지에 표시할 옵션값 태그를 반환한다.
     * @return string
     */
    public function getDocumentOptionsHTML(){
        if($this->id){
            $board = $this->getBoard();
            return $board->fields()->getDocumentValuesHTML($this);
        }
        return '';
    }

    /**
     * 게시글 본문 페이지에 표시할 옵션값을 반환한다.
     * @return array
     */
    public function getDocumentOptions(){
        if($this->id){
            $board = $this->getBoard();
            return $board->fields()->getDocumentValues($this);
        }
        return array();
    }

    /**
     * 게시글에 표시할 첨부파일을 반환한다.
     * @return object
     */
    public function getAttachmentList(){
        $attachment_list = new stdClass();
        if($this->id){
            $board = $this->getBoard();
            $attachment_list = $board->fields()->getAttachmentList($this);
        }
        \Event::fire('placecompany.board.board_content_get_attachment_list', [&$attachment_list, $this, $this->getBoard()]);
        return $attachment_list;
    }

    /**
     * 메일에 첨부할 첨부파일을 반환한다.
     * @return array
     */
    public function getMailAttachments(){
        $attachments = array();

        if(count((array)$this->attach) > 0){
            $board = $this->getBoard();
            $max_size = $board->meta->latest_alerts_attachments_size;

            if(!$max_size){
                return $attachments;
            }

            $board_mail_attached_dir = storage_path('placecompany/board/board_mail_attached');
            if(!is_dir($board_mail_attached_dir)){
                File::makeDirectory($board_mail_attached_dir, 0755, true, true);
            }

            $sum_size = 0;
            foreach($this->attach as $key=>$attach){
                $sum_size += $attach['file_size'] / (1024 * 1024); // MB

                // 설정된 최대 용량만큼 전송하고 나머지 파일은 제외한다.
                if($sum_size > $max_size) break;

                $source = $this->abspath . $attach[0];
                $dest = $board_mail_attached_dir . $attach[1];
                copy($source, $dest);
                $attachments[] = $dest;
            }
        }

        return $attachments;
    }

    /**
     * 메일에 첨부한 첨부파일을 삭제한다.
     */
    public function deleteMailAttachments(){
        $board_mail_attached_dir = storage_path('board/board_mail_attached');
        if(is_dir($board_mail_attached_dir)){
            $files = Storage::files($board_mail_attached_dir);
            foreach($files as  $attach){
                Storage::delete($attach);
            }

            rmdir($board_mail_attached_dir);
        }
    }

    /**
     * 게시글 본문에 이미지가 포함되어 있는지 확인한다.
     * @return boolean
     */
    public function hasImage(){
        $has_image = false;
        if($this->id && strpos($this->content, '<img') !== false){
            $has_image = true;
        }
        \Event::fire('placecompany.board.board_content_has_image', [&$has_image, $this, $this->getBoard()]);
        return $has_image;
    }

    /**
     * 정보를 배열로 반환한다.
     * @return array
     */
    public function toArray(){
        if($this->id){
            return get_object_vars($this->row);
        }
        return array();
    }

    /**
     * 옵션 데이터를 포함해서 정보를 배열로 반환한다.
     */
    public function toArrayWithOptions(){
        if($this->id){
            $object = $this->row;
            foreach($this->option->row as $key=>$value){
                $object->{BoardContentManager::$SKIN_OPTION_PREFIX . $key} = $value;
            }
            return get_object_vars($object);
        }
        return array();
    }

    /**
     * 제목 문자열에서 HTML과 PHP 태그를 제거한다.
     * @param string $title
     * @return string
     */
    public function titleStripTags($title){
        \Event::fire('placecompany.board.board_content_title_allowable_tags', ['<i><b><u><s><br><span><strong><img><ins><del>', $this, $this->getBoard()]);
        $title = strip_tags($title, '<i><b><u><s><br><span><strong><img><ins><del>');
        return $title;
    }

    /**
     * 이모지를 해당하는 HTML 엔터티로 변환한다.
     * @param string $string
     * @return string
     */
    public function encodeEmoji($string){
//        if($string && $wpdb->charset != 'utf8mb4'){
//            if(function_exists('wp_encode_emoji') && function_exists('mb_convert_encoding')){
//                $string = wp_encode_emoji($string);
//            }
//        }
        return $string;
    }

    /**
     * 게시판을 이동하면 게시판의 정보를 변경한다.
     * @param int $new_board_id
     */
    private function changeBoardID($new_board_id){
        if($this->id){
            $current_board = new BoardManager($this->previous_board_id);
            $new_board = new BoardManager($new_board_id);

            if($new_board->id && $current_board->id != $new_board->id){
                $current_board->meta->total = $current_board->getTotal() - 1;
                if($this->status != 'trash'){
                    $current_board->meta->list_total = $current_board->getListTotal() - 1;
                }

                $new_board->meta->total = $new_board->getTotal() + 1;
                if($this->status != 'trash'){
                    $new_board->meta->list_total = $new_board->getListTotal() + 1;
                }
            }
        }
    }

    /**
     * 본문에 인터넷 주소가 있을때 자동으로 링크를 생성한다.
     */
    public static function autolink($contents){
        // http://yongji.tistory.com/28
        $pattern = "/(http|https|ftp|mms):\/\/[0-9a-z-]+(\.[_0-9a-z-]+)+(:[0-9]{2,4})?\/?"; //domain+port
        $pattern .= "([\.~_0-9a-z-]+\/?)*";// sub roots
        $pattern .= "(\S+\.[_0-9a-z]+)?";// file & extension string
        $pattern .= "(\?[_0-9a-z#%&=\-\+]+)*/i";// parameters
        $replacement = "<a href=\"\\0\" target=\"window.opne(this.href); return false;\">\\0</a>";
        return preg_replace($pattern, $replacement, $contents, -1);
    }
}
