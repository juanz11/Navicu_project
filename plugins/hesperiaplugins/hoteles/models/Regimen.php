<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;

/**
 * Model
 */
class Regimen extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
        'nombre'      => 'required',
        'descripcion' => 'required'
    ];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_regimen';

    /*public $belongsTo = [
      'hotel' => ['HesperiaPlugins\Hoteles\Models\Hotel', 'key' => 'hotel_id'],
    ];*/

    /*public $HasMany = [
      'preciosFechas' => ['HesperiaPlugins\Hoteles\Models\PrecioFecha'],
    ];*/

    /* */

    public $attachOne = [
      'icon'=> 'System\Models\File'
    ];
}
