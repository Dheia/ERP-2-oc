<?php namespace Placecompany\Board\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Placecompany\Board\Classes\BoardLatestViewManager;
use Placecompany\Board\Classes\BoardListManager;
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
class switchAreaBuilder extends FormWidgetBase
{
    /**
     * {@inheritDoc}
     */
    protected $defaultAlias = 'switchAreaBuilder';

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
        $this->vars['skin'] = BoardSkinManager::getInstance();
        $latestview = new BoardLatestViewManager();
        $latestview->initWithID($this->model->id);
        $this->vars['linked_board'] = $latestview->getLinkedBoard();
        $this->vars['board_list'] = new BoardListManager();
    }

    /**
     * {@inheritDoc}
     */
    public function loadAssets()
    {
        $this->addCss('/plugins/placecompany/board/assets/css/board-admin.css', 'board');
        $this->addCss('/plugins/placecompany/board/formwidgets/switchareabuilder/assets/css/switch.css', 'board');
    }

}
