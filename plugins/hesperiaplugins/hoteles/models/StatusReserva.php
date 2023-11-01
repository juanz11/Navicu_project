<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;

/**
 * Model
 */
class StatusReserva extends Model
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
        'status'      => 'required',
        'descripcion' => 'required'
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_status_reserva';
}