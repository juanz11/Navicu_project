<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesHabitaciones extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_habitaciones', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('nombre', 150);
            $table->text('descripcion');
            $table->integer('capacidad');
            $table->string('status', 1);
            $table->integer('hotel_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_habitaciones');
    }
}
