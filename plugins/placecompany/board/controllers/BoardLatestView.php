<?php namespace Placecompany\Board\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Placecompany\Board\Classes\BoardSkinManager;

class BoardLatestView extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class
    ];

    /**
     * @var array `FormController` configuration.
     */
    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $bodyClass = 'compact-container';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Placecompany.Board', 'board', 'boardlatestview');
    }
}
