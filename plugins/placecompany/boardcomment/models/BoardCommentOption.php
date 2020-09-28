<?php namespace Placecompany\BoardComment\Models;

use Model;

/**
 * Model
 */
class BoardCommentOption extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'placecompany_board_comment_option';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The BoardComment that hasMany the BoardCommentOption.
     */
    public function BoardComment()
    {
        return $this->belongsTo(BoardComment::class, 'comment_id');
    }

}
