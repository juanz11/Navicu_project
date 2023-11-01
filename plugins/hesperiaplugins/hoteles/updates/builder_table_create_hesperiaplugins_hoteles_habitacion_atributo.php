<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesHabitacionAtributo extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_habitacion_atributo', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('habitacion_id');
            $table->integer('atributo_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_habitacion_atributo');
    }
}
