<?php namespace Placecompany\Board\Classes;

use Backend\Facades\BackendAuth;
use Cms\Classes\Controller;
use Illuminate\Support\Facades\File;
use Placecompany\Board\Models\Settings;
use ToughDeveloper\ImageResizer\Classes\Image;

/**
 * User Extended by Shawn Clake
 * Class Helpers
 * User Extended is licensed under the MIT license.
 *
 * @author Shawn Clake <shawn.clake@gmail.com>
 * @link https://github.com/ShawnClake/UserExtended
 *
 * @license https://github.com/ShawnClake/UserExtended/blob/master/LICENSE MIT
 * @package Clake\UserExtended\Classes
 */
class Helpers
{

    /**
     * 배열에서 허가된 데이터만 남긴다.
     * @param array $array
     * @param array $whitelist
     * @return array
     */
    public static function board_array_filter($array, $whitelist){
        if(function_exists('array_intersect_key')){
            return array_intersect_key($array, array_flip($whitelist));
        }
        foreach($array as $key=>$value){
            if(!in_array($key, $whitelist)) unset($array[$key]);
        }
        return $array;
    }

    /**
     * sanitize key string.
     *
     * @param string $key
     * @return string $key Sanitized key.
     */
    public static function sanitize_key($key)
    {
        $key = strtolower($key);
        $key = preg_replace('/[^a-z0-9_\-]/', '', $key);

        return $key;
    }

    /**
     * category1 값을 반환한다.
     * @return string
     */
    public static function board_category1(){
        static $category1;
        if($category1 === null){
            $category1 = get('category1') ? filter_var(get('category1'), FILTER_SANITIZE_STRING):'';
        }
        \Event::fire('placecompany.board.board_category1', [&$category1]);
        return $category1;
    }


    /**
     * category2 값을 반환한다.
     * @return string
     */
    public static function board_category2(){
        static $category2;
        if($category2 === null){
            $category2 = get('category2') ? filter_var(get('category2'), FILTER_SANITIZE_STRING):'';
        }
        \Event::fire('placecompany.board.board_category2', [&$category2]);
        return $category2;
    }


    /**
     * board id 값을 반환한다.
     * @return string
     */
    public static function board_id(){
        static $id;
        if($id === null){
            $id = request('id')?intval(request('id')):'';
        }
        \Event::fire('placecompany.board.board_id', [&$id]);
        return $id;
    }

    /**
     * 현재 사용자의 역할을 반환한다.
     * @return array
     */
    public static function board_current_user_roles(){
        $roles = array();
        if(BackendAuth::check()){
            $user = BackendAuth::getUser();
            if($user->roles){
                $roles = (array) $user->roles;
            }
            else{
                $user = BackendAuth::getUser();
                if($user->roles){
                    $roles = (array) $user->roles;
                }
            }
        }
        \Event::fire('placecompany.board.current_user_roles', [&$roles]);
        return $roles;
    }

    /**
     * mod 값을 반환한다.
     * @param string $default
     * @return string
     */
    public static function board_mod($default=''){
        static $mod;
        if($mod === null){
            $mod = request('mod')?static::sanitize_key(request('mod')):'';
        }
        if(!in_array($mod, array('list', 'document', 'editor', 'remove', 'order', 'complete', 'history', 'sales'))){
            return $default;
        }
        \Event::fire('placecompany.board.board_mod', [&$mod]);
        return $mod;
    }

    /**
     * 배열 원소들을 정수형으로 변환한다.
     * @param array $array
     * @return array
     */
    public static function array2int($array){
        $array = array_map(
            function($value) {
                return (int)$value;
            },
            $array
        );
        return $array;
    }

    /**
     * pageid 값을 반환한다.
     * @return string
     */
    public static function board_pageid(){
        static $pageid;
        if($pageid === null){
            $pageid = get('pageid') ? intval(get('pageid')): 1;
        }
        \Event::fire('placecompany.board.board_pageid', [&$pageid]);
        return $pageid;
    }

    /**
     * compare 값을 반환한다.
     * @return string
     */
    public static function board_compare(){
        static $compare;
        if($compare === null){
            $compare = filter_var(request('compare'), FILTER_SANITIZE_STRING);
            if(!in_array($compare, array('=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE'))){
                $compare = 'LIKE';
            }
        }
        \Event::fire('placecompany.board.board_compare', [&$compare]);
        return $compare;
    }


    /**
     * start_date 값을 반환한다.
     * @return string
     */
    public static function board_start_date(){
        static $start_date;
        if($start_date === null){
            $start_date = filter_var(request('start_date'), FILTER_SANITIZE_STRING);
        }
        \Event::fire('placecompany.board.board_start_date', [&$start_date]);
        return $start_date;
    }

