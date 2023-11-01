<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;

/**
 * Model
 */
class Observacion extends Model
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
    public $table = 'hesperiaplugins_hoteles_observacion';

    protected $fillable = [
        'descripcion',
        'usable_id',
        'usable_type',
        'observable_id',
        'observable_type'
    ];

    /* relaciones */

    public $morphTo = [
        'observable' => [],
        'usable' =>[]
    ];


}
