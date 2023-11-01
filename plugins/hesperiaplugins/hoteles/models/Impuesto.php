<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;

/**
 * Model
 */
class Impuesto extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /*
     * Validation
     */
    public $rules = [
        'impuesto' => 'required',
        'valor'    => 'required|numeric',
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_impuesto';

    public $belongsTo = [
     'moneda' => ['HesperiaPlugins\Hoteles\Models\Moneda', 'key' => 'moneda_id'],
     'hotel' => ['HesperiaPlugins\Hoteles\Models\Hotel', 'key' => 'hotel_id']
     //VARIABLE - RUTA DEL MODELO - KEY--> CLAVE-FORANEA EN MI TABLA
    ];

    public $morphedByMany = [
        'Compra'  => ['HesperiaPlugins\Hoteles\Models\Compra', 'name' => 'impuestable']
    ];
}
