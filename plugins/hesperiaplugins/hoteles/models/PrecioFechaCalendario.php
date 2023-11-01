<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;

/**
 * Model
 */
class PrecioFechaCalendario extends Model
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
    public $table = 'hesperiaplugins_hoteles_precio_fecha_calendario';

    public $belongsTo = [
      'fecha' => ['HesperiaPlugins\Hoteles\Models\FechaCalendario', 'key' => 'fecha_id'],
      'moneda' => ['HesperiaPlugins\Hoteles\Models\Moneda', 'key' => 'moneda_id']
    ];

    public function cambiarFormatoPrecio(){
      $valor = number_format($this->precio, 0 ,",", "." );
      return $valor;
    }
}
