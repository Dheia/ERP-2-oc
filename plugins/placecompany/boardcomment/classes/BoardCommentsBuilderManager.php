<?php namespace Placecompany\BoardComment\Classes;

use Backend\Facades\BackendAuth;
use Illuminate\Support\Facades\Lang;
use Placecompany\Board\Classes\BoardUrlManager;
use Placecompany\Board\Classes\Helpers;
use stdClass;

/**
 * KBoard 워드프레스 게시판 댓글 빌더
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardCommentsBuilderManager {

    var $board;
    var $board_id;
    var $content_id;
    var $skin;
    var $skin_name;
    var $permission_comment_write;

    public function __construct(){
        global $kboard_comment_builder;
        $kboard_comment_builder = $this;

        $this->setSkin('default');
    }

    /**
     * 스킨을 지정한다.
     * @param string $skin_name
     * @return BoardCommentsBuilderManager
     */
    public function setSkin($skin_name){
        $this->skin = BoardCommentSkinManager::getInstance();
        $this->skin_name = $skin_name;
        return $this;
    }

    /**
     * 댓글창 화면을 생성한다.
     * @return string
     */
    public function create(){
        if(!$this->content_id) return 'KBoard 댓글 알림 :: content_id=null, content_id값은 필수 입니다.';

        $current_user = BackendAuth::getUser();
        $commentList = new BoardCommentListManager($this->content_id);
        $commentList->board = $this->board;

        $url = new BoardUrlManager();
        $url->setBoard($this->board);
        $comment_url = new BoardCommentUrlManager();
        $comment_url->setBoard($this->board);

        $vars = array(
            'content_id' => $this->content_id,
            'commentList' => $commentList,
            'temporary' => $this->getTemporary(),
            'url' => $url,
            'commentURL' => $comment_url,
            'user_id' => $current_user ? $current_user->id : '',
            'user_display' => $current_user ? $current_user->first_name: '방문객',
            'skin_path' => $this->skin->url($this->skin_name),
            'skin_dir' => $this->skin->dir($this->skin_name),
            'board' => $this->board,
            'commentBuilder' => $this,
            'board_media_group' => Helpers::board_media_group(),
            'board_use_recaptcha' => Helpers::board_use_recaptcha(),
            'board_captcha' => Helpers::board_captcha(),
            'board_recaptcha_site_key' => Helpers::board_recaptcha_site_key(),
        );

        \Event::fire('placecompany.board.board_comments_skin_header', [&$this]);

        echo $this->skin->load($this->skin_name, 'list.htm', $vars);

        \Event::fire('placecompany.board.board_comments_skin_footer', [&$this]);
    }

    /**
     * 댓글 리스트 트리를 생성한다.
     * @param string $template
     * @param string $parent_id
     * @param int $depth
     */
    public function buildTreeList($template, $parent_id='', $depth=0)
    {
        $current_user = BackendAuth::getUser();
        $commentList = new BoardCommentListManager();
        $commentList->board = $this->board;

        if($parent_id){
            $commentList->initWithParentID($parent_id);
        }
        else{
            $commentList->initWithID($this->content_id);
        }

        $url = new BoardUrlManager();
        $url->setBoard($this->board);
        $comment_url = new BoardCommentUrlManager();
        $comment_url->setBoard($this->board);

        $vars = array(
            'content_id' => $this->content_id,
            'commentList' => $commentList,
            'depth' => $depth,
            'url' => $url,
            'commentURL' => $comment_url,
            'user_id' => $current_user ? $current_user->id : '',
            'user_display' => $current_user ? $current_user->display_name : 'Anonymous',
            'skin_path' => $this->skin->url($this->skin_name),
            'skin_dir' => $this->skin->dir($this->skin_name),
            'board' => $this->board,
            'commentBuilder' => $this,
        );

        return $this->skin->load($this->skin_name, $template, $vars);
    }

    /**
     * 댓글 쓰기 권한이 있는 사용자인지 확인한다.
     * @return boolean
     */
    public function isWriter(){
        if(!$this->permission_comment_write){
            return true;
        }
        else if(BackendAuth::check()){
            if($this->permission_comment_write == '1'){
                return true;
            }
            else if($this->permission_comment_write == 'roles'){
                if(array_intersect($this->board->getCommentRoles(), Helpers::board_current_user_roles())){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 댓글 쓰기 권한이 있는 사용자인지 확인한다.
     * @return boolean
     */
    public function getTemporary(){
        static $temporary;
        if($temporary === null){
            if(session('board_temporary_comments')){
                $temporary = session('board_temporary_comments');
            }
            else{
                $temporary = new stdClass();
                $temporary->member_display = '';
                $temporary->content = '';
            }
            if(!isset($temporary->option) || !(array)$temporary->option){
                $temporary->option = new BoardCommentOptionManager();
            }
        }
        return $temporary;
    }
}
?>
