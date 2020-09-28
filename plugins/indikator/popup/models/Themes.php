<?php namespace Indikator\Popup\Models;

use Model;
use File;

class Themes extends Model
{
    use \October\Rain\Database\Traits\Validation;

    protected $table = 'indikator_popup_themes';

    public $rules = [
        'name'      => ['required', 'unique:indikator_popup_themes'],
        'style'     => 'required|between:1,2|numeric',
        'animation' => 'required|between:1,6|numeric',
        'speed'     => 'required|numeric',
        'open'      => 'numeric',
        'status'    => 'required|between:1,2|numeric'
    ];

    public function afterSave()
    {
        File::put(base_path().'/plugins/indikator/popup/assets/css/theme-'.$this->id.'.css', $this->css);
    }
}