    /**
     * end_date 값을 반환한다.
     * @return string
     */
    public static function board_end_date(){
        static $end_date;
        if($end_date === null){
            $end_date = filter_var(request('end_date'), FILTER_SANITIZE_STRING);
        }
        \Event::fire('placecompany.board.board_end_date', [&$end_date]);
        return $end_date;
    }

    /**
     * board_option 값을 반환한다.
     * @return array
     */
    public static function board_search_option(){
        static $search_option;
        if($search_option === null){
            $search_option = (request('board_search_option')&&is_array(request('board_search_option'))) ?request('board_search_option') : array();
        }
        \Event::fire('placecompany.board.board_search_option', [&$search_option]);
        return $search_option;
    }

    /**
     * keyword 값을 반환한다.
     * @return string
     */
    public static function board_keyword(){
        static $keyword;
        if($keyword === null){
            $keyword = get('keyword') ? filter_var(get('keyword'), FILTER_SANITIZE_STRING) : '';
        }
        \Event::fire('placecompany.board.board_keyword', [&$keyword]);
        return $keyword;
    }

    /**
     * target 값을 반환한다.
     * @return string
     */
    public static function board_target(){
        static $target;
        if($target === null){
            $target = get('target') ? filter_var(get('target'), FILTER_SANITIZE_STRING) : '';
        }

        \Event::fire('placecompany.board.board_target', [&$target]);
        return $target;
    }


    /**
     * with_notice 값을 반환한다.
     * @return string
     */
    public static function board_with_notice(){
        static $with_notice;
        if($with_notice === null){
            $with_notice = (request('with_notice') && intval(request('with_notice'))) ? true : false;
        }
        \Event::fire('placecompany.board.board_with_notice', [&$with_notice]);
        return $with_notice;
    }

    /**
     * KBoardBuilder 클래스에서 실행된 게시판의 mod 값을 반환한다.
     * @param string $mod
     * @return string
     */
    public static function board_builder_mod($mod=''){
        static $builder_mod;
        if($builder_mod === null){
            $builder_mod = '';
        }
        if($mod){
            $builder_mod = $mod;
        }
        if(!in_array($builder_mod, array('list', 'document', 'editor', 'remove', 'order', 'complete', 'history', 'sales'))){
            $builder_mod = '';
        }
        \Event::fire('placecompany.board.board_builder_mod', [&$builder_mod]);
        return $builder_mod;
    }


    /**
     * parent_id 값을 반환한다.
     * @return string
     */
    public static function board_parent_id(){
        static $parent_id;
        if($parent_id === null){
            $parent_id = get('parent_id') ? intval(get('parent_id')) : '';
        }
        \Event::fire('placecompany.board.board_parent_id', [&$parent_id]);
        return $parent_id;
    }

    /**
     * 유튜브, 비메오 동영상 URL을 iframe 코드로 변환한다.
     * @param string $content
     * @return mixed
     */
    public static function board_video_url_to_iframe($content){
        // 유튜브
        $content = preg_replace("/\s*[a-zA-Z\/\/:\.]*youtube.com\/watch\?v=([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i", '<iframe src="https://www.youtube.com/embed/$1" width="560" height="315" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>', $content);

        // 비메오
        $content = preg_replace("/\s*[a-zA-Z\/\/:\.]*vimeo.com\/(\d+)/i", '<iframe src="https://player.vimeo.com/video/$1" width="560" height="315" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>', $content);

        return $content;
    }


    /**
     * 이미지 사이즈를 조절한다.
     * @param string $image_src
     * @param int $width
     * @param int $height
     * @return string
     */
    public static function board_resize($image_src, $width, $height){
        $upload_dir = storage_path();
        $basedir = str_replace(base_path(), '', $upload_dir);
        $dirname = dirname($image_src);
        $dirname = explode("/{$basedir}", $dirname);
        $resize_dir = end($dirname);

        $basename = basename($image_src);
        $fileinfo = pathinfo($basename);
        $resize_name = basename($image_src, '.'.$fileinfo['extension']) . "-{$width}x{$height}.{$fileinfo['extension']}";

        $new_image = $upload_dir . "{$resize_dir}/{$resize_name}";
        $new_image_src = $upload_dir . "{$resize_dir}/{$resize_name}";

        if(file_exists($new_image)){
            return $new_image_src;
        }

        $image_editor = new Image($upload_dir . "{$resize_dir}/{$basename}");
        if($image_editor){
            $image_editor->resize($width, $height, [ 'mode' => 'crop' ]);
            return $new_image_src;
        }
        else{
            return url($image_src);
        }
    }

