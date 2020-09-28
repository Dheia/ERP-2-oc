<?php namespace Placecompany\BoardComment\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Model;

/**
 * Model
 */
class BoardComment extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'placecompany_board_comment';

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

    /**
     * The BoardComment that hasMany the BoardCommentOption.
     */
    public function BoardCommentOptions()
    {
        return $this->hasMany(BoardCommentOption::class, 'comment_id');
    }
}
