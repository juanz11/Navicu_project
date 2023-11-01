<?php namespace HesperiaPlugins\Hoteles\Models;

use Model;

/**
 * Model
 */
class Novedad extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
    ];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'hesperiaplugins_hoteles_novedades';

    /*relaciones*/

    /*public $belongsTo = [
     'hotel' => ['HesperiaPlugins\Hoteles\Models\Hotel', 'key' => 'hotel_id']
   ];*/

    public $attachOne = [
      'foto'=> 'System\Models\File'
    ];
}
