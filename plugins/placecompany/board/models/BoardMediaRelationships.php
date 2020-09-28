<?php namespace Placecompany\Board\Models;

use Model;

/**
 * Model
 */
class BoardMediaRelationships extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'placecompany_board_media_relationships';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $timestamps = true;

    /**
     * The BoardMediaRelationships that be longs to the BoardMedia.
     */
    public function BoardMedia()
    {
        return $this->belongsTo(BoardMedia::class, 'media_id');
    }
}
