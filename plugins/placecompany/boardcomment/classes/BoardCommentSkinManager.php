<?php namespace Placecompany\BoardComment\Classes;

use Backend\Facades\BackendAuth;
use Cms\Classes\Controller;
use Placecompany\Board\Models\BoardMeta;
use stdClass;

/**
 * KBoard 댓글 스킨
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardCommentSkinManager
{
    static private $instance;
    private $active;
    private $list;

    private function __construct(){
        $dir = plugins_path('/placecompany/boardcomment/skin');
        if($dh = @opendir($dir)){
            while(($name = readdir($dh)) !== false){
                if($name == '.' || $name == '..' || $name == 'readme.txt' || $name == '__MACOSX') continue;
                $skin = new stdClass();
                $skin->name = $name;
                $skin->dir = $dir . "/{$name}";
                $skin->url = url("plugins/placecompany/boardcomment/skin/{$name}");
                $this->list[$name] = $skin;
            }
            closedir($dh);
        }

        \Event::fire('placecompany.board.board_comments_skin_list', [&$this->list]);

        ksort($this->list);
    }

    /**
     * 인스턴스를 반환한다.
     * @return BoardCommentSkinManager
     */
    static public function getInstance(){
        if(!self::$instance) self::$instance = new BoardCommentSkinManager();
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
     * 스킨 레이아웃을 불러온다.
     * @param string $skin_name
     * @param string $file
     * @param array $vars
     * @return string
     */
    public function load($skin_name, $file, $vars=array()){
        $current_file_path = '';
        if(isset($this->list[$skin_name])){
            extract($vars, EXTR_SKIP);

            $is_admin = \App::runningInBackend();
            if($is_admin){
                if(file_exists("{$this->list[$skin_name]->dir}/admin-{$file}")){
                    $is_admin = true;
                }
            }

            if($is_admin){
                $current_file_path = "{$this->list[$skin_name]->dir}/admin-{$file}";
            }
            else{
                $current_file_path = "{$this->list[$skin_name]->dir}/{$file}";
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
        if(isset($this->list[$skin_name]) && file_exists("{$this->list[$skin_name]->dir}/functions.php")){
            include_once "{$this->list[$skin_name]->dir}/functions.php";
        }
    }

    /**
     * 스킨 URL 주소를 반환한다.
     * @param string $skin_name
     * @param string $file
     * @return string
     */
    public function url($skin_name, $file=''){
        if(isset($this->list[$skin_name])){
            return "{$this->list[$skin_name]->url}" . ($file ? "/{$file}" : '');
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
        if(isset($this->list[$skin_name])){
            return "{$this->list[$skin_name]->dir}" . ($file ? "/{$file}" : '');
        }
        return '';
    }

    /**
     * 사용 중인 스킨 리스트를 반환한다.
     * @return array
     */
    public function getActiveList(){
        if($this->active){
            return $this->active;
        }
        $results = BoardMeta::select('value')->where('key', 'comment_skin')->get();
        foreach($results as $row){
            if(!empty($row->value)){
                $this->active[] = $row->value;
            }
        }
        return $this->active ? $this->active : array();
    }
}
