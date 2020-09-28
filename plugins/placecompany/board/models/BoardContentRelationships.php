<?php namespace Placecompany\Board\Models;

use Model;

/**
 * Model
 */
class BoardContentRelationships extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'placecompany_board_content_relationships';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $timestamps = true;

}
