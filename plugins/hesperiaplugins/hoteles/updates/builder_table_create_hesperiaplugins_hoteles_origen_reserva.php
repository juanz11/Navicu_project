<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesOrigenReserva extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_origen_reserva', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('origen', 250);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_origen_reserva');
    }
}
