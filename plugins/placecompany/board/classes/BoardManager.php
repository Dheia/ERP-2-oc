<?php namespace Placecompany\Board\Classes;
use Backend\Facades\BackendAuth;
use Cms\Classes\ComponentManager;
use Cms\Classes\Controller;
use Cms\Classes\PartialStack;
use Placecompany\Board\Components\BoardComment;
use Placecompany\Board\Models\BoardContent;
use Placecompany\Board\Models\BoardMeta;
use Placecompany\Board\Models\BoardSetting;
use Placecompany\Board\Models\Settings;
use RainLab\User\Facades\Auth;
use stdClass;
use System\Classes\PluginManager;

/**
 * BoardManager 워드프레스 게시판 설정
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardManager {

    private $fields;

    var $id;
    var $row;
    var $content;
    var $category;
    var $category_row;
    var $tree_category;
    var $current_user;
    var $meta;

    public function __construct($id=''){
        $this->row = new stdClass();
        $this->meta = new BoardMetaManager();
        $this->fields = null;
        $this->tree_category = new BoardTreeCategoryManager();
        $this->current_user = BackendAuth::getUser();
        $this->setID($id);
    }

    public function __get($name){
        if(isset($this->row->{$name})){
            return $this->row->{$name};
        }
        return '';
    }

    /**
     * 게시판 아이디값을 입력받는다.
     * @param int $id
     * @return BoardManager
     */
    public function setID($id){
        $id = intval($id);
        if($id){
            $this->row = BoardSetting::find($id);
            if(isset($this->row->id) && $this->row->id){
                $this->id = $this->row->id;
                $this->meta = new BoardMetaManager($this->row->id);
                $this->fields = new BoardFieldsManager($this);
                $this->tree_category = new BoardTreeCategoryManager($this->meta->tree_category);
                return $this;
            }
        }
        $this->id = 0;
        $this->meta = new BoardMetaManager();
        $this->fields = null;
        $this->tree_category = new BoardTreeCategoryManager();
        return $this;
    }

    /**
     * 게시판 아이디값을 반환한다.
     * @return int
     */
    public function getID(){
        return $this->id;
    }

    /**
     * 게시판 아이디값을 반환한다.
     * @return int
     */
    public function ID(){
        return $this->id;
    }

    /**
     * 게시판 정보를 입력받는다.
     * @param object $row
     * @return BoardManager
     */
    public function initWithRow($row){
        $this->row = $row;
        if(isset($this->row->id) && $this->row->id){
            $this->id = $this->row->id;
            $this->meta = new BoardMetaManager($this->row->id);
            $this->fields = new BoardFieldsManager($this);
            $this->tree_category = new BoardTreeCategoryManager($this->meta->tree_category);
        }
        else{
            $this->id = 0;
            $this->meta = new BoardMetaManager();
            $this->fields = null;
            $this->tree_category = new BoardTreeCategoryManager();
        }
        return $this;
    }

    /**
     * 게시글이 등록된 게시판 정보를 초기화한다.
     * @param int $content_id
     * @return BoardManager
     */
    public function initWithContentID($content_id){
        $content_id = intval($content_id);
        if($content_id){
            $this->row = BoardContent::find($content_id)->boardSetting;
            if(isset($this->row->id) && $this->row->id){
                $this->id = $this->row->id;
                $this->meta = new BoardMetaManager($this->row->id);
                $this->fields = new BoardFieldsManager($this);
                $this->tree_category = new BoardTreeCategoryManager($this->meta->tree_category);
                return $this;
            }
        }
        $this->id = 0;
        $this->meta = new BoardMetaManager();
        $this->fields = null;
        $this->tree_category = new BoardTreeCategoryManager();
        return $this;
    }

    /**
     * 카테고리 정보를 초기화 한다.
     */
    public function initCategory1(){
        $this->category = explode(',', $this->category1_list);
        return $this->category1_list;
    }

    /**
     * 두번째 카테코리 정보를 초기화 한다.
     */
    public function initCategory2(){
        $this->category = explode(',', $this->category2_list);
        return $this->category2_list;
    }

    /**
     * 다음 카테고리 정보를 반환한다.
     * @return object
     */
    public function hasNextCategory(){
        if(!$this->category) $this->initCategory1();
        $this->category_row = current($this->category);

        if(!$this->category_row) unset($this->category);
        else next($this->category);

        return $this->category_row;
    }

    /**
     * 카테고리 정보를 반환한다.
     */
    public function currentCategory(){
        return $this->category_row;
    }

    /**
     * 게시물의 댓글 폼과 리스트를 생성한다.
     * @param int $content_id
     * @return string
     */
    public function buildComment($content_id){
        if($this->id && $content_id && $this->isComment()){
            if($this->meta->comments_plugin_id && $this->meta->use_comments_plugin){
                $template = new BoardTemplateManager();
                return $template->comments_plugin($this->meta);
            }
            else{
                $args['board'] = $this;
                $args['board_id'] = $this->id;
                $args['content_id'] = $content_id;
                $args['skin'] = $this->meta->comment_skin;
                $args['permission_comment_write'] = $this->meta->permission_comment_write;

                $controller = Controller::getController();

                return $controller->renderComponent('BoardComment', $args);
            }
        }
        return '';
    }

    /**
     * 글 읽기 권한이 있는 사용자인지 확인한다.
     * @param int $user_id
     * @param string $secret
     * @return boolean
     */
    public function isReader($user_id, $secret=''){
        if($this->permission_read == 'all' && !$secret){
            return true;
        }
        else if(BackendAuth::check()){
            if($user_id == BackendAuth::getUser()->id){
                // 본인 허용
                return true;
            }
            else if($this->isAdmin()){
                // 게시판 관리자 허용
                return true;
            }
            else if($this->permission_read == 'author' && !$secret){
                // 로그인 사용자 허용
                return true;
            }
            else if($this->permission_read == 'roles' && !$secret){
                // 선택된 역할의 사용자 허용
                if(array_intersect($this->getReadRoles(), Helpers::board_current_user_roles())){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 글 쓰기 권한이 있는 사용자인지 확인한다.
     * @return boolean
     */
    public function isWriter(){
        if($this->permission_write == 'all'){
            return true;
        }
        else if(BackendAuth::check()){
            if($this->isAdmin()){
                // 게시판 관리자 허용
                return true;
            }
            else if($this->permission_write == 'author'){
                // 로그인 사용자 허용
                return true;
            }
            else if($this->permission_write == 'roles'){
                // 선택된 역할의 사용자 허용
                if(array_intersect($this->getWriteRoles(), Helpers::board_current_user_roles())){
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * 글 수정 권한이 있는 사용자인지 확인한다.
     * @param int $user_id
     * @return boolean
     */
    public function isEditor($user_id){
        if(BackendAuth::check()){
            if($user_id == BackendAuth::getUser()->id){
                // 본인 허용
                return true;
            }
            else if($this->isAdmin()){
                // 게시판 관리자 허용
                return true;
            }
        }
        return false;
    }

    /**
     * 답글쓰기 권한이 있는 사용자인지 확인한다.
     * @return boolean
     */
    public function isReply(){
        if(!$this->meta->permission_reply){
            return true;
        }
        else if(BackendAuth::check()){
            if($this->isAdmin()){
                // 게시판 관리자 허용
                return true;
            }
            else if($this->meta->permission_reply == 'roles'){
                // 선택된 역할의 사용자 허용
                if(array_intersect($this->getReplyRoles(), Helpers::board_current_user_roles())){
                    return true;
                }
            }
            else{
                // 로그인 사용자 허용
                return true;
            }
        }
        return false;
    }

    public function isBuyer($user_id){
        if(BackendAuth::check()){
            if($user_id == BackendAuth::getUser()->id){
                // 본인 허용
                return true;
            }
            else if($this->isAdmin()){
                // 게시판 관리자 허용
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
        if(!$this->meta->permission_attachment_download){
            return true;
        }
        else if(BackendAuth::check()){
            if($this->isAdmin()){
                // 게시판 관리자 허용
                return true;
            }
            else if($this->meta->permission_attachment_download == 'roles'){
                // 선택된 역할의 사용자 허용
                if(array_intersect($this->getAttachmentDownloadRoles(), Helpers::board_current_user_roles())){
                    return true;
                }
            }
            else{
                // 로그인 사용자 허용
                return true;
            }
        }
        return false;
    }

    /**
     * 추천권한이 있는 사용자인지 확인한다.
     * @return boolean
     */
    public function isVote(){
        if(!$this->meta->permission_vote){
            return true;
        }
        else if(BackendAuth::check()){
            if($this->isAdmin()){
                // 게시판 관리자 허용
                return true;
            }
            else if($this->meta->permission_vote == 'roles'){
                // 선택된 역할의 사용자 허용
                if(array_intersect($this->getVoteRoles(), Helpers::board_current_user_roles())){
                    return true;
                }
            }
            else{
                // 로그인 사용자 허용
                return true;
            }
        }
        return false;
    }

    /**
     * 게시글 비밀번호와 일치하는지 확인한다.
     * @param string $content_password
     * @param int $content_id
     * @param boolean $reauth
     * @return boolean
     */
    public function isConfirm($content_password, $content_id, $reauth=false){
        $confirm = false;
        $input_password = '';

        \Event::fire('placecompany.board.board_password_confirm_reauth', [&$reauth, $this]);

        if($content_password && $content_id){
            $input_password = isset($_POST['password']) ? e($_POST['password']) : '';

            if($reauth){
                if($input_password == $content_password){
                    $_SESSION['board_confirm'][$content_id] = $content_password;
                    $confirm = true;
                }
            }
            else if(isset($_SESSION['board_confirm']) && isset($_SESSION['board_confirm'][$content_id]) && $_SESSION['board_confirm'][$content_id] == $content_password){
                $confirm = true;
            }
            else if($input_password == $content_password){
                $_SESSION['board_confirm'][$content_id] = $content_password;
                $confirm = true;
            }
        }

        return \Event::fire('placecompany.board.board_password_confirm', [$confirm, $input_password, $content_password, $content_id, $reauth, $this]);
    }

    /**
     * 비밀번호 확인에 실패했는지 확인한다.
     * @return boolean
     */
    public function isConfirmFailed(){
        $submitted_password = isset($_POST['password']) ? e($_POST['password']) : '';
        if($submitted_password){
            return true;
        }
        return false;
    }

    /**
     * 관리자인지 확인한다.
     * @return boolean
     */
    public function isAdmin(){
        if($this->id && BackendAuth::check()){
            $admin_user = explode(',', $this->admin_user);
            $admin_user = array_map('e', $admin_user);

            if(in_array('administrator', Helpers::board_current_user_roles())){
                // 최고관리자 허용
                return true;
            }
            else if(is_array($admin_user) && in_array($this->current_user->user_login, $admin_user)){
                // 선택된 관리자 허용
                return true;
            }
            else if(array_intersect($this->getAdminRoles(), Helpers::board_current_user_roles())){
                // 선택된 역할의 사용자 허용
                return true;
            }
        }
        return false;
    }

    /**
     * 댓글 플러그인이 있고, 해당 게시판에서 댓글을 사용하는지 확인한다.
     * @return boolean
     */
    public function isComment(){
        $manager = PluginManager::instance();
        if($manager->exists('Placecompany.BoardComment') && $this->use_comment) return true;
        if($this->meta->comments_plugin_id && $this->meta->use_comments_plugin) return true;
        return false;
    }

    /**
     * 주문시 포인트를 사용할 수 있는지 확인한다.
     * @return boolean
     */
    public function isUsePointOrder(){
        if(class_exists('myCRED_Core')){
            return true;
        }
        return false;
    }

    public function isTreeCategoryActive(){
        if($this->use_category && $this->meta->use_tree_category){
            return true;
        }
        return false;
    }

    /**
     * 읽기권한의 role을 반환한다.
     * @return array
     */
    public function getReadRoles(){
        if($this->meta->permission_read_roles){
            return json_decode($this->meta->permission_read_roles, true);
        }
        return array();
    }

    /**
     * 쓰기권한의 role을 반환한다.
     * @return array
     */
    public function getWriteRoles(){
        if($this->meta->permission_write_roles){
            return json_decode($this->meta->permission_write_roles, true);
        }
        return array();
    }

    /**
     * 답글쓰기권한의 role을 반환한다.
     * @return array
     */
    public function getReplyRoles(){
        if($this->meta->permission_reply_roles){
            return json_decode($this->meta->permission_reply_roles, true);
        }
        return array();
    }

    /**
     * 댓글쓰기권한의 role을 반환한다.
     * @return array
     */
    public function getCommentRoles(){
        if($this->meta->permission_comment_write_roles){
            return unserialize($this->meta->permission_comment_write_roles);
        }
        return array();
    }

    /**
     * 주문하기권한의 role을 반환한다.
     * @return array
     */
    public function getOrderRoles(){
        if($this->meta->permission_order_roles){
            return json_decode($this->meta->permission_order_roles, true);
        }
        return array();
    }

    /**
     * 관리자권한의 role을 반환한다.
     * @return array
     */
    public function getAdminRoles(){
        if($this->meta->permission_admin_roles){
            return json_decode($this->meta->permission_admin_roles, true);
        }
        return array();
    }

    /**
     * 첨부파일 다운로드 권한의 role을 반환한다.
     * @return array
     */
    public function getAttachmentDownloadRoles(){
        if($this->meta->permission_attachment_download_roles){
            return json_decode($this->meta->permission_attachment_download_roles, true);
        }
        return array();
    }

    /**
     * 추천권한의 role을 반환한다.
     * @return array
     */
    public function getVoteRoles(){
        if($this->meta->permission_vote_roles){
            return json_decode($this->meta->permission_vote_roles, true);
        }
        return array();
    }

    /**
     * 게시판을 삭제한다.
     * @param int $board_id
     */
    public function delete($board_id=''){
        $board_id = intval($board_id);
        if($board_id){
            $this->remove($board_id);
        }
        else if($this->id){
            $this->remove($this->id);
        }
    }

    /**
     * 게시판을 삭제한다.
     * @param int $board_id
     */
    public function remove($board_id){
        $board_id = intval($board_id);
        if($board_id){
            $list = new BoardContentListManager($board_id);
            $list->rpp(1000);
            $list->initFirstList();

            while($list->hasNextList()){
                while($content = $list->hasNext()){
                    $content->delete(false);
                }
                $list->initFirstList();
            }

            BoardSetting::find($board_id)->delete();
            BoardMeta::find($board_id)->delete();
        }
    }

    /**
     * 모든 게시글을 삭제한다.
     */
    public function truncate(){
        if($this->id){
            $list = new BoardContentListManager($this->id);
            $list->rpp(1000);
            $list->initFirstList();

            while($list->hasNextList()){
                while($content = $list->hasNext()){
                    $content->delete(false);
                }
                $list->initFirstList();
            }

            $this->resetTotal();
        }
    }

    /**
     * 게시판에서 CAPTCHA 사용 여부를 확인한다.
     * @return boolean
     */
    public function useCAPTCHA(){
        if(BackendAuth::check() || Settings::get('board_captcha_stop')){
            return \Event::fire('placecompany.board.board_use_captcha', [false, $this]);
        }
        return \Event::fire('placecompany.board.board_use_captcha', [true, $this]);
    }

    /**
     * 게시판에서 비로그인 작성자 입력 필드 보여줄지 확인한다.
     * @return boolean
     */
    public function viewUsernameField(){
        if(!BackendAuth::check() || ($this->content->id && !$this->content->user_id)){
            return true;
        }
        return false;
    }

    /**
     * 게시판에 등록된 전체 게시글 숫자를 반환한다.
     * @return int
     */
    public function getTotal(){
        if(!$this->id){
            return 0;
        }
        if(!$this->meta->total || $this->meta->total<=0){
            $this->meta->total = BoardContent::where('board_id', $this->id)->count();
        }
        return intval($this->meta->total);
    }

    /**
     * 게시판 리스트에 표시되는 게시글 숫자를 반환한다.
     * @return int
     */
    public function getListTotal(){
        if(!$this->id){
            return 0;
        }
        if(!$this->meta->list_total || $this->meta->list_total<=0){
            $this->meta->list_total = $this->getTotal();

            $results = BoardContent::where('board_id', $this->id)->where('status', 'trash')->get();

            foreach($results as $row){
                $content = new BoardContentManager();
                $content->initWithRow($row);
                $content->board = $this;
                $content->moveReplyToTrash($content->id);
            }
        }
        return intval($this->meta->list_total);
    }

    /**
     * 게시글 숫자를 초기화한다.
     */
    public function resetTotal(){
        if($this->id){
            $this->meta->total = 0;
            $this->meta->list_total = 0;
        }
    }

    /**
     * 본인의 글만 보기인지 확인한다.
     */
    public function isPrivate(){
        if($this->meta->permission_list && !$this->isAdmin()){
            return true;
        }
        return false;
    }

    /**
     * 입력된 숫자를 통화 형식으로 반환한다.
     * @param int $value
     * @param string $format
     * @return string
     */
    public function currency($value, $format='%s원'){
        return sprintf(\Event::fire('placecompany.board.board_currency_format', [$format, $this], number_format($value)));
    }

    /**
     * 해당 카테고리에 등록된 게시글 숫자를 반환한다.
     * @param array|string $category
     * @return int
     */
    public function getCategoryCount($category){
        if($this->id && $category){
            $where[] = "`board_id`='{$this->id}'";

            if(is_array($category)){
                if(isset($category['category1']) && $category['category1']){
                    $category1 = e($category['category1']);
                    $where[] = "`category1`='{$category1}'";
                }

                if(isset($category['category2']) && $category['category2']){
                    $category2 = e($category['category2']);
                    $where[] = "`category2`='{$category2}'";
                }
            }
            else{
                $category = e($category);
                $where[] = "(`category1`='{$category}' OR `category2`='{$category}')";
            }

            $where[] = "(`status`='' OR `status` IS NULL OR `status`='pending_approval')";

            $count = \DB::table('placecompany_board_content')->whereRaw(implode(' AND ', $where))->count();

            return intval($count);
        }
        return 0;
    }

    /**
     * BoardManager 커뮤니티에 기여합니다.
     * @return boolean
     */
    public function contribution(){
        $contribution = true;
        return \Event::fire('placecompany.board.board_contribution', [$contribution, $this]);
    }

    /**
     * 필드 클래스를 반환한다.
     * @return BoardFieldsManager
     */
    public function fields(){
        if(!$this->fields){
            $this->fields = new BoardFieldsManager($this);
        }
        return $this->fields;
    }
}
?>
