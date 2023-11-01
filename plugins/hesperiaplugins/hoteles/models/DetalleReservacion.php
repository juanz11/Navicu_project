<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;

/**
 * Model
 */
class DetalleReservacion extends Model
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

    protected $fillable = [
        'habitacion_id',
        'ocupacion',
        'paquete',
        'precio',
        'regimen_id',
        'reservacion_id',
        'huespedes',
        'info_adicional'
    ];

    protected $jsonable = ['info_adicional', 'huespedes'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_detalle_reservacion';


    //VARIABLE - RUTA DEL MODELO - KEY--> CLAVE-FORANEA EN MI TABLA
    protected $with = ['reservacion'];
    public $belongsTo = [
        'reservacion' => ['HesperiaPlugins\Hoteles\Models\Reservacion', 'key' => 'reservacion_id' ],
        'habitacion' => ['HesperiaPlugins\Hoteles\Models\Habitacion', 'key' => 'habitacion_id' ],
        'regimen' => ['HesperiaPlugins\Hoteles\Models\Regimen', 'key' => 'regimen_id' ]
    ];

    public $hasMany = [
       'descuentos' => ['HesperiaPlugins\Hoteles\Models\DescuentoReserva', 'key' => 'detalle_id']
     ];

     public $morphMany = [
         'upgrades' => ['HesperiaPlugins\Hoteles\Models\Upgrade', 'name' => 'upgradable']
     ];
    public function cambiarFormatoPrecio(){
      $valor = number_format($this->precio, 0 ,",", "." );
      return $valor;
    }

    public function getOcupacionOptions($value, $formData){
      $aux = explode("-", $value);
      $result = "$aux[0] Adultos - $aux[1] Niños";
      return [$value => $result];

    }

    public function getTextoOcupacion(){
      $arr = explode("-", $this->ocupacion);
      $result = "$arr[0] Adultos - $arr[1] Niños";
      return $result;
    }

    public function getOcupacionCodes(){
      $arr = explode("-", $this->ocupacion);
      $arrOcupacion = ["adultos" => $arr[0], "ninos" => $arr[1]];
      return $arrOcupacion;
    }
}
