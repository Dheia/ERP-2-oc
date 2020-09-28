<?php
namespace Placecompany\Board\Controllers;

use Backend\Facades\BackendAuth;
use Cms\Classes\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use October\Rain\Support\Facades\Flash;
use Placecompany\Board\Classes\BoardContentManager;
use Placecompany\Board\Classes\BoardContentMediaManager;
use Placecompany\Board\Classes\BoardUrlManager;
use Placecompany\Board\Classes\BoardVoteManager;
use Placecompany\Board\Classes\Helpers;
use Placecompany\Board\Models\Settings;

class BoardController extends Controller
{
    /**
     * 게시글 등록 및 수정
     */
    public function editorExecute(Request $request){
        $args = request();

        $id = isset($args['id'])?intval($args['id']):0;
        $board_id = isset($args['board_id'])?intval($args['board_id']):0;

        $content = new BoardContentManager();
        $content->initWithID($id);
        $content->setBoardID($board_id);
        $content->saveTemporary();
        $board = $content->getBoard();

        if(!$content->id && !$board->isWriter()){
            Flash::error(Lang::get('placecompany.board::lang.You do not have permission.'));
            return redirect()->back();
        }
        else if($content->id && !$content->isEditor()){
            if($board->permission_write=='all' && !$content->user_id){
                if(!$content->isConfirm()){
                    Flash::error(Lang::get('placecompany.board::lang.You do not have permission.'));
                    return redirect()->back();
                }
            }
            else{
                Flash::error(Lang::get('placecompany.board::lang.You do not have permission.'));
                return redirect()->back();
            }
        }

        $content->new_password = isset($args['password'])?e($args['password']):$content->password;

        if(!$board->id){
            Flash::error(Lang::get('placecompany.board::lang.You do not have permission.'));
            return redirect()->back();
        }
        else if(!$content->title){
            Flash::error(Lang::get('placecompany.board::lang.Please enter the title'));
            return redirect()->back();
        }
        else if(!BackEndAuth::check() && !$content->new_password){
            Flash::error(Lang::get('placecompany.board::lang.Please enter the password'));
            return redirect()->back();
        }

        // 금지단어 체크
        if(!$board->isAdmin()){
            $replace = array(' ', '「', '」', '『', '』', '-', '_', '.', '(', ')', '［', '］', ',', '~', '＊', '+', '^', '♥', '★', '!', '#', '=', '­', '[', ']', '/', '▶', '▷', '<', '>', '%', ':', 'ღ', '$', '*', '♣', '♧', '☞');

            // 작성자 금지단어 체크
            $name_filter = Helpers::board_name_filter(true);
            if($name_filter){
                $subject = urldecode($content->user_display);
                $subject = strtolower($subject);
                $subject = str_replace($replace, '', $subject);

                $name_filter_message = Settings::get('board_name_filter_message', '');

                foreach($name_filter as $filter){
                    if($filter && strpos($subject, $filter) !== false){
                        if(!$name_filter_message){
                            $name_filter_message = Lang::get(':title is not available.', ['title' => $filter]);
                        }
                        \Event::fire('placecompany.board.board_name_filter_message', [&$name_filter_message, $filter, $subject, $board]);
                        Flash::error($name_filter_message);
                        return redirect()->back();
                    }
                }
            }

            // 본문/제목/댓글 금지단어 체크
            $content_filter = Helpers::board_content_filter(true);
            if($content_filter){
                $subject = urldecode($content->content);
                $subject = strtolower($subject);
                $subject = str_replace($replace, '', $subject);

                $content_filter_message = Settings::get('board_content_filter_message', '');

                foreach($content_filter as $filter){
                    if($filter && strpos($subject, $filter) !== false){
                        if(!$content_filter_message){
                            $content_filter_message = Lang::get(':title is not available.', ['title' => $filter]);
                        }
                        \Event::fire('placecompany.board.board_content_filter_message', [&$content_filter_message, $filter, $subject, $board]);
                        Flash::error($content_filter_message);
                        return redirect()->back();
                    }
                }

                $subject = urldecode($content->title);
                $subject = strtolower($subject);
                $subject = str_replace($replace, '', $subject);

                $content_filter_message = Settings::get('board_content_filter_message', '');

                foreach($content_filter as $filter){
                    if($filter && strpos($subject, $filter) !== false){
                        if(!$content_filter_message){
                            $content_filter_message = Lang::get('placecompany.board::lang.:title is not available', ['title' => $filter]);
                        }
                        \Event::fire('placecompany.board.board_content_filter_message', [&$content_filter_message, $filter, $subject, $board]);
                        Flash::error($content_filter_message);
                        return redirect()->back();
                    }
                }
            }
        }

        \Event::fire('placecompany.board.board_pre_content_execute', [$content, $board]);

        // 글쓰기 감소 포인트
        if($content->execute_action == 'insert' && $board->meta->document_insert_down_point){
            // @todo 포인트 구현 필요
        }

        // 실행
        $execute_id = $content->execute();

        if(!$execute_id){
            Flash::error(Lang::get('placecompany.board::lang.An unexpected problem has occurred.'));
            return redirect()->back();
        }

        \Event::fire('placecompany.board.board_content_execute', [$content, $board]);

        // 글쓰기 증가 포인트
        if($content->execute_action == 'insert' && $board->meta->document_insert_up_point){
            // @todo 포인트 구현 필요
        }

        // 비밀번호가 입력되면 즉시 인증과정을 거친다.
        if($content->password) $board->isConfirm($content->password, $execute_id);

        $url = new BoardUrlManager();

        if($content->execute_action == 'insert'){
            if(!$board->meta->after_executing_mod){
                $next_page_url = $url->set('execute_id', $execute_id)->set('id', $execute_id)->set('mod', 'document')->toString();
            }
            else{
                $next_page_url = $url->set('execute_id', $execute_id)->set('mod', $board->meta->after_executing_mod)->toString();
            }
        }
        else{
            $next_page_url = $url->set('id', $execute_id)->set('mod', 'document')->toString();
        }

        \Event::fire('placecompany.board.board_after_executing_url', [&$next_page_url, $execute_id, $board_id]);

        \Event::fire('placecompany.board.board_content_execute_pre_redirect', [$next_page_url, $content, $board]);

        if($content->execute_action == 'insert'){
            if($board->meta->conversion_tracking_code){
                echo $board->meta->conversion_tracking_code;
                echo "<script>window.location.href='{$next_page_url}';</script>";
                exit;
            }
        }

        return redirect($next_page_url);
    }

