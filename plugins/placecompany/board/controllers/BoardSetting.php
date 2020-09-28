<?php namespace Placecompany\Board\Controllers;

use Backend\Classes\Controller;
use Backend\Facades\BackendMenu;
use Illuminate\Support\Facades\Request;
use Placecompany\Board\Classes\BoardManager;
use Placecompany\Board\Classes\BoardTreeCategoryManager;

class BoardSetting extends Controller
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

        BackendMenu::setContext('Placecompany.Board', 'board', 'boardsetting');

        //Add CSS for some backend menus
        $this->loadAssets();
    }

    /**
     * Load Assets
     */
    public function loadAssets()
    {
        $this->addCss('/plugins/placecompany/board/assets/css/board-admin.css', 'board');
        $this->addJs('https://code.jquery.com/ui/1.12.1/jquery-ui.js', 'board');
        $this->addJs('/plugins/placecompany/board/assets/plugins/nested-sortable/jquery.mjs.nestedSortable.js', 'board');
        $this->addJs('/plugins/placecompany/board/controllers/boardsetting/app.js', 'board');
    }

    public function onTreeCategorySortable()
    {
        $tree_category_serialize = post('tree_category_serialize') ? json_decode(post('tree_category_serialize')) : '';
        $board_id = post('board_id');

        $board = new BoardManager($board_id);
        $category = new BoardTreeCategoryManager();
        $category->setBoardID($board_id);

        $sortable_category = array();

        foreach($tree_category_serialize as $item){
            if(isset($item->id) && $item->id){
                foreach($category->tree_category as $key=>$value){
                    if($item->id === $value['id']){
                        $value['parent_id'] = $item->parent_id;
                        $sortable_category[] = $value;
                    }
                }
            }
        }

        $board->meta->tree_category = json_encode($sortable_category);
        $category->setTreeCategory($sortable_category);
        $build_tree_category = $category->buildAdminTreeCategory();

        $table_body = $category->buildAdminTreeCategorySortableRow($build_tree_category);

        $this->vars['table_body'] = $table_body;
        return [
            'table_body' => $table_body
        ];
    }

    public function onBoardTreeCategoryUpdate()
    {
        $board_id = post('board_id');

        $tree_category = array();
        if(post('tree_category')){
            parse_str(post('tree_category'), $tree_category);
        }

        $board = new BoardManager($board_id);
        $category = new BoardTreeCategoryManager();
        $category->setBoardID($board_id);
        $board->meta->tree_category = json_encode($tree_category['tree_category']);
        $category->setTreeCategory($tree_category['tree_category']);
        $build_tree_category = $category->buildAdminTreeCategory();

        $table_body = $category->buildAdminTreeCategorySortableRow($build_tree_category);

        $this->vars['table_body'] = $table_body;
        return [
            'table_body' => $table_body
        ];
    }
}
