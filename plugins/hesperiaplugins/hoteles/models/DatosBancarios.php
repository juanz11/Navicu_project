<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;

/**
 * Model
 */
class DatosBancarios extends Model
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
        'hotel'        => 'required',
        'banco'        => 'required',
        'beneficiario' => 'required',
        'cuenta'       => 'required',
        'email'        => 'required|email'
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_datos_bancarios';

    //VARIABLE - RUTA DEL MODELO - KEY--> CLAVE-FORANEA EN MI TABLA
    public $belongsTo = [
        'hotel' => ['HesperiaPlugins\Hoteles\Models\Hotel', 'key' => 'hotel_id']
    ];
}