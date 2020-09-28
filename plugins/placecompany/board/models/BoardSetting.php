<?php namespace Placecompany\Board\Models;

use Placecompany\Board\Classes\BoardMetaManager;
use Placecompany\Board\Classes\BoardSkinManager;
use Model;
use Placecompany\BoardComment\Classes\BoardCommentSkinManager;

/**
 * Model
 */
class BoardSetting extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'placecompany_board_setting';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $timestamps = true;

    /**
     * The BoardMeta that belong to the BoardSetting.
     */
    public function boardMetas()
    {
        return $this->hasMany(BoardMeta::class, 'board_id');
    }

    /**
     * The BoardContentManager that belong to the BoardSetting.
     */
    public function boardContents()
    {
        return $this->hasMany(BoardContent::class, 'board_id');
    }

    public function getNoticeInvisibleComments()
    {
        $result = [];
        $boardSkin = BoardMeta::getInstance();
        foreach ($boardSkin->getList() as $item) {
            $result[$item->name] = $item->name;
        }

        return $result;
    }

    public function getSkinOptions()
    {
        $result = [];
        $boardSkin = BoardSkinManager::getInstance();
        foreach ($boardSkin->getList() as $item) {
            $result[$item->name] = $item->name;
        }

        return $result;
    }

    public function getCommentSkinOptions()
    {
        $result = [];
        $boardSkin = BoardCommentSkinManager::getInstance();
        foreach ($boardSkin->getList() as $item) {
            $result[$item->name] = $item->name;
        }

        return $result;
    }

    public function filterFields($fields, $context = null)
    {
        if(!$this->id) {
            $fields->_input_field->hidden = true;
            $fields->_tree_category->hidden = true;
            $fields->_shortcode_execute->hidden = true;
            $fields->_default_content->hidden = true;
            $fields->_reply_copy_content->hidden = true;
            $fields->_use_direct_url->hidden = true;
            $fields->_pass_autop->hidden = true;
            $fields->_view_iframe->hidden = true;
            $fields->_editor_view_iframe->hidden = true;
            $fields->_conversion_tracking_code->hidden = true;
            $fields->_default_build_mod->hidden = true;
            $fields->_after_executing_mod->hidden = true;
            $fields->_document_insert_up_point->hidden = true;
            $fields->_document_insert_down_point->hidden = true;
            $fields->_document_delete_up_point->hidden = true;
            $fields->_document_delete_down_point->hidden = true;
            $fields->_document_read_down_point->hidden = true;
            $fields->_attachment_download_down_point->hidden = true;
            $fields->_comment_insert_up_point->hidden = true;
            $fields->_comment_insert_down_point->hidden = true;
            $fields->_comment_delete_down_point->hidden = true;
        }
    }

    public function afterSave()
    {
        try {
            \Db::beginTransaction();

            $post = post();
            $meta_array = [
                'notice_invisible_comments' => isset($post['BoardSetting']['_notice_invisible_comments']) ? $post['BoardSetting']['_notice_invisible_comments'] : '',
                'use_direct_url' => isset($post['BoardSetting']['_use_direct_url']) ? $post['BoardSetting']['_use_direct_url'] : '',
                'latest_alerts' => isset($post['BoardSetting']['_latest_alerts']) ? $post['BoardSetting']['_latest_alerts'] : '',
                'latest_alerts_attachments_size' => isset($post['BoardSetting']['_latest_alerts_attachments_size']) ? $post['BoardSetting']['_latest_alerts_attachments_size'] : '',
                'comment_skin' => isset($post['BoardSetting']['_comment_skin']) ? $post['BoardSetting']['_comment_skin'] : '',
                'use_tree_category' => isset($post['BoardSetting']['_use_tree_category']) ? $post['BoardSetting']['_use_tree_category'] : '',
                'default_content' => isset($post['BoardSetting']['_default_content']) ? $post['BoardSetting']['_default_content'] : '',
                'pass_autop' => isset($post['BoardSetting']['_pass_autop']) ? $post['BoardSetting']['_pass_autop'] : '',
                'shortcode_execute' => isset($post['BoardSetting']['_shortcode_execute']) ? $post['BoardSetting']['_shortcode_execute'] : '',
                'autolink' => isset($post['BoardSetting']['_autolink']) ? $post['BoardSetting']['_autolink'] : '',
                'reply_copy_content' => isset($post['BoardSetting']['_reply_copy_content']) ? $post['BoardSetting']['_reply_copy_content'] : '',
                'view_iframe' => isset($post['BoardSetting']['_view_iframe']) ? $post['BoardSetting']['_view_iframe'] : '',
                'editor_view_iframe' => isset($post['BoardSetting']['_editor_view_iframe']) ? $post['BoardSetting']['_editor_view_iframe'] : '',
                'permission_list' => isset($post['BoardSetting']['_permission_list']) ? $post['BoardSetting']['_permission_list'] : '',
                'permission_access' => isset($post['BoardSetting']['_permission_access']) ? $post['BoardSetting']['_permission_access'] : '',
                'permission_reply' => isset($post['BoardSetting']['_permission_reply']) ? $post['BoardSetting']['_permission_reply'] : '',
                'permission_comment_write' => isset($post['BoardSetting']['_permission_comment_write']) ? $post['BoardSetting']['_permission_comment_write'] : '',
                'permission_comment_read' => isset($post['BoardSetting']['_permission_comment_read']) ? $post['BoardSetting']['_permission_comment_read'] : '',
                'permission_comment_read_minute' => isset($post['BoardSetting']['_permission_comment_read_minute']) ? $post['BoardSetting']['_permission_comment_read_minute'] : '',
                'permission_order' => isset($post['BoardSetting']['_permission_order']) ? $post['BoardSetting']['_permission_order'] : '',
                'permission_attachment_download' => isset($post['BoardSetting']['_permission_attachment_download']) ? $post['BoardSetting']['_permission_attachment_download'] : '',
                'permission_vote' => isset($post['BoardSetting']['_permission_vote']) ? $post['BoardSetting']['_permission_vote'] : '',
                'comments_plugin_id' => isset($post['BoardSetting']['_comments_plugin_id']) ? $post['BoardSetting']['_comments_plugin_id'] : '',
                'use_comments_plugin' => isset($post['BoardSetting']['_use_comments_plugin']) ? $post['BoardSetting']['_use_comments_plugin'] : '',
                'comments_plugin_row' => isset($post['BoardSetting']['_comments_plugin_row']) ? $post['BoardSetting']['_comments_plugin_row'] : '',
                'conversion_tracking_code' => isset($post['BoardSetting']['_conversion_tracking_code']) ? $post['BoardSetting']['_conversion_tracking_code'] : '',
                'always_view_list' => isset($post['BoardSetting']['_always_view_list']) ? $post['BoardSetting']['_always_view_list'] : '',
                'max_attached_count' => isset($post['BoardSetting']['_max_attached_count']) ? $post['BoardSetting']['_max_attached_count'] : '',
                'list_sort_numbers' => isset($post['BoardSetting']['_list_sort_numbers']) ? $post['BoardSetting']['_list_sort_numbers'] : '',
                'permit' => isset($post['BoardSetting']['_permit']) ? $post['BoardSetting']['_permit'] : '',
                'secret_checked_default' => isset($post['BoardSetting']['_secret_checked_default']) ? $post['BoardSetting']['_secret_checked_default'] : '',
                'default_build_mod' => isset($post['BoardSetting']['_default_build_mod']) ? $post['BoardSetting']['_default_build_mod'] : '',
                'after_executing_mod' => isset($post['BoardSetting']['_after_executing_mod']) ? $post['BoardSetting']['_after_executing_mod'] : '',
                'skin_fields' => isset($post['fields']) ? json_encode($post['fields']) : '',
                'document_insert_up_point' => isset($post['BoardSetting']['_document_insert_up_point']) ? $post['BoardSetting']['_document_insert_up_point'] : '',
                'document_insert_down_point' => isset($post['BoardSetting']['_document_insert_down_point']) ? $post['BoardSetting']['_document_insert_down_point'] : '',
                'document_delete_up_point' => isset($post['BoardSetting']['_document_delete_up_point']) ? $post['BoardSetting']['_document_delete_up_point'] : '',
                'document_delete_down_point' => isset($post['BoardSetting']['_document_delete_down_point']) ? $post['BoardSetting']['_document_delete_down_point'] : '',
                'document_read_down_point' => isset($post['BoardSetting']['_document_read_down_point']) ? $post['BoardSetting']['_document_read_down_point'] : '',
                'attachment_download_down_point' => isset($post['BoardSetting']['_attachment_download_down_point']) ? $post['BoardSetting']['_attachment_download_down_point'] : '',
                'comment_insert_up_point' => isset($post['BoardSetting']['_comment_insert_up_point']) ? $post['BoardSetting']['_comment_insert_up_point'] : '',
                'comment_insert_down_point' => isset($post['BoardSetting']['_comment_insert_down_point']) ? $post['BoardSetting']['_comment_insert_down_point'] : '',
                'comment_delete_up_point' => isset($post['BoardSetting']['_comment_delete_up_point']) ? $post['BoardSetting']['_comment_delete_up_point'] : '',
                'comment_delete_down_point' => isset($post['BoardSetting']['_comment_delete_down_point']) ? $post['BoardSetting']['_comment_delete_down_point'] : '',
            ];

            if (isset($post['BoardSetting']['_permission_read_roles'])) {
                $meta_array['permission_read_roles'] = json_encode($post['BoardSetting']['_permission_read_roles']);
            }
            if (isset($post['BoardSetting']['_permission_write_roles'])) {
                $meta_array['permission_write_roles'] = json_encode($post['BoardSetting']['_permission_write_roles']);
            }
            if (isset($post['BoardSetting']['_permission_reply_roles'])) {
                $meta_array['permission_reply_roles'] = json_encode($post['BoardSetting']['_permission_reply_roles']);
            }
            if (isset($post['BoardSetting']['_permission_comment_write_roles'])) {
                $meta_array['permission_comment_write_roles'] = json_encode($post['BoardSetting']['_permission_comment_write_roles']);
            }
            if (isset($post['BoardSetting']['_permission_order_roles'])) {
                $meta_array['permission_order_roles'] = json_encode($post['BoardSetting']['_permission_order_roles']);
            }
            if (isset($post['BoardSetting']['_permission_admin_roles'])) {
                $meta_array['permission_admin_roles'] = json_encode($post['BoardSetting']['_permission_admin_roles']);
            }
            if (isset($post['BoardSetting']['_permission_vote_roles'])) {
                $meta_array['permission_vote_roles'] = json_encode($post['BoardSetting']['_permission_vote_roles']);
            }
            if (isset($post['BoardSetting']['_permission_attachment_download_roles'])) {
                $meta_array['permission_attachment_download_roles'] = json_encode($post['BoardSetting']['_permission_attachment_download_roles']);
            }

            // 메타데이터 등록
            BoardMetaManager::registerMetaList($this, $meta_array);

            // 이벤트 실행
            \Event::fire('placecompany.board.boardSettingUpdate', [$this->boardMetas, $this->id]);
            \Event::fire("placecompany.board.{$this->skin}BoardSettingUpdate", [$this->boardMetas, $this->id]);

        } catch (\Exception $ex) {
            \Db::rollBack();

            if (\Request::ajax()) throw $ex;
            else \Flash::error($ex->getMessage());
        }

        \Db::commit();
    }

}
