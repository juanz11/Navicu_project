<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;
use HesperiaPlugins\Hoteles\Models\DescuentoHabitacion as DH;
use Input;
use Db;

/**
 * Model
 */
class DescuentoHabitacion extends Model
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
    public $table = 'hesperiaplugins_hoteles_descuento_habitacion';

    public $belongsTo = [
        'descuento' => [
          'HesperiaPlugins\Hoteles\Models\Descuento',
          'key' => 'descuento_id',
          'conditions' => 'ind_activo = 1'
        ],
        'habitacion' =>['HesperiaPlugins\Hoteles\Models\Habitacion', 'key' => 'habitacion_id'],
        'detalle_hab' =>['HesperiaPlugins\Hoteles\Models\DetalleReservacion', 'key' => 'habitacion_id']

    ];

    public function getHabitacionOptions(){
        $data = Input::get();
        if (isset($data["Descuento"]["hotel"])) {
          $hotel = $data["Descuento"]["hotel"];

          $habitaciones = Db::table('hesperiaplugins_hoteles_habitaciones as a')
          ->where('hotel_id', '=', $hotel)->lists('a.nombre', 'a.id');

        }else if(isset($data["manage_id"])){
          $descuentoHab = DH::find($data["manage_id"]);
          $habitacion = $data["manage_id"];
          $hotel = $descuentoHab->habitacion->hotel->id;
          $habitaciones = Db::table('hesperiaplugins_hoteles_habitaciones as a')
          ->where("hotel_id", "=", $hotel)->lists('a.nombre', 'a.id');
        }
      //  var_dump($data);

       return $habitaciones;
      //return ['au' => 'Australia', 'ca' => 'Canada'];
    }

    public function getDescuentoDisponible($propiedades){
      $descuento = null;
      $flag = false; //BANDERA PARA VERIFICAR SI EL DESCUENTO APLICA EN UNO DE SUS TIPOS

      if($this->descuento != null){ //SI EL DESCUENTO ESTA VIGENTE 

        $descuento = $this->descuento->isVigente($propiedades)->where("id", $this->descuento_id)->first();
        //trace_log($descuento);
        if($descuento != null){
          //trace_log($descuento->minimo_noches."-".$descuento->concepto);
          if($descuento->minimo_noches > 0){
            if(!$descuento->aplicaMinimoNoches($propiedades)){
              return null;
            }
          }
          if($descuento->noches_antelacion > 0){
            if(!$descuento->aplicaNochesAntelacion($propiedades)){
              return null;
            }
          }
          //trace_log($descuento);
        }
        
      }
      
      return $descuento;
    }
   
}
