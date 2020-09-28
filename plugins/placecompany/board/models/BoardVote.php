<?php namespace Placecompany\Board\Models;

use Model;

/**
 * Model
 */
class BoardVote extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'placecompany_board_vote';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $timestamps = true;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

//    /**
//     * Get the BoardSetting that owns the BoardContent.
//     */
//    public function BoardSetting()
//    {
//        return $this->belongsTo('BoardSetting', 'board_id');
//    }
//
//    /**
//     * The boardOptions that belong to the BoardContent.
//     */
//    public function BoardOptions()
//    {
//        return $this->hasMany('BoardOption', 'content_id');
//    }
}
