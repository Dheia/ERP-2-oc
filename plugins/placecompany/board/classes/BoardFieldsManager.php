<?php namespace Placecompany\Board\Classes;

use Backend\Facades\BackendAuth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Lang;
use stdClass;

/**
 * KBoard 필드
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardFieldsManager {

    var $board;
    var $default_fields = array();
    var $extends_fields = array();
    var $skin_fields = array();

    public function __construct($value){
        if($value){
            $this->setBoardID($value);
        }

        $this->default_fields = array(
            'title' => array(
                'field_type' => 'title',
                'field_label' => Lang::get('placecompany.board::lang.Title'),
                'field_name' => '',
                'class' => 'board-attr-title',
                'meta_key' => 'title',
                'permission' => 'all',
                'roles' => array(),
                'default_value' => '',
                'placeholder' => '',
                'description' => '',
                'close_button' => ''
            ),
            'option' => array(
                'field_type' => 'option',
                'field_label' => Lang::get('placecompany.board::lang.Options'),
                'field_name' => '',
                'class' => 'board-attr-option',
                'meta_key' => 'option',
                'secret_permission' => '',
                'secret' => array(),
                'notice_permission' => 'roles',
                'notice'=> array('administrator'),
                'description' => '',
                'close_button' => 'yes'
            ),
            'author' => array(
                'field_type' => 'author',
                'field_label' => Lang::get('placecompany.board::lang.Author'),
                'field_name' => '',
                'class' => 'board-attr-author',
                'meta_key' => 'author',
                'permission' => '',
                'default_value' => '',
                'placeholder' => '',
                'description' => '',
                'close_button' => ''
            ),
            'category1' => array(
                'field_type' => 'category1',
                'field_label' => Lang::get('placecompany.board::lang.Category1'),
                'field_name' => '',
                'class' => 'board-attr-category1',
                'meta_key' => 'category1',
                'permission' => '',
                'roles' => array(),
                'default_value' => '',
                'description' => '',
                'required' => '',
                'close_button' => 'yes'
            ),
            'category2' => array(
                'field_type' => 'category2',
                'field_label' => Lang::get('placecompany.board::lang.Category2'),
                'field_name' => '',
                'class' => 'board-attr-category2',
                'meta_key' => 'category2',
                'permission' => '',
                'roles' => array(),
                'default_value' => '',
                'description' => '',
                'required' => '',
                'close_button' => 'yes'
            ),
            'tree_category' => array(
                'field_type' => 'tree_category',
                'field_label' => Lang::get('placecompany.board::lang.Tree Category'),
                'field_name' => '',
                'class' => 'board-attr-tree-category',
                'meta_key' => 'tree_category',
                'permission' => '',
                'roles' => array(),
                'option_field' => true,
                'description' => '',
                'close_button' => 'yes'
            ),
            'captcha' => array(
                'field_type' => 'captcha',
                'field_label' => Lang::get('placecompany.board::lang.Captcha'),
                'class' => 'board-attr-captcha',
                'meta_key' => 'captcha',
                'description' => '',
                'close_button' => 'yes'
            ),
            'content' => array(
                'field_type' => 'content',
                'field_label' => Lang::get('placecompany.board::lang.Content'),
                'field_name' => '',
                'class' => 'board-attr-content',
                'meta_key' => 'content',
                'placeholder' => '',
                'description' => '',
                'required' => '',
                'close_button' => 'yes'
            ),
            'media' => array(
                'field_type' => 'media',
                'field_label' => Lang::get('placecompany.board::lang.Photos'),
                'field_name' => '',
                'class' => 'board-attr-media',
                'meta_key' => 'media',
                'permission' => '',
                'roles' => array(),
                'description' => '',
                'close_button' => 'yes'
            ),
            'thumbnail' => array(
                'field_type' => 'thumbnail',
                'field_label' => Lang::get('placecompany.board::lang.Thumbnail'),
                'field_name' => '',
                'class' => 'board-attr-thumbnail',
                'meta_key' => 'thumbnail',
                'permission' => '',
                'roles' => array(),
                'description' => '',
                'close_button' => 'yes'
            ),
            'attach' => array(
                'field_type' => 'attach',
                'field_label' => Lang::get('placecompany.board::lang.Attachment'),
                'field_name' => '',
                'class' => 'board-attr-attach',
                'meta_key' => 'attach',
                'permission' => '',
                'roles' => array(),
                'description' => '',
                'close_button' => 'yes'
            ),
            'search' => array(
                'field_type' => 'search',
                'field_label' => Lang::get('placecompany.board::lang.WP Search'),
                'field_name' => '',
                'class' => 'board-attr-search',
                'meta_key' => 'search',
                'permission' => '',
                'roles' => array(),
                'default_value' => '',
                'description' => '',
                'hidden' => '',
                'close_button' => ''
            ),
            'ip' => array(
                'field_type' => 'ip',
                'field_label' => Lang::get('placecompany.board::lang.IP Address'),
                'class' => 'board-attr-ip',
                'board_extends' => '',
                'meta_key' => 'ip',
                'show_document' => '',
                'option_field' => true,
                'close_button' => 'yes'
            )
        );

        $this->extends_fields = array(
            'text' => array(
                'field_type' => 'text',
                'field_label' => Lang::get('placecompany.board::lang.Text/Hidden'),
                'field_name' => '',
                'class' => 'board-attr-text',
                'meta_key' => '',
                'permission' => '',
                'roles' => array(),
                'default_value' => '',
                'placeholder' => '',
                'description' => '',
                'required' => '',
                'show_document' => '',
                'hidden' => '',
                'close_button' => 'yes'
            ),
            'select' => array(
                'field_type' => 'select',
                'field_label' => Lang::get('placecompany.board::lang.Select Box'),
                'field_name' => '',
                'class' => 'board-attr-select',
                'meta_key' => '',
                'row' => array(),
                'default_value' => '',
                'permission' => '',
                'roles' => array(),
                'description' => '',
                'required' => '',
                'show_document' => '',
                'close_button' => 'yes'
            ),
            'radio' => array(
                'field_type' => 'radio',
                'field_label' => Lang::get('placecompany.board::lang.Radio Button'),
                'field_name' => '',
                'class' => 'board-attr-radio',
                'meta_key' => '',
                'row' => array(),
                'default_value' => '',
                'permission' => '',
                'roles' => array(),
                'description' => '',
                'required' => '',
                'show_document' => '',
                'close_button' => 'yes'
            ),
            'checkbox' => array(
                'field_type' => 'checkbox',
                'field_label' => Lang::get('placecompany.board::lang.Checkbox'),
                'field_name' => '',
                'class' => 'board-attr-checkbox',
                'meta_key' => '',
                'row' => array(),
                'permission' => '',
                'roles' => array(),
                'description' => '',
                'required' => '',
                'show_document' => '',
                'close_button' => 'yes'
            ),
            'textarea' => array(
                'field_type' => 'textarea',
                'field_label' => Lang::get('placecompany.board::lang.Textarea'),
                'field_name' => '',
                'class' => 'board-attr-textarea',
                'meta_key' => '',
                'permission' => '',
                'roles' => array(),
                'default_value' => '',
                'placeholder' => '',
                'required' => '',
                'show_document' => '',
                'description' => '',
                'close_button' => 'yes'
            ),
            'file' => array(
                'field_type' => 'file',
                'field_label' => Lang::get('placecompany.board::lang.File'),
                'field_name' => '',
                'class' => 'board-attr-file',
                'meta_key' => '',
                'permission' => '',
                'roles' => array(),
                'description' => '',
                'show_document' => '',
                'close_button' => 'yes'
            ),
            'wp_editor' => array(
                'field_type' => 'wp_editor',
                'field_label' => Lang::get('placecompany.board::lang.WP Editor'),
                'field_name' => '',
                'class' => 'board-attr-wp-editor',
                'meta_key' => '',
                'permission' => '',
                'roles' => array(),
                'default_value' => '',
                'description' => '',
                'show_document' => '',
                'close_button' => 'yes'
            )
        );
    }

    /**
     * 게시판 아이디값을 입력받는다.
     * @param integer $board
     */
    public function setBoardID($board){
        if(is_int($board)){

        }
        else{
            $this->board = $board;
            $this->setFields($board->meta->skin_fields);
        }
    }

    /**
     * 필드 정보를 받는다.
     * @param array|string $skin_fields
     */
    public function setFields($skin_fields){
        if(is_array($skin_fields)){
            $this->skin_fields = $skin_fields;
        }
        else{
            $this->skin_fields = json_decode($skin_fields, true);
        }

        if($this->skin_fields){
            foreach($this->skin_fields as $key=>$item){
                if(!(isset($item['meta_key']) && $item['meta_key'])){
                    $this->skin_fields[$key]['meta_key'] = $key;
                }
            }
        }
    }

    /**
     * 저장되지 않은 KBoard 기본 필드를 반환한다.
     * @return array
     */
    public function getDefaultFields(){
        $default_fields = $this->default_fields;
        Event::fire('placecompany.board.admin_default_fields', [&$default_fields, $this->board]);

        foreach($default_fields as $key=>$value){
            if($this->skin_fields){
                if(isset($this->skin_fields[$key])){
                    unset($default_fields[$key]);
                }
            }
            else{
                if(!isset($value['board_extends'])){
                    unset($default_fields[$key]);
                }
            }
        }

        return $default_fields;
    }

    /**
     * 확장 필드를 반환한다.
     * @return array
     */
    public function getExtensionFields(){
        Event::fire('placecompany.board.admin_extends_fields', [&$this->extends_fields, $this->board]);
        return $this->extends_fields;
    }

    /**
     * 스킨 필드를 반환한다.
     * @return array
     */
    public function getSkinFields(){
        if($this->skin_fields){
            $fields = $this->skin_fields;
        }
        else{
            $fields = $this->default_fields;
            foreach($fields as $key=>$value){
                if(isset($value['board_extends'])){
                    unset($fields[$key]);
                }
            }
        }

        Event::fire('placecompany.board.skin_fields', [&$fields, $this->board]);
        return $fields;
    }

    /**
     * KBoard 기본 필드인지 확인한다.
     * @param string $fields_type
     * @return string
     */
    public function isDefaultFields($fields_type){
        $default_fields = $this->default_fields;
        Event::fire('placecompany.board.admin_default_fields', [&$default_fields, $this->board]);

        if(isset($default_fields[$fields_type])){
            return 'default';
        }
        return 'extends';
    }

    /**
     * 필드의 레이아웃을 반환한다.
     * @param array $field
     * @param string $content
     * @param string $boardBuilder
     * @return string
     */
    public function getTemplate($field, $content='', $boardBuilder=''){
        $template = '';
        $permission = (isset($field['permission']) && $field['permission']) ? $field['permission'] : '';
        $roles = (isset($field['roles']) && $field['roles']) ? $field['roles'] : '';
        $meta_key = (isset($field['meta_key']) && $field['meta_key']) ? $field['meta_key'] : '';

        if($this->isUseFields($permission, $roles) && $meta_key){
            if(!$content){
                $content = new BoardContentManager();
            }

            Event::fire('placecompany.board.get_template_field_data', [&$field, $content, $this->board]);

            $field_name = (isset($field['field_name']) && $field['field_name']) ? $field['field_name'] : $this->getFieldLabel($field);
            $required = (isset($field['required']) && $field['required']) ? 'required' : '';
            $placeholder = (isset($field['placeholder']) && $field['placeholder']) ? $field['placeholder'] : '';
            $wordpress_search = '';
            $default_value = (isset($field['default_value']) && $field['default_value']) ? $field['default_value'] : '';
            $row = false;

            $default_value_list = array();
            if(isset($field['row']) && $field['row']){
                foreach($field['row'] as $item){
                    if(isset($item['label']) && $item['label']){
                        $row = true;

                        if(isset($item['default_value']) && $item['default_value']){
                            $default_value_list[] = $item['label'];
                        }
                    }
                }
            }

            if($default_value_list){
                $default_value = $default_value_list;
            }

            if($field['field_type'] == 'search'){
                if($content->search){
                    $wordpress_search = $content->search;
                }
                else if(isset($field['default_value']) && $field['default_value']){
                    $wordpress_search = $field['default_value'];
                }
            }

            // 게시글 수정시에는 기본값을 제거하고 저장된 상태를 표시하도록 한다.
            if($content->id){
                if(is_array($default_value)){
                    $default_value = array();
                }
                else{
                    $default_value = '';
                }
            }

            $url = new BoardUrlManager();
            $url->setBoard($this->board);

            $skin = BoardSkinManager::getInstance();

            if(!$boardBuilder){
                $boardBuilder = new BoardBuilderManager($this->board->id);
                $boardBuilder->setSkin($this->board->skin);
                $boardBuilder->setRpp($this->board->page_rpp);
                $boardBuilder->board = $this->board;
            }

            $vars = array(
                'field' => $field,
                'meta_key' => $meta_key,
                'field_name' => $field_name,
                'required' => $required,
                'placeholder' => $placeholder,
                'row' => $row,
                'wordpress_search' => $wordpress_search,
                'default_value' => $default_value,
                'board' => $this->board,
                'content' => $content,
                'fields' => $this,
                'url' => $url,
                'skin' => $skin,
                'skin_path' => $skin->url($this->board->skin),
                'skin_dir' => $skin->dir($this->board->skin),
                'boardBuilder' => $boardBuilder,
                'boardContent' => Helpers::board_content_editor(array(
                    'board' => $this->board,
                    'content' => $content,
                    'required' => $required,
                    'placeholder' => $placeholder,
                    'editor_height' => '400',
                )),
                'board_user_ip' => Helpers::board_user_ip(),
                'board_captcha' => Helpers::board_captcha(),
                'board_recaptcha_site_key' => Helpers::board_recaptcha_site_key(),
                'board_use_recaptcha' => Helpers::board_use_recaptcha()
            );

            ob_start();

            Event::fire('placecompany.board.skin_field_before', $field, $content, $this->board);
            Event::fire("placecompany.board.skin_field_before_{$meta_key}", $field, $content, $this->board);

            if($skin->fileExists($this->board->skin, "editor-field-{$meta_key}.htm")){
                $field_html = $skin->load($this->board->skin, "editor-field-{$meta_key}.htm", $vars);
            }
            else{
                $field_html = $skin->load($this->board->skin, 'editor-fields.htm', $vars);
            }

            Event::fire('placecompany.board.get_template_field_html', [&$field_html, $field, $content, $this->board]);
            echo $field_html;

            Event::fire("placecompany.board.skin_field_after_{$meta_key}", $field, $content, $this->board);
            Event::fire('placecompany.board.skin_field_after', $field, $content, $this->board);

            $template = ob_get_clean();
        }

        return $template;
    }

    /**
     * 번역된 필드의 레이블을 반환한다.
     * @param array $field
     * @return string
     */
    public function getFieldLabel($field){
        $field_type = $field['field_type'];

        Event::fire('placecompany.board.admin_default_fields', [&$this->default_fields, $this->board]);
        $fields = $this->default_fields;
        if(isset($fields[$field_type])){
            return $fields[$field_type]['field_label'];
        }

        Event::fire('placecompany.board.admin_extends_fields', [&$this->extends_fields, $this->board]);
        $fields = $this->extends_fields;
        if(isset($fields[$field_type])){
            return $fields[$field_type]['field_label'];
        }

        return $field['field_label'];
    }

    /**
     * 저장된 값이 있는지 체크한다.
     * @param array $row
     * @return boolean
     */
    public function valueExists($row){
        foreach($row as $key=>$item){
            if(isset($item['label']) && $item['label']){
                return true;
            }
        }
        return false;
    }

    /**
     * 기본값이나 저장된 값이 있는지 확인한다.
     * @param array|string $value
     * @param string $label
     * @return boolean
     */
    public function isSavedOption($value, $label){
        if(is_array($value) && in_array($label, $value)){
            return true;
        }
        else if($value == $label){
            return true;
        }
        return false;
    }

    /**
     * 입력 필드 이름을 반환한다.
     * @param string $name
     * @return string
     */
    public function getOptionFieldName($name){
        $key = strtolower( $name );
        $name = preg_replace( '/[^a-z0-9_\-]/', '', $key );
        return BoardContentManager::$SKIN_OPTION_PREFIX . $name;
    }

    /**
     * 입력 필드를 사용할 수 있는 권한인지 확인한다.
     * @param string $permission
     * @param string roles
     * @return boolean
     */
    public function isUseFields($permission, $roles){
        $board = $this->board;
        $is_admin = \App::runningInBackend();
        if($is_admin){
            return true;
        }

        switch($permission){
            case 'all': return true;
            case 'author': return BackendAuth::check() ? true : false;
            case 'roles':
                if(BackendAuth::check()){
                    if(array_intersect($roles, Helpers::board_current_user_roles())){
                        return true;
                    }
                }
                return false;
            default: return true;
        }
    }

    /**
     * 게시글 본문 페이지에 표시할 옵션값 태그를 반환한다.
     * @param BoardContentManager $content
     * @return string
     */
    public function getDocumentValuesHTML($content){
        $option_value_list = array();

        $board = $this->board;
        $skin_fields = $board->fields()->getSkinFields();

        foreach($skin_fields as $key=>$field){
            Event::fire('placecompany.board.document_add_option_value_field_data', [&$field, $content, $board]);

            $meta_key = (isset($field['meta_key'])&&$field['meta_key']) ? $field['meta_key'] : $key;
            $field_type = (isset($field['field_type'])&&$field['field_type']) ? $field['field_type'] : '';

            if($field_type == 'file'){
                $option_value = isset($content->attach->{$meta_key}) ? $content->attach->{$meta_key} : array();
            }
            else{
                $option_value = $content->option->{$meta_key};
            }

            if(isset($field['show_document']) && $field['show_document'] && $option_value){
                if(is_array($option_value) && $field_type != 'file'){
                    $separator = ', ';
                    Event::fire('placecompany.board.document_add_option_value_separator', [&$separator, $field, $content, $board]);
                    $option_value = implode($separator, $option_value);
                }

                if(!(isset($field['field_name']) && $field['field_name'])){
                    $field['field_name'] = $this->getFieldLabel($field);
                }

                $html = '<div class="board-document-add-option-value meta-key-' . htmlspecialchars($meta_key) . '"><span class="option-name">' . $field['field_name'] . '</span> : ';

                if($field_type == 'file'){
                    if($content->execute_action == 'insert'){
                        $download_button = $option_value[1];
                    }
                    else{
                        $url = new BoardUrlManager();
                        $download_button = "<button type=\"button\" class=\"board-button-action board-button-download\" onclick=\"window.location.href='{$url->getDownloadURLWithAttach($content->id, $meta_key)}'\" title=\"\">{$option_value[1]}</button>";
                    }
                    $html .= $download_button . '</div><hr>';
                }
                else{
                    $html .= nl2br($option_value) . '</div><hr>';
                }
                Event::fire('placecompany.board.document_add_option_value_field_html', [&$html, $field, $content, $board]);
                $option_value_list[$meta_key] = $html;
            }
        }

        if($option_value_list){
            return '<div class="board-document-add-option-value-wrap">' . implode('', $option_value_list) . '</div>';
        }
        return '';
    }

    /**
     * 게시글 본문 페이지에 표시할 옵션값을 반환한다.
     * @param BoardContentManager $content
     * @return array
     */
    public function getDocumentValues($content){
        $option_value_list = array();

        $board = $this->board;
        $skin_fields = $board->fields()->getSkinFields();

        foreach($skin_fields as $key=>$field){
            Event::fire('placecompany.board.document_add_option_value_field_data', [&$field, $content, $board]);

            $meta_key = (isset($field['meta_key']) && $field['meta_key']) ? $field['meta_key'] : $key;
            $option_value = $content->option->{$meta_key};

            if(isset($field['show_document']) && $field['show_document'] && $option_value){
                if(!(isset($field['field_name']) && $field['field_name'])){
                    $field['field_name'] = $this->getFieldLabel($field);
                }

                $option_value_list[$meta_key] = array('field'=>$field, 'value'=>$option_value);
            }
        }

        return $option_value_list;
    }

    /**
     * 게시글에 표시할 첨부파일을 반환한다.
     * @param BoardContentManager $content
     * @return object
     */
    public function getAttachmentList($content){
        $skin_fields = $this->getSkinFields();
        $attach_list = $content->attach;

        foreach($skin_fields as $key=>$field){
            $meta_key = (isset($field['meta_key']) && $field['meta_key']) ? $field['meta_key'] : $key;
            if(array_key_exists($meta_key, $attach_list)){
                unset($attach_list->$meta_key);
            }
        }

        return $attach_list ? $attach_list : new stdClass();
    }
}
?>
