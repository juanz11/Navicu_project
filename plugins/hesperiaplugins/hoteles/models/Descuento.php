<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;
use Carbon\Carbon;
/**
 * Model
 */
class Descuento extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
        'porcentaje' => 'required|numeric',
        'concepto' => 'required',
        'fecha_desde' => 'required',
        'fecha_hasta' => 'required|after:fecha_desde',
        'codigo_promocional' => 'max:10',
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_descuentos';

    public $belongsTo = [
     'hotel' => ['HesperiaPlugins\Hoteles\Models\Hotel', 'key' => 'hotel_id']
     //VARIABLE - RUTA DEL MODELO - KEY--> CLAVE-FORANEA EN MI TABLA
    ];

    public $hasMany = [
        'desc_habs' => ['HesperiaPlugins\Hoteles\Models\DescuentoHabitacion', 'key' => 'descuento_id'],
    ];

    public function scopeIsVigente($query, $propiedades){
      $begin = new \DateTime($propiedades["checkin"]);
      $end = new \DateTime($propiedades["checkout"]);
      $query = $query->where('fecha_desde', '<=', $begin->format("Y-m-d"))
      ->where('fecha_hasta', '>=', $end->format("Y-m-d"))
      ->when($propiedades["cod_promo"]!="",
       function ($query) use ($propiedades){
        return $query->where('codigo_promocional', '=', $propiedades["cod_promo"]);
      },
      function ($query){
    
        return $query->where('codigo_promocional', "");
      }    
    );
    
      /* ->where('a.fecha_desde', '<=', $begin->format("Y-m-d"))
      ->where('a.fecha_hasta', '>=', $end->format("Y-m-d"))
      ->where('a.ind_activo', '=', 1);
      if (isset($propiedades["cod_promo"]) && $propiedades["cod_promo"]!="") {
        $descuentos->where('a.codigo_promocional', '=', $propiedades["cod_promo"])
        ->where("b.cantidad", ">", 0);

      }else{
        $descuentos->where('a.codigo_promocional', "");
      }*/

      return $query;
    }

    public function aplicaMinimoNoches($propiedades){
        //trace_log("minimo noches:".$this->minimo_noches." - noches:".$propiedades["numero_noches"]);
        if($propiedades["numero_noches"] >= $this->minimo_noches){
            return true;       
        }else{
            return false;
        }
    }

    public function aplicaNochesAntelacion($propiedades){
        $checkin = new Carbon($propiedades["checkin"]);
        $hoy = new Carbon();
       
        //trace_log("noches antelacion:".$this->noches_antelacion." - diferencia:".$hoy->diffInDays($checkin));
        
        if($hoy->diffInDays($checkin) >= $this->noches_antelacion){
            return true;
        }else{
            return false;
        }
    }
}
