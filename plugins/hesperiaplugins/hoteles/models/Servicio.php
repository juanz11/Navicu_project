<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;

/**
 * Model
 */
class Servicio extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
        'titulo' => 'required',
        'slug'   => 'required',
        'informacion' => 'required',
        'banner' => 'required|image|mimes:jpeg'
    ];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_servicios';

    public function getRecomendados(){
      return $recomendados = Servicio::where('id', '<>', $this->id)->
      where('hotel_id', '=', $this->hotel_id)->get();
      //return $recomendados = Db::table($this->table)->where('id', '<>', $id)->get();
    }

    /* Relaciones */

    public $attachOne = [
      'banner'=> 'System\Models\File'
    ];

    public $belongsTo = [
     'hotel' => ['HesperiaPlugins\Hoteles\Models\Hotel', 'key' => 'hotel_id']
    ];

    public $attachMany = [
      'galeria'=> 'System\Models\File'
    ];
}