    /**
     * 리사이즈 이미지를 지운다.
     * @param string $image_src
     */
    public static function board_delete_resize($image_src){
        if(file_exists($image_src)){
            $size = getimagesize($image_src);
            if($size){
                $fileinfo = pathinfo($image_src);
                $original_name = basename($image_src, '.'.$fileinfo['extension']).'-';
                $dir = dirname($image_src);
                if($dh = @opendir($dir)){
                    while(($file = readdir($dh)) !== false){
                        if($file == "." || $file == "..") continue;
                        if(strpos($file, $original_name) !== false){
                            @unlink($dir . '/' . $file);
                        }
                    }
                }
                closedir($dh);
            }
        }
    }

    /**
     * 허가된 첨부파일 확장자를 반환한다.
     * @param boolean $to_array
     * @return array
     */
    public static function board_allow_file_extensions($to_array=false){
        $file_extensions = Settings::get('board_allow_file_extensions', 'jpg, jpeg, gif, png, bmp, zip, 7z, hwp, ppt, xls, doc, txt, pdf, xlsx, pptx, docx, torrent, smi, mp4, mp3');
        $file_extensions = trim($file_extensions);

        if($to_array){
            $file_extensions = explode(',', $file_extensions);
            return array_map('trim', $file_extensions);
        }
        return $file_extensions;
    }

    /**
     * 업로드 가능한 파일 크기를 반환한다.
     */
    public static function board_limit_file_size(){
        // @todo 관리자에서 옵션을 가져오기 적용 필요
        return intval(Settings::get('board_limit_file_size', static::board_upload_max_size()));
    }

    /**
     * 서버에 설정된 최대 업로드 크기를 반환한다.
     * @link http://stackoverflow.com/questions/13076480/php-get-actual-maximum-upload-size
     * @return int
     */
    public static function board_upload_max_size(){
        static $max_size = -1;
        if($max_size < 0){
            $max_size = static::board_parse_size(ini_get('post_max_size'));
            $upload_max = static::board_parse_size(ini_get('upload_max_filesize'));
            if($upload_max > 0 && $upload_max < $max_size){
                $max_size = $upload_max;
            }
        }
        return $max_size;
    }

    /**
     * 바이트로 크기를 변환한다.
     * @link http://stackoverflow.com/questions/13076480/php-get-actual-maximum-upload-size
     * @return int
     */
    public static function board_parse_size($size){
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        if($unit){
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }
        else{
            return round($size);
        }
    }


    /**
     * 파일의 MIME Content-type을 반환한다.
     * @param string $file
     * @return string
     */
    public static function board_mime_type($file){
        /*
         * http://php.net/manual/en/function.mime-content-type.php#87856
         */
        $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            '7z' => 'application/x-7z-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',

            // etc
            'hwp' => 'application/hangul',
        );

        $mime_type = '';
        $temp = basename($file);
        $temp = explode('.', $temp);
        $temp = array_pop($temp);
        $ext = strtolower($temp);

        if(array_key_exists($ext, $mime_types)){
            $mime_type = $mime_types[$ext];
        }
        else if(function_exists('mime_content_type') && file_exists($file)){
            $mime_type = mime_content_type($file);
        }
        else if(function_exists('finfo_open') && file_exists($file)){
            $finfo = finfo_open(FILEINFO_MIME);
            $mime_type = finfo_file($finfo, $file);
            finfo_close($finfo);
        }

