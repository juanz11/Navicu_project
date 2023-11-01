<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;

/**
 * Model
 */
class Lead extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_reservas_lead';
}
