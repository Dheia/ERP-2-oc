<?php namespace Placecompany\Board\Models;

use Illuminate\Database\Eloquent\Builder;
use Model;

/**
 * Model
 */
class BoardLatestViewLink extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'placecompany_board_latestview_link';

    protected $primaryKey = ['board_id', 'key'];

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $incrementing = false;
    public $timestamps = false;

    /**
     * Get the BoardLatestView that owns the BoardLatestViewLink.
     */
    public function BoardLatestView()
    {
        return $this->belongsTo(BoardLatestView::class, 'latestview_id');
    }

    protected function setKeysForSaveQuery(Builder $query)
    {
        return $query->where('latestview_id', $this->getAttribute('latestview_id'))
            ->where('board_id', $this->getAttribute('board_id'));
    }
}