        if($mime_type){
            return $mime_type;
        }
        return 'application/octet-stream';
    }


    /**
     * 모든 html을 제거한다.
     * @param object $data
     * @return array|string
     */
    public static function board_htmlclear($data){
        if(is_array($data)) return array_map('board_htmlclear', $data);
        $data = e($data);
        return htmlspecialchars($data);
    }

    /**
     * 사용자 IP 주소를 반환한다.
     * @return string
     */
    public static function board_user_ip(){
        static $ip;
        if($ip === null){
            if(!empty($_SERVER['HTTP_CLIENT_IP'])){
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            }
            else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }
            else{
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        }
        \Event::fire('placecompany.board.board_user_ip', [&$ip]);
        return $ip;
    }

    /**
     * Captcha 이미지를 생성하고 이미지 주소를 반환한다.
     * @return string
     */
    public static function board_captcha(){
        $captcha = new BoardCaptchaManager();
        return $captcha->createImage();
    }

    /**
     * 구글 reCAPTCHA의 Site key를 반환한다.
     * @return string
     */
    public static function board_recaptcha_site_key(){
        static $recaptcha_site_key;
        if($recaptcha_site_key === null){
            $recaptcha_site_key = Settings::get('board_recaptcha_site_key');

            $controller = Controller::getController();
        }
        return $recaptcha_site_key;
    }

    /**
     * 구글 reCAPTCHA의 Secret key를 반환한다.
     * @return string
     */
    public static function board_recaptcha_secret_key(){
        static $recaptcha_secret_key;
        if($recaptcha_secret_key === null){
            $recaptcha_secret_key = Settings::get('board_recaptcha_secret_key');
        }
        return $recaptcha_secret_key;
    }

    /**
     * 구글 reCAPTCHA 사용 여부를 체크한다.
     * @return boolean
     */
    public static function board_use_recaptcha(){
        static $use_recaptcha;
        if($use_recaptcha === null){
            $site_key = Settings::get('board_recaptcha_site_key');
            $secret_key = Settings::get('board_recaptcha_secret_key');

            if($site_key && $secret_key){
                $use_recaptcha = true;
            }
            else{
                $use_recaptcha = false;
            }
        }
        return $use_recaptcha;
    }


    /**
     * 게시글 본문 에디터 코드를 반환한다.
     * @param array $vars
     * @return string
     */
    public static function board_content_editor($vars=array()){
        $vars = array_merge(array(
            'board' => new BoardManager(),
            'content' => new BoardContentManager(),
            'required' => '',
            'placeholder' => '',
            'editor_height' => '400',
        ), $vars);

        \Event::fire('placecompany.board.board_content_editor_vars', [&$vars]);

        extract($vars, EXTR_SKIP);

        if($board->use_editor == 'ckeditor'){
            $editor = sprintf('<textarea id="board_content" class="ckeditor" name="board_content" style="height:%dpx;" placeholder="%s">%s</textarea>', $editor_height, e($placeholder), e($content->content));
        }
        else if($board->use_editor == 'snote'){ // summernote
            $editor = sprintf('<textarea id="board_content" class="summernote" name="board_content" style="height:%dpx;" placeholder="%s">%s</textarea>', $editor_height, e($placeholder), e($content->content));
        }
        else{
            $editor = sprintf('<textarea id="board_content" class="editor-textarea %s" name="board_content" placeholder="%s">%s</textarea>', e($required), e($placeholder), e($content->content));
        }

        \Event::fire('placecompany.board.board_content_editor', [&$editor, $vars]);
        return $editor;
    }

    /**
     * view_iframe 값을 반환한다.
     * @return string
     */
    public static function board_view_iframe(){
        static $view_iframe;
        if($view_iframe === null){
            $view_iframe = (request('view_iframe')&&intval(request('view_iframe')))?true:false;
        }
        \Event::fire('placecompany.board.board_view_iframe', [&$view_iframe]);
        return $view_iframe;
    }

    /**
     * media_group 값을 반환한다.
     * @return string
     */
    public static function board_media_group($reset=false){
        static $media_group;
        if($media_group === null || $reset){
            $media_group = uniqid();
        }
        \Event::fire('placecompany.board.board_media_group', [&$media_group]);
        return $media_group;
    }

    /**
     * 작성자 금지단어를 반환한다.
     * @param bool $to_array
     * @return array
     */
    public static function board_name_filter($to_array=false){
        $name_filter = Settings::get('board_name_filter', '관리자, 운영자, admin, administrator');

        if($to_array){
            $name_filter = explode(',', $name_filter);
            return array_map('trim', $name_filter);
        }
        return $name_filter;
    }

    /**
     * 본문/제목/댓글 금지단어를 반환한다.
     * @param bool $to_array
     * @return array
     */
    public static function board_content_filter($to_array=false){
        $content_filter = Settings::get('kboard_content_filter', '');

        if($to_array){
            $content_filter = explode(',', $content_filter);
            return array_map('trim', $content_filter);
        }
        return $content_filter;
    }

    /**
     * 새글 알림 시간을 반환한다.
     * @return int
     */
    public static function board_new_document_notify_time(){
        return Settings::get('board_new_document_notify_time', '86400');
    }

    /**
     * 게시글 내용의 문단을 나눈다.
     * @param $content
     * @param string $builder
     * @return string|string[]|null
     */
    public static function board_content_paragraph_breaks($content, $builder=''){
        $content = nl2br($content);
        $content = preg_replace("/(<(|\/)(table|thead|tfoot|tbody|th|tr|td|ul|ol|li|h1|h2|h3|h4|h5|h6|hr|p).*>)(<br \/>)/", "\$1", $content);
        return $content;
    }
}
