<?php namespace Placecompany\Board\Controllers;

use Backend\Facades\BackendAuth;
use Cms\Classes\CmsException;
use Cms\Classes\Controller;
use Illuminate\Support\Facades\Lang;
use October\Rain\Exception\AjaxException;
use Placecompany\Board\Classes\BoardContentManager;
use Placecompany\Board\Classes\BoardContentMediaManager;
use Placecompany\Board\Classes\BoardManager;
use Placecompany\Board\Classes\Helpers;
use Placecompany\Board\Classes\SecurityHelpers;

/**
 * KBoard 템플릿 페이지 설정
 * @link www.cosmosfarm.com
 * @copyright Copyright 2019 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class BoardTemplateController extends Controller {

    public function __construct($theme = null)
    {
        view()->addNamespace('placecompany.board', plugins_path('placecompany/board/template'));
        parent::__construct($theme);
    }

    /**
     * 이미지 추가하기 팝업창 화면을 출력한다.
     */
    public function media(){
        $media = new BoardContentMediaManager();
        $media->truncate();

        $media->board_id = intval(get('board_id') ?: '');
        $media->content_id = intval(get('content_id') ?: '');
        $media->media_group = get('media_group') ? SecurityHelpers::board_htmlclear(get('media_group')) : '';

        $vars = [
            'media' => $media,
            'board' => new BoardManager($media->board_id)
        ];

        return view('placecompany.board::view.media', $vars);
    }

    /**
     * 이미지 추가하기 팝업창 화면을 출력한다.
     * @throws string|AjaxException
     */
    public function documentPrint(){
        try {
            $id = Helpers::board_id();

            $content = new BoardContentManager();
            $content->initWithID($id);

            if(!$content->id){
                throw new AjaxException([
                    'error' => Lang::get('placecompany.board::lang.You do not have permission.'),
                    'questionsNeeded' => 2
                ]);
            }

            $board = $content->getBoard();

            if(!$content->isReader()){
                if($board->permission_read != 'all' && !BackendAuth::check()){
                    throw new AjaxException([
                        'error' => Lang::get('You do not have permission.'),
                        'questionsNeeded' => 2
                    ]);
                }
                else if($content->secret){
                    if(!$content->isConfirm()){
                        if($content->parent_id){
                            $parent = new BoardContentManager();
                            $parent->initWithID($content->getTopContentID());
                            if(!$board->isReader($parent->user_id, $content->secret) && !$parent->isConfirm()){
                                throw new AjaxException([
                                    'error' => Lang::get('You do not have permission.'),
                                    'questionsNeeded' => 2
                                ]);
                            }
                        }
                        else{
                            throw new AjaxException([
                                'error' => Lang::get('You do not have permission.'),
                                'questionsNeeded' => 2
                            ]);
                        }
                    }
                }
                else{
                    throw new AjaxException([
                        'error' => Lang::get('You do not have permission.'),
                        'questionsNeeded' => 2
                    ]);
                }
            }

            $vars = [
                'content' => $content,
                'board' => $board
            ];

            $current_file_path = plugins_path('placecompany/board') . '/template/document_print.php';
            $controller = Controller::getController();

            $template = $controller->getTwig()->loadTemplate($current_file_path);
            return $template->render(array_merge($controller->vars, $vars));

        } catch (\Exception $e) {
            return $e;
        }
    }
}
