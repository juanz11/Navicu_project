<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesPaqueteHabitacion extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_paquete_habitacion', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('paquete_id');
            $table->integer('habitacion_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_paquete_habitacion');
    }
}
