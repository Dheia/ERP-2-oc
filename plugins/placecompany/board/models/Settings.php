<?php namespace Placecompany\Board\Models;

use Model;

/**
 * Builder settings model
 *
 * @package rainlab\builder
 * @author Alexey Bobkov, Samuel Georges
 *
 */
class Settings extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'placecompany_board_settings';

    public $settingsFields = 'fields.yaml';

    /**
     * Validation rules
     */
    public $rules = [
    ];
}
