<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesFechaCalendario extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_fecha_calendario', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->date('fecha');
            $table->integer('calendario_id');
            $table->integer('disponible');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_fecha_calendario');
    }
}
