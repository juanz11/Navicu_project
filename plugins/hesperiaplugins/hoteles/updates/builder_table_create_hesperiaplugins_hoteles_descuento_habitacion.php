<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesDescuentoHabitacion extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_descuento_habitacion', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('habitacion_id');
            $table->integer('descuento_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_descuento_habitacion');
    }
}
