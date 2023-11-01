<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;
use Carbon\Carbon;
use DB;

/**
 * Model
 */
class Upgrade extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_upgrades';

    protected $fillable = [
        'precio',
        'upgradable_type',
        'upgradable_id',
        'upselling_id',
        'moneda_id',
        'cantidad',
        'fecha_disfrute',
        'porcentaje_markup',
        'ocupacion',
        
    ];

    public $belongsTo = [
     'upselling' => ['HesperiaPlugins\Hoteles\Models\Upselling', 'key' => 'upselling_id'],
     'moneda' => ['HesperiaPlugins\Hoteles\Models\Moneda', 'key' => 'moneda_id'],
    ];

    protected $with = ['upgradable', 'upselling'];
    public $morphTo = [
        'upgradable' => []
    ];

    public function getMontoFormateado(){
      return number_format($this->precio, 0 ,",", "." )." ".$this->moneda->acronimo;
    }

    public function getTituloXcantidad(){
      if ($this->cantidad > 1) {
        return $this->titulo." X".$this->cantidad;
      }
    }

    public function scopeBetween($query,$dates){
      if($dates != null){
        $begin = new Carbon($dates["desde"]);
        $end = new Carbon($dates["hasta"]);
        $query->whereBetween("created_at", [$begin->toDateString(), $end->toDateString()]);
      }
      return $query;
    }

    public function scopeUpgradableType($query,$modelos){
      if($modelos != null){
        $query->whereIn('upgradable_type', $modelos);
      }
      return $query;
    }

    public function scopeMoneda($query,$dates){
      if($dates != null){
        $query->where('moneda_id',$dates["moneda"]);
      } else{
        $query->where('moneda_id',1);
      }
      return $query;
    }

    public function getTextoOcupacion(){
      $arr = explode("-", $this->ocupacion);
      $result = "$arr[0] Adultos - $arr[1] Niños";
      return $result;
    }
}
