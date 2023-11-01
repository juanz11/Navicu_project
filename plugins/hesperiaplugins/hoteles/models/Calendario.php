<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;

/**
 * Model
 */
class Calendario extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_calendario';

    public $morphTo = [
        'calendarizable' => []
    ];

    public $hasMany = [
     'fechas' => ['HesperiaPlugins\Hoteles\Models\FechaCalendario', 'key' => 'calendario_id']
     //VARIABLE - RUTA DEL MODELO - KEY--> CLAVE-FORANEA EN MI TABLA
   ];
}
