<?php namespace Placecompany\Board\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Placecompany\Board\Classes\BoardManager;
use Placecompany\Board\Classes\BoardSkinManager;
use RainLab\Builder\Classes\ControlLibrary;
use ApplicationException;
use Input;
use Lang;

/**
 * Form builder widget.
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 */
class InputFieldBuilder extends FormWidgetBase
{
    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'InputFieldBuilder';

    /**
     * {@inheritDoc}
     */
    public function init()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('body');
    }

    /**
     * Prepares the list data
     */
    public function prepareVars()
    {
        $this->vars['model'] = $this->model;
        $this->vars['items'] = $this->model->menus;

        $board_id = $this->model->id;
        $board = new BoardManager($board_id);
        $meta = $board->meta;
        $skin = BoardSkinManager::getInstance();
//        if(defined('KBOARD_COMMNETS_VERSION')){
//            include_once WP_CONTENT_DIR.'/plugins/board-comments/class/KBCommentSkin.class.php';
//            $comment_skin = KBCommentSkin::getInstance();
//        }

        $this->vars['board'] = $board;
        $this->vars['meta'] = $meta;
        $this->vars['skin'] = $skin;
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
    }

}
