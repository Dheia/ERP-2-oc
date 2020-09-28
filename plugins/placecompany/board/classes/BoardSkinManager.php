<?php namespace Placecompany\Board\Classes;

use Backend\Facades\Backend;
use Backend\Facades\BackendAuth;
use Cms\Classes\Controller;
use Cms\Twig\Extension as CmsTwigExtension;
use Cms\Twig\Loader as TwigLoader;
use Placecompany\Board\Components\Board;
use Placecompany\Board\Models\BoardContent;
use Placecompany\Board\Models\BoardLatestView;
use Placecompany\Board\Models\BoardSetting;
use stdClass;
use System\Twig\Extension as SystemTwigExtension;
use Twig\Environment as TwigEnvironment;

/**
 * KBoard 워드프레스 게시판 스킨
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardSkinManager {

    private static $instance;
    private $active;
    private $list;
    private $latestview_list;
    private $merged_list;

    private function __construct(){
        $dir = plugins_path('/placecompany/board/skin');

        if($dh = @opendir($dir)){

            while(($name = readdir($dh)) !== false){
                if($name == '.' || $name == '..' || $name == 'readme.txt' || $name == '__MACOSX') continue;
                $skin = new stdClass();
                $skin->name = $name;
                $skin->dir = $dir . "/{$name}";
                $skin->url = url("plugins/placecompany/board/skin/{$name}");
                $this->list[$name] = $skin;
            }

            closedir($dh);
        }

        \Event::fire('placecompany.board.board_skin_list', [&$this->list]);
        \Event::fire('placecompany.board.board_skin_latestview_list', [&$this->list]);

        $this->latestview_list = $this->list;
        $this->merged_list = array_merge($this->list, $this->latestview_list);

        ksort($this->list);
        ksort($this->latestview_list);
        ksort($this->merged_list);
    }

    /**
     * 인스턴스를 반환한다.
     * @return BoardSkinManager
     */
    public static function getInstance(){
        if(!self::$instance) self::$instance = new BoardSkinManager();
        return self::$instance;
    }

    /**
     * 모든 스킨 리스트를 반환한다.
     * @return array
     */
    public function getList(){
        return $this->list ? $this->list : array();
    }

    /**
     * 최신글 모아보기 스킨 리스트를 반환한다.
     */
    public function getLatestviewList(){
        return $this->latestview_list ? $this->latestview_list : array();
    }

    /**
     * 모든 스킨과 최신글 모아보기 스킨의 합쳐진 리스트를 반환한다.
     * @return array
     */
    public function getMergedList(){
        return $this->merged_list ? $this->merged_list : array();
    }

    /**
     * 스킨 파일이 있는지 확인한다.
     * @param string $skin_name
     * @param string $file
     * @return boolean
     */
    public function fileExists($skin_name, $file){
        $file_exists = false;
        $current_file_path = '';

        if(isset($this->merged_list[$skin_name])){
            $is_ajax = false;
            if(defined('DOING_AJAX') && DOING_AJAX){
                if(file_exists("{$this->merged_list[$skin_name]->dir}/ajax-{$file}")){
                    $is_ajax = true;
                }
            }

            $is_admin = \App::runningInBackend();
            if($is_admin){
                if(file_exists("{$this->merged_list[$skin_name]->dir}/admin-{$file}")){
                    $is_admin = true;
                }
            }

            if($is_ajax){
                $current_file_path = "{$this->merged_list[$skin_name]->dir}/ajax-{$file}";
            }
            else if($is_admin){
                $current_file_path = "{$this->merged_list[$skin_name]->dir}/admin-{$file}";
            }
            else{
                $current_file_path = "{$this->merged_list[$skin_name]->dir}/{$file}";
            }
        }

        if($current_file_path && file_exists($current_file_path)){
            $file_exists = true;
        }
        return $file_exists;
    }

    /**
     * 스킨 레이아웃을 불러온다.
     * @param string $skin_name
     * @param string $file
     * @param array $vars
     * @return string
     */
    public function load($skin_name, $file, $vars=array()){
        $current_file_path = '';

        if(isset($this->merged_list[$skin_name])){

            $is_ajax = false;
            if(defined('DOING_AJAX') && DOING_AJAX){
                if(file_exists("{$this->merged_list[$skin_name]->dir}/ajax-{$file}")){
                    $is_ajax = true;
                }
            }

            $is_admin = \App::runningInBackend();
            if($is_admin){
                if(file_exists("{$this->merged_list[$skin_name]->dir}/admin-{$file}")){
                    $is_admin = true;
                }
            }

            if($is_ajax){
                $current_file_path = "{$this->merged_list[$skin_name]->dir}/ajax-{$file}";
            }
            else if($is_admin){
                $current_file_path = "{$this->merged_list[$skin_name]->dir}/admin-{$file}";
            }
            else{
                $current_file_path = "{$this->merged_list[$skin_name]->dir}/{$file}";
            }
        }

        $partialContent = '';
        if($current_file_path && file_exists($current_file_path)){
            $controller = Controller::getController();

            $template = $controller->getTwig()->loadTemplate($current_file_path);
            $partialContent = $template->render(array_merge($controller->vars, $vars));

        }
        else{
            echo sprintf('%s file does not exist.', $file);
        }
        return $partialContent;
    }

    /**
     * 스킨의 functions.php 파일을 불러온다.
     * @param string $skin_name
     */
    public function loadFunctions($skin_name){
        if(isset($this->merged_list[$skin_name]) && file_exists("{$this->merged_list[$skin_name]->dir}/functions.php")){
            include_once "{$this->merged_list[$skin_name]->dir}/functions.php";
        }
    }

    /**
     * 스킨 URL 주소를 반환한다.
     * @param string $skin_name
     * @param string $file
     * @return string
     */
    public function url($skin_name, $file=''){
        if(isset($this->merged_list[$skin_name])){
            return "{$this->merged_list[$skin_name]->url}" . ($file ? "/{$file}" : '');
        }
        return '';
    }

    /**
     * 스킨 DIR 경로를 반환한다.
     * @param string $skin_name
     * @param string $file
     * @return string
     */
    public function dir($skin_name, $file=''){
        if(isset($this->merged_list[$skin_name])){
            return "{$this->merged_list[$skin_name]->dir}" . ($file ? "/{$file}" : '');
        }
        return '';
    }

    /**
     * 사용 중인 스킨 리스트를 반환한다.
     * @return array
     */
    public function getActiveList(){
        $site_id = 1;
        $union_query = BoardLatestView::select('skin')->getQuery();
        $results = BoardSetting::select('skin')->union($union_query)->get();
        foreach($results as $row){
            $this->active[$site_id][] = $row->skin;
        }
        return (isset($this->active[$site_id]) && $this->active[$site_id]) ? $this->active[$site_id] : array();
    }

    /**
     * 스킨의 editor 폼에 필수 정보를 출력한다.
     * @param BoardContentManager $content
     * @param BoardManager $board
     */
    public function editorHeader(BoardContentManager $content, BoardManager $board){
        $header = array();
        $header['action'] = '<input type="hidden" name="action" value="board_editor_execute">';
        $header['mod'] = '<input type="hidden" name="mod" value="editor">';
        $header['id'] = sprintf('<input type="hidden" name="id" value="%d">', $content->id);
        $header['board_id'] = sprintf('<input type="hidden" name="board_id" value="%d">', $content->board_id);
        $header['parent_id'] = sprintf('<input type="hidden" name="parent_id" value="%d">', $content->parent_id);
        $header['user_id'] = sprintf('<input type="hidden" name="user_id" value="%d">', $content->user_id);
        $header['user_display'] = sprintf('<input type="hidden" name="user_display" value="%s">', $content->user_display);
        $header['user_id'] = sprintf('<input type="hidden" name="user_id" value="%d">', BackendAuth::getUser() ? BackendAuth::getUser()->id : '');

        foreach($header as $input){
            echo $input;
        }

    }

}
