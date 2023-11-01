<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesFechas extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_fechas', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->date('fecha')->nullable();
            $table->integer('disponible');
            $table->integer('habitacion_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_fechas');
    }
}