    /**
     * 미디어 파일 업로드
     */
    public function mediaUpload(){
        $media = new BoardContentMediaManager();
        $media->board_id = intval(request('board_id')?:'');
        $media->media_group = request('board_id') ? Helpers::board_htmlclear(request('media_group')): '';
        $media->content_id = intval(request('content_id')?:'');
        $media->upload();

        return redirect()->back();
    }

    /**
     * 미디어 파일 삭제
     */
    public function mediaDelete(){
        $media_id = intval(request('media_id')?:'');
        $media = new BoardContentMediaManager();
        $media->deleteWithMediaID($media_id);

        return redirect()->back();
    }

    /**
     * 첨부파일 삭제
     */
    public function fileDelete(){
        header('Content-Type: text/html; charset=UTF-8');

        $id = request('id')?intval(request('id')):'';
        $file = request('file')?Helpers::sanitize_key(request('file')):'';

        $content = new BoardContentManager();
        $content->initWithID($id);
        $board = $content->getBoard();

        if(!$content->id || !$file){
            wp_die(__('You do not have permission.', 'kboard'));
        }

        if(!$content->isEditor()){
            if($board->permission_write=='all' && !$content->user_id){
                if(!$content->isConfirm()){
                    wp_die(__('You do not have permission.', 'kboard'));
                }
            }
            else{
                wp_die(__('You do not have permission.', 'kboard'));
            }
        }

        if($file == 'thumbnail'){
            $content->removeThumbnail();
        }
        else{
            $content->removeAttached($file);
        }

        wp_redirect(wp_get_referer());
        exit;
    }

