<?php namespace Placecompany\Board\Models;

use Model;
use Placecompany\Board\Classes\BoardLatestViewManager;
use Placecompany\Board\Classes\BoardSkinManager;

/**
 * Model
 */
class BoardLatestView extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'placecompany_board_latestview';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $timestamps = true;


    public function getSkinOptions()
    {
        $result = [];
        $boardSkin = BoardSkinManager::getInstance();
        foreach ($boardSkin->getList() as $item) {
            $result[$item->name] = $item->name;
        }

        return $result;
    }

    /**
     * The BoardLatestViewLink that belong to the BoardLatestView.
     */
    public function BoardLatestViewLink()
    {
        return $this->hasOne(BoardLatestViewLink::class, 'latestview_id');
    }

    /**
     * this is a recommended way to declare event handlers
     */
    public static function boot() {
        parent::boot();

        static::deleting(function($model) { // before delete() method call this
            $model->BoardLatestViewLink()->delete();
        });
    }


    public function afterSave()
    {
        try {
            \Db::beginTransaction();

            $post = post();

            $latestview_link = $post['latestview_link'];
            $latestview_unlink = $post['latestview_unlink'];

            $latestview = new BoardLatestViewManager();
            if($this->id) $latestview->initWithID($this->id);
            else $latestview->create();

            $latestview_link = explode(',', $latestview_link);
            if(is_array($latestview_link)){
                foreach($latestview_link as $key=>$value){
                    $value = intval($value);
                    if($value) $latestview->pushBoard($value);
                }
            }

            $latestview_unlink = explode(',', $latestview_unlink);
            if(is_array($latestview_unlink)){
                foreach($latestview_unlink as $key=>$value){
                    $value = intval($value);
                    if($value) $latestview->popBoard($value);
                }
            }

        } catch (\Exception $ex) {
            \Db::rollBack();

            if (\Request::ajax()) throw $ex;
            else \Flash::error($ex->getMessage());
        }

        \Db::commit();
    }
}
