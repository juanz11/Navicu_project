<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;

/**
 * Model
 */
class Atributo extends Model
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
    public $rules = ['nombre' => 'required'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_atributos';


    public $belongsTo = [
        'tipo_atributo' => ['HesperiaPlugins\Hoteles\Models\TipoAtributo', 'key' => 'tipo_atributo_id']
        //VARIABLE - RUTA DEL MODELO - KEY--> CLAVE-FORANEA EN MI TABLA
    ];

    public $attachOne = [
        'icon'=> 'System\Models\File'
      ];
}