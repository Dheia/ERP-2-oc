<?php namespace Placecompany\BoardComment\Components;

use Backend\Facades\BackendAuth;
use Carbon\Carbon;
use Cms\Classes\CodeBase;
use Cms\Classes\ComponentBase;
use Cms\Classes\Controller;
use Exception;
use Illuminate\Support\Facades\Lang;
use October\Rain\Exception\AjaxException;
use October\Rain\Support\Facades\Flash;
use Placecompany\Board\Classes\BoardCaptchaManager;
use Placecompany\Board\Classes\BoardContentManager;
use Placecompany\Board\Classes\BoardFileHandler;
use Placecompany\Board\Classes\BoardManager;
use Placecompany\Board\Classes\Helpers;
use Placecompany\Board\Classes\SecurityHelpers;
use Placecompany\Board\Models\Settings;
use Placecompany\BoardComment\Classes\BoardCommentListManager;
use Placecompany\BoardComment\Classes\BoardCommentManager;
use Placecompany\BoardComment\Classes\BoardCommentMediaManager;
use Placecompany\BoardComment\Classes\BoardCommentOptionManager;
use Placecompany\BoardComment\Classes\BoardCommentsBuilderManager;
use Placecompany\BoardComment\Classes\BoardCommentSkinManager;
use stdClass;

class BoardComment extends ComponentBase
{
    private $abspath;

    // 스킨에서 사용 할 첨부파일 input[type=file] 이름의 prefix를 정의한다.
    var $skin_attach_prefix = 'comment_attach_';

    // 스킨에서 사용 할 사용자 정의 옵션 input, textarea, select 이름의 prefix를 정의한다.
    var $skin_option_prefix = 'comment_option_';

    public function __construct(CodeBase $cmsObject = null, $properties = [])
    {
        $this->abspath = base_path();

        parent::__construct($cmsObject, $properties);
    }

    public function componentDetails()
    {
        return [
            'name'        => 'Board',
            'description' => '게시판 mod 에 맞게 view 를 생성합니다.'
        ];
    }

    public function defineProperties()
    {
        return [
            'id' => [
                'title'             => 'Board ID',
                'description'       => '게시판 아이디',
                'type'              => 'string',
                'default'           => false,
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The Max Items property can contain only numeric symbols'
            ]
        ];
    }

    public function onRun()
    {
        // 활성화된 스킨의 style.css 등록
        $skin = BoardCommentSkinManager::getInstance();
        foreach($skin->getActiveList() as $skin_name){
            $this->addCss($skin->url($skin_name, 'style.css'),'board');
        }

        // 설정 등록
        $script = \JavaScript::put([
            'board_comments_localize_strings' => [
                'reply' => Lang::get('placecompany.boardcomment::lang.Reply'),
                'cancel' => Lang::get('placecompany.boardcomment::lang.Cancel'),
                'please_enter_the_author' => Lang::get('placecompany.boardcomment::lang.Please enter the author.'),
                'please_enter_the_password' => Lang::get('placecompany.boardcomment::lang.Please enter the password.'),
                'please_enter_the_CAPTCHA' => Lang::get('placecompany.boardcomment::lang.Please enter the CAPTCHA.'),
                'please_enter_the_content' => Lang::get('placecompany.boardcomment::lang.Please enter the content.'),
                'are_you_sure_you_want_to_delete' => Lang::get('placecompany.boardcomment::lang.Are you sure you want to delete?'),
                'please_wait' => Lang::get('placecompany.boardcomment::lang.Please wait.'),
                'name' => Lang::get('placecompany.boardcomment::lang.Name'),
                'email' => Lang::get('placecompany.boardcomment::lang.Email'),
                'address' => Lang::get('placecompany.boardcomment::lang.Address'),
                'postcode' => Lang::get('placecompany.boardcomment::lang.Postcode'),
                'phone_number' => Lang::get('placecompany.boardcomment::lang.Phone number'),
                'find' => Lang::get('placecompany.boardcomment::lang.Find'),
                'rate' => Lang::get('placecompany.boardcomment::lang.Rate'),
                'ratings' => Lang::get('placecompany.boardcomment::lang.Ratings'),
                'waiting' => Lang::get('placecompany.boardcomment::lang.Waiting'),
                'complete' => Lang::get('placecompany.boardcomment::lang.Complete'),
                'question' => Lang::get('placecompany.boardcomment::lang.Question'),
                'answer' => Lang::get('placecompany.boardcomment::lang.Answer'),
                'notify_me_of_new_comments_via_email' => Lang::get('placecompany.boardcomment::lang.Notify me of new comments via email'),
                'comment' => Lang::get('placecompany.boardcomment::lang.Comment'),
                'comments' => Lang::get('placecompany.boardcomment::lang.Comments'),
            ]
        ]);

        \Event::listen('cms.page.render', function (Controller $controller, $pageContents) use ($script) {
            echo "<script>{$script}</script>";
        });

    }

