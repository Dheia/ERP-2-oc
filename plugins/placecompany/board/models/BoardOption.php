<?php namespace Placecompany\Board\Models;

use Model;

/**
 * Model
 */
class BoardOption extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'placecompany_board_option';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $timestamps = false;

    /**
     * Get the BoardContent that owns the BoardOption.
     */
    public function BoardContent()
    {
        return $this->belongsTo(BoardContent::class, 'content_id');
    }
}