    /**
     * 첨부파일 다운로드
     */
    public function fileDownload(){
        global $wpdb;

        header('X-Robots-Tag: noindex, nofollow'); // 검색엔진 수집 금지
        header('Content-Type: text/html; charset=UTF-8');

        $id = isset(request['id'])?intval(request['id']):'';
        $comment_id = isset(request['comment_id'])?intval(request['comment_id']):'';
        $file = isset(request['file'])?sanitize_key(request['file']):'';

        $content = new KBContent();
        $comment = new KBComment();

        if($comment_id){
            $comment->initWithID($comment_id);
            $board = $content->getBoard();

            if(!$comment->id){
                do_action('kboard_cannot_download_file', 'go_back', wp_get_referer(), $content, $board, $comment);
                exit;
            }

            $id = $comment->content_id;
        }

        $content->initWithID($id);
        $board = $content->getBoard();

        if(!isset(request['kboard-file-download-nonce']) || !wp_verify_nonce(request['kboard-file-download-nonce'], 'kboard-file-download')){
            if(!wp_get_referer()){
                wp_die(__('This page is restricted from external access.', 'kboard'));
            }
        }

        if(!$file){
            do_action('kboard_cannot_download_file', 'go_back', wp_get_referer(), $content, $board, $comment);
            exit;
        }

        if(!$content->id){
            do_action('kboard_cannot_download_file', 'go_back', wp_get_referer(), $content, $board, $comment);
            exit;
        }

        if(!$content->isReader()){
            if($board->permission_read != 'all' && !is_user_logged_in()){
                do_action('kboard_cannot_download_file', 'go_login', wp_login_url(wp_get_referer()), $content, $board, $comment);
                exit;
            }
            else if($content->secret){
                if(!$content->isConfirm()){
                    if($content->parent_id){
                        $parent = new KBContent();
                        $parent->initWithID($content->getTopContentID());
                        if(!$board->isReader($parent->user_id, $content->secret) && !$parent->isConfirm()){
                            do_action('kboard_cannot_download_file', 'go_back', wp_get_referer(), $content, $board, $comment);
                            exit;
                        }
                    }
                    else{
                        do_action('kboard_cannot_download_file', 'go_back', wp_get_referer(), $content, $board, $comment);
                        exit;
                    }
                }
            }
            else{
                do_action('kboard_cannot_download_file', 'go_back', wp_get_referer(), $content, $board, $comment);
                exit;
            }
        }

        if(!$content->isAttachmentDownload()){
            if($board->meta->permission_attachment_download == '1' && !is_user_logged_in()){
                do_action('kboard_cannot_download_file', 'go_login', wp_login_url(wp_get_referer()), $content, $board, $comment);
                exit;
            }
            else{
                do_action('kboard_cannot_download_file', 'go_back', wp_get_referer(), $content, $board, $comment);
                exit;
            }
        }

        $file = esc_sql($file);

        if($comment->id){
            $file_info = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_board_attached` WHERE `comment_id`='{$comment->id}' AND `file_key`='{$file}'");
        }
        else{
            $file_info = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_board_attached` WHERE `content_id`='{$content->id}' AND `file_key`='{$file}'");
        }

        $file_info = apply_filters('kboard_pre_download_file', $file_info, $content->id, $board->id, $comment->id);

        do_action('kboard_pre_file_download', $file_info, $content, $board, $comment);
        do_action("kboard_{$board->skin}_pre_file_download", $file_info, $content, $board, $comment);

        $ds = DIRECTORY_SEPARATOR;

        $content_dir_name = basename(WP_CONTENT_DIR);
        list($path) = explode("{$ds}{$content_dir_name}", dirname(__FILE__));
        $file_info->full_path = $path . str_replace('/', $ds, $file_info->file_path);

        if(!$file_info->file_path || !file_exists($file_info->full_path)){
            echo '<script>alert("'.__('File does not exist.', 'kboard').'");</script>';
            echo '<script>window.location.href="' . wp_get_referer() . '";</script>';
            exit;
        }

        $file_info->file_name = str_replace(' ' ,'-', $file_info->file_name);
        $file_info->mime_type = kboard_mime_type($file_info->full_path);
        $file_info->size = sprintf('%d', filesize($file_info->full_path));

        $file_info = apply_filters('kboard_download_file', $file_info, $content->id, $board->id, $comment->id);

        if(!$file_info->file_path || !file_exists($file_info->full_path)){
            echo '<script>alert("'.__('File does not exist.', 'kboard').'");</script>';
            echo '<script>window.location.href="' . wp_get_referer() . '";</script>';
            exit;
        }

        do_action('kboard_file_download', $file_info, $content, $board, $comment);
        do_action("kboard_{$board->skin}_file_download", $file_info, $content, $board, $comment);

        // 첨부파일 다운로드 감소 포인트
        if($board->meta->attachment_download_down_point){
            if(function_exists('mycred_add')){
                if(!is_user_logged_in()){
                    do_action('kboard_cannot_download_file', 'go_back', wp_get_referer(), $content, $board, $comment);
                    exit;
                }
                else if($content->user_id != get_current_user_id()){
                    $log_args['user_id'] = get_current_user_id();
                    $log_args['ref'] = 'attachment_download_down_point';
                    $log_args['ref_id'] = $content->id;
                    $log = new myCRED_Query_Log($log_args);

                    if(!$log->have_entries()){
                        $balance = mycred_get_users_balance(get_current_user_id());
                        if($board->meta->attachment_download_down_point > $balance){
                            do_action('kboard_cannot_download_file', 'not_enough_points', wp_get_referer(), $content, $board, $comment);
                            exit;
                        }
                        else{
                            $point = intval(get_user_meta(get_current_user_id(), 'kboard_document_mycred_point', true));
                            update_user_meta(get_current_user_id(), 'kboard_document_mycred_point', $point + ($board->meta->attachment_download_down_point*-1));

                            mycred_add('attachment_download_down_point', get_current_user_id(), ($board->meta->attachment_download_down_point*-1), __('Attachment download decrease points', 'kboard'), $content->id);
                        }
                    }
                }
            }
        }

        // download_count 증가
        $wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_attached` SET `download_count`=`download_count`+1 WHERE `id`='{$file_info->id}'");

        if(get_option('kboard_attached_copy_download')){
            $unique_dir = uniqid();
            $upload_dir = wp_upload_dir();
            $temp_path = $upload_dir['basedir'] . "{$ds}kboard_temp";

            $file_handler = new KBFileHandler();
            $file_handler->deleteWithOvertime($temp_path, 60);
            $file_handler->mkPath("{$temp_path}{$ds}{$unique_dir}");

            copy($file_info->full_path, "{$temp_path}{$ds}{$unique_dir}{$ds}{$file_info->file_name}");
            header('Location: ' . $upload_dir['baseurl'] . "{$ds}kboard_temp{$ds}{$unique_dir}{$ds}{$file_info->file_name}");
        }
        else{
            $ie = isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false);
            if($ie){
                $file_info->file_name = iconv('UTF-8', 'EUC-KR//IGNORE', $file_info->file_name);

                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');
            }
            else{
                header('Pragma: no-cache');
            }

            header('Content-type: ' . $file_info->mime_type);
            header('Content-Disposition: attachment; filename="' . $file_info->file_name . '"');
            header('Content-Transfer-Encoding: binary');
            header('Content-length: ' . $file_info->size);
            header('Expires: 0');

            @ob_clean();
            @flush();

            readfile($file_info->full_path);
        }
        exit;
    }

    /**
     * 게시글 좋아요
     */
    public function documentLike(){
        if(input('document_id')){
            $content = new BoardContentManager();
            $content->initWithID(input('document_id'));
            if($content->id){
                $board = $content->getBoard();
                if($board->isVote()){
                    $args['target_id'] = $content->id;
                    $args['target_type'] = BoardVoteManager::$TYPE_DOCUMENT;
                    $args['target_vote'] = BoardVoteManager::$VOTE_LIKE;
                    $vote = new BoardVoteManager();
                    if(!$vote->isExists($args)){
                        if($vote->insert($args)){
                            $content->like += 1;
                            $content->vote = $content->like - $content->unlike;
                            $content->updateContent();

                            \Event::fire('placecompany.board.board_content_like', [$content, $board]);

                            return [
                                'result'=>'success',
                                'data'=> [
                                    'vote'=>intval($content->vote),
                                    'like'=>intval($content->vote),
                                    'unlike'=>intval($content->unlike)
                                ]
                            ];
                        }
                    }
                    else{
                        return [
                            'result'=>'error', 'message'=>Lang::get('placecompany.board::lang.You have already voted.')
                        ];
                    }
                }
                else if(!BackendAuth::check()){
                    return [
                        'result'=>'error', 'message'=>Lang::get('placecompany.board::lang.Please Log in to continue.')
                    ];
                }
            }
        }
        return [
            'result'=>'error', 'message'=>Lang::get('placecompany.board::lang.You do not have permission.')
        ];
    }

    /**
     * 게시글 싫어요
     */
    function documentUnlike(){
        if(input('document_id')){
            $content = new BoardContentManager();
            $content->initWithID(input('document_id'));
            if($content->id){
                $board = $content->getBoard();
                if($board->isVote()){
                    $args['target_id'] = $content->id;
                    $args['target_type'] = BoardVoteManager::$TYPE_DOCUMENT;
                    $args['target_vote'] = BoardVoteManager::$VOTE_UNLIKE;
                    $vote = new BoardVoteManager();
                    if($vote->isExists($args) === 0){
                        if($vote->insert($args)){
                            $content->unlike += 1;
                            $content->vote = $content->like - $content->unlike;
                            $content->updateContent();

                            \Event::fire('placecompany.board.board_content_unlike', [$content, $board]);

                            return [
                                'result'=>'success',
                                'data'=> [
                                    'vote'=>intval($content->vote),
                                    'like'=>intval($content->vote),
                                    'unlike'=>intval($content->unlike)
                                ]
                            ];
                        }
                    }
                    else{
                        return [
                            'result'=>'error', 'message'=>Lang::get('placecompany.board::lang.You have already voted.')
                        ];
                    }
                }
                else if(!BackendAuth::check()){
                    return [
                        'result'=>'error', 'message'=>Lang::get('placecompany.board::lang.Please Log in to continue.')
                    ];
                }
            }
        }
        return [
            'result'=>'error', 'message'=>Lang::get('placecompany.board::lang.You do not have permission.')
        ];
    }

    /**
     * 게시글 정보 업데이트
     */
    public function contentUpdate(){
        if(isset($args['content_id']) && intval($args['content_id'])){
            $content = new KBContent();
            $content->initWithID($args['content_id']);
            if($content->isEditor() || $content->isConfirm()){
                $content->updateContent($args['data']);
                $content->updateOptions($args['data']);

                // 게시글 수정 액션 훅 실행
                $content->initWithID($args['content_id']);
                do_action('kboard_document_update', $content->id, $content->board_id, $content, $content->getBoard());

                wp_send_json(array('result'=>'success', 'data'=>$args['data']));
            }
        }
        wp_send_json(array('result'=>'error', 'message'=>__('You do not have permission.', 'kboard')));
    }
}