    public function onRender()
    {
        $comment_builder = new BoardCommentsBuilderManager();
        $comment_builder->board = $this->property('board');
        $comment_builder->board_id = $this->property('board_id');
        $comment_builder->content_id = $this->property('content_id');
        $comment_builder->permission_comment_write = $this->property('permission_comment_write');
        $comment_builder->setSkin($this->property('skin'));
        return $comment_builder->create();
    }

    public function onInsert()
    {
        try {
            $content_id = post('content_id');

            $content = post('content');
            $comment_content = post('comment_content');
            $content = $content ? $content : $comment_content;

            $parent_id = post('parent_id');
            $user_id = post('user_id');
            $user_display = post('user_display');
            $password = post('password');

            if (BackendAuth::check()) {
                $current_user = BackendAuth::getUser();
                $user_id = $current_user->id;
                $user_display = $user_display ? $user_display : $current_user->first_name;
            }

            $option = new stdClass();
            $post = post();
            foreach ($post as $key => $value) {
                if (strpos($key, $this->skin_option_prefix) !== false) {
                    $key = Helpers::sanitize_key(str_replace($this->skin_option_prefix, '', $key));
                    $value = SecurityHelpers::board_safeiframe(SecurityHelpers::board_xssfilter($value));
                    $option->{$key} = $value;
                }
            }

            $document = new BoardContentManager();
            $document->initWithID($content_id);
            $board = new BoardManager($document->board_id);

            // 임시저장
            $temporary = new stdClass();
            $temporary->member_display = $user_display;
            $temporary->content = $content;
            $temporary->option = $option;
            session('board_temporary_comments', $temporary);

            if (!$board->id) {
                throw new AjaxException([
                    'error' => Lang::get('placecompany.boardcomment::lang.You do not have permission.'),
                    'questionsNeeded' => 2
                ]);
            } else if (!$document->id) {
                throw new AjaxException([
                    'error' => Lang::get('placecompany.boardcomment::lang.You do not have permission.'),
                    'questionsNeeded' => 2
                ]);
            } else if (!BackendAuth::check() && $board->meta->permission_comment_write) {
                throw new AjaxException([
                    'error' => Lang::get('placecompany.boardcomment::lang.You do not have permission.'),
                    'questionsNeeded' => 2
                ]);
            } else if (!BackendAuth::check() && !$user_display) {
                throw new AjaxException([
                    'error' => Lang::get('placecompany.boardcomment::lang.Please enter the author.'),
                    'questionsNeeded' => 2
                ]);
            } else if (!BackendAuth::check() && !$password) {
                throw new AjaxException([
                    'error' => Lang::get('placecompany.boardcomment::lang.Please enter the password.'),
                    'questionsNeeded' => 2
                ]);
            } else if (!$content) {
                throw new AjaxException([
                    'error' => Lang::get('placecompany.boardcomment::lang.Please enter the content.'),
                    'questionsNeeded' => 2
                ]);
            } else if (!$content_id) {
                throw new AjaxException([
                    'error' => Lang::get('placecompany.boardcomment::lang.content_id is required.'),
                    'questionsNeeded' => 2
                ]);
            }

            // 금지단어 체크
            if (!$board->isAdmin()) {
                $replace = array(' ', '「', '」', '『', '』', '-', '_', '.', '(', ')', '［', '］', ',', '~', '＊', '+', '^', '♥', '★', '!', '#', '=', '­', '[', ']', '/', '▶', '▷', '<', '>', '%', ':', 'ღ', '$', '*', '♣', '♧', '☞');

                // 작성자 금지단어 체크
                $name_filter = Helpers::board_name_filter(true);
                if ($name_filter) {
                    $subject = urldecode($user_display);
                    $subject = strtolower($subject);
                    $subject = str_replace($replace, '', $subject);

                    $name_filter_message = Settings::get('board_name_filter_message', '');

                    foreach ($name_filter as $filter) {
                        if ($filter && strpos($subject, $filter) !== false) {
                            if (!$name_filter_message) {
                                $name_filter_message = sprintf(__('%s is not available.', 'board'), $filter);
                            }
                            \Event::fire('placecompany.board.board_comments_name_filter_message', [&$name_filter_message, $filter, $subject, $board]);
                            throw new AjaxException([
                                'error' => $name_filter_message,
                                'questionsNeeded' => 2
                            ]);
                        }
                    }
                }

                // 본문/제목/댓글 금지단어 체크
                $content_filter = Helpers::board_content_filter(true);
                if ($content_filter) {
                    $subject = urldecode($content);
                    $subject = strtolower($subject);
                    $subject = str_replace($replace, '', $subject);

                    $content_filter_message = Settings::get('board_content_filter_message', '');

                    foreach ($content_filter as $filter) {
                        if ($filter && strpos($subject, $filter) !== false) {
                            if (!$content_filter_message) {
                                $content_filter_message = sprintf(__('%s is not available.', 'board'), $filter);
                            }
                            \Event::fire('placecompany.board.board_comments_content_filter_message', [&$content_filter_message, $filter, $subject, $board]);
                            throw new AjaxException([
                                'error' => $content_filter_message,
                                'questionsNeeded' => 2
                            ]);
                        }
                    }
                }
            }

            // Captcha 검증
            if ($board->useCAPTCHA()) {
                $captcha = new BoardCaptchaManager();

                if (!$captcha->validate()) {
                    throw new AjaxException([
                        'error' => Lang::get('placecompany.boardcomment::lang.CAPTCHA is invalid.'),
                        'questionsNeeded' => 2
                    ]);
                }
            }

            // 댓글쓰기 감소 포인트
            if ($board->meta->comment_insert_down_point) {
//                if (function_exists('mycred_add')) {
//                    if (!BackendAuth::check()) {
//                        die("<script>alert('" . __('You do not have permission.', 'board-comments') . "');history.go(-1);</script>");
//                    } else {
//                        $balance = mycred_get_users_balance(get_current_user_id());
//                        if ($board->meta->comment_insert_down_point > $balance) {
//                            die('<script>alert("' . __('You have not enough points.', 'board-comments') . '");history.go(-1);</script>');
//                        } else {
//                            $point = intval(get_user_meta(get_current_user_id(), 'board_comments_mycred_point', true));
//                            update_user_meta(get_current_user_id(), 'board_comments_mycred_point', $point + ($board->meta->comment_insert_down_point * -1));
//
//                            mycred_add('comment_insert_down_point', get_current_user_id(), ($board->meta->comment_insert_down_point * -1), __('Writing comment decrease points', 'board-comments'));
//                        }
//                    }
//                }
            }

            \Event::fire('placecompany.board.board_comments_pre_insert', [$content_id, $board]);

            // 업로드된 파일이 있는지 확인한다. (없으면 중단)
            $upload_checker = false;
            foreach ($_FILES as $key => $value) {
                if (strpos($key, $this->skin_attach_prefix) === false) continue;
                if ($_FILES[$key]['tmp_name']) {
                    $upload_checker = true;
                    break;
                }
            }

            $upload_attach_files = array();
            if ($upload_checker) {
                $upload_dir = uploads_path('placecompany');
                $attach_store_path = str_replace($this->abspath, '', $upload_dir) . "/board_attached/{$board->id}/" . date('Ym', Carbon::now()->timestamp) . '/';

                $file = new BoardFileHandler();
                $file->setPath($attach_store_path);

                foreach ($_FILES as $key => $value) {
                    if (strpos($key, $this->skin_attach_prefix) === false) continue;
                    $key = str_replace($this->skin_attach_prefix, '', $key);
                    $key = Helpers::sanitize_key($key);

                    $upload = $file->upload($this->skin_attach_prefix . $key);
                    $file_path = $upload['path'] . $upload['stored_name'];
                    $file_name = $upload['original_name'];
                    $metadata = $upload['metadata'];

                    if ($file_name) {
                        $attach_file = new stdClass();
                        $attach_file->key = $key;
                        $attach_file->path = $file_path;
                        $attach_file->name = $file_name;
                        $attach_file->metadata = $metadata;
                        $upload_attach_files[] = $attach_file;
                    }
                }
            }

            $comment_list = new BoardCommentListManager($content_id);
            $comment_list->board = $board;
            $comment_id = $comment_list->add($parent_id, $user_id, $user_display, $content, $password);

            if ($comment_id && $upload_attach_files && is_array($upload_attach_files)) {
                foreach ($upload_attach_files as $attach_file) {
                    $file_key = e($attach_file->key);
                    $file_path = e($attach_file->path);
                    $file_name = e($attach_file->name);
                    $file_size = intval(filesize($this->abspath . $file_path));

                    \Event::fire('placecompany.board.board_comments_file_metadata', [&$attach_file->metadata, $attach_file, $this]);
                    $metadata = $attach_file->metadata;
                    $metadata = serialize($metadata);
                    $metadata = e($metadata);

                    $present_file = \DB::table('placecompany_board_attached')->select('file_path')->where('comment_id', $comment_id)->where('file_key', $file_key)->get();
                    if ($present_file) {
                        @unlink($this->abspath . $present_file);
                        $date = date('YmdHis', Carbon::now()->timestamp);
                        \DB::table('placecompany_board_attached')->where('comment_id', $comment_id)->where('file_key', $file_key)->update([
                            'file_path' => $file_path,
                            'file_name' => $file_name,
                            'file_size' => $file_size,
                            'metadata' => $metadata,
                            'updated_at' => $date
                        ]);
                    } else {
                        $date = date('YmdHis', Carbon::now()->timestamp);
                        \DB::table('placecompany_board_attached')->where('comment_id', $comment_id)->where('file_key', $file_key)->insert([
                            'content_id' => 0,
                            'comment_id' => $comment_id,
                            'file_key' => $file_key,
                            'file_path' => $file_path,
                            'file_name' => $file_name,
                            'file_size' => $file_size,
                            'download_count' => 0,
                            'metadata' => $metadata,
                            'created_at' =>$date,
                        ]);
                    }
                }
            } else if ($upload_attach_files && is_array($upload_attach_files)) {
                foreach ($upload_attach_files as $attach_file) {
                    Helpers::board_delete_resize($this->abspath . $attach_file->path);
                    @unlink($this->abspath . $attach_file->path);
                }
            }

            // 댓글과 미디어의 관계를 입력한다.
            $media = new BoardCommentMediaManager();
            $media->board_id = $board->id;
            $media->comment_id = $comment_id;
            $media->media_group = post('media_group') ? Helpers::sanitize_key(post('media_group')) : '';
            $media->createRelationships();

            $comment_option = new BoardCommentOptionManager($comment_id);
            foreach ($option as $key => $value) {
                $comment_option->{$key} = $value;
            }

            // 댓글쓰기 증가 포인트
            if ($board->meta->comment_insert_up_point) {
//                if (function_exists('mycred_add')) {
//                    if (BackendAuth::check()) {
//                        $point = intval(get_user_meta(get_current_user_id(), 'board_comments_mycred_point', true));
//                        update_user_meta(get_current_user_id(), 'board_comments_mycred_point', $point + $board->meta->comment_insert_up_point);
//
//                        mycred_add('comment_insert_up_point', get_current_user_id(), $board->meta->comment_insert_up_point, __('Writing comment increase points', 'board-comments'));
//                    }
//                }
            }

            if ($comment_id) {
                session()->forget('board_temporary_comments');
            }
            $previousUrl = app('url')->previous();
            return redirect()->to($previousUrl."#board-comments-".$content_id);

        } catch (Exception $ex) {
            throw $ex;
        }
    }

    public function onDelete()
    {
        try{
            $id = post('id');
            $password = post('password')?: '';

            if(!$id){
                throw new AjaxException([
                    'error' => Lang::get('placecompany.boardcomment::lang.id is required.'),
                    'questionsNeeded' => 2
                ]);
            }
            else if(!BackendAuth::check() && !$password){
                return [
                    'state' => 'password confirm',
                ];
            }

            $comment = new BoardCommentManager();
            $comment->initWithID($id);
            $board = $comment->getBoard();

            if(!$comment->isEditor() && $comment->password != $password){
                throw new AjaxException([
                    'error' => Lang::get('placecompany.boardcomment::lang.You do not have permission.'),
                    'questionsNeeded' => 2
                ]);
            }

            \Event::fire('placecompany.board.board_comments_pre_delete', [$comment->id, $comment->content_id, $board]);

            $comment->delete();

            $previousUrl = app('url')->previous();
            return ['url' => $previousUrl];

        } catch(Exception $ex) {
            throw $ex;
        }
    }
}
