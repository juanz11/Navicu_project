<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;

/**
 * Model
 */
class DescuentoReserva extends Model
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
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_descuento_reserva';

    protected $fillable = [
        'detalle_id',
        'descuento_id',
        'porcentaje',
    ];

    public $belongsTo = [
        'detalle' => ['HesperiaPlugins\Hoteles\Models\DetalleReservacion', 'key' => 'detalle_id' ],
    ];
}
