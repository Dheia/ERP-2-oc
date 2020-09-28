<?php namespace Placecompany\Board\Models;

use Illuminate\Database\Eloquent\Builder;
use Model;

/**
 * Model
 */
class BoardMeta extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'placecompany_board_meta';

    protected $primaryKey = ['board_id', 'key'];

    public $incrementing = false;

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    protected $fillable = ['board_id', 'key', 'value'];

    public $timestamps = false;

    protected function setKeysForSaveQuery(Builder $query)
    {
        return $query->where('board_id', $this->getAttribute('board_id'))
            ->where('key', $this->getAttribute('key'));
    }
}
