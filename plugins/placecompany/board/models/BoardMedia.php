<?php namespace Placecompany\Board\Models;

use Model;

/**
 * Model
 */
class BoardMedia extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'placecompany_board_media';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $timestamps = true;

    /**
     * The BoardMedia that has many the BoardMediaRelationships.
     */
    public function BoardMediaRelationships()
    {
        return $this->hasMany(BoardMediaRelationships::class, 'media_id');
    }

    /**
     * this is a recommended way to declare event handlers
     */
    public static function boot() {
        parent::boot();

        static::deleting(function($model) { // before delete() method call this
            $model->BoardMediaRelationships()->delete();
        });
    }
}
