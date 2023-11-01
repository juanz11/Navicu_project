<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;

/**
 * Model
 */
class Moneda extends Model
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
        'acronimo' => 'required',
        'moneda'   => 'required'
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_moneda';

    public function scopeIsActiva($query){
      return $query->where("ind_activo", 1)->orderBy("id", "ASC");
    }

    public function scopeDefecto($query){
      return $query->where("defecto", 1);
    }

    public function afterSave(){
        if ($this->defecto == 1) {
          Moneda::where('id', "!=", $this->id)
          ->update(['defecto' => 0]);
        }
    }
}
