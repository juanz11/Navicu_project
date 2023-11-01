<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesStatusReserva extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_status_reserva', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('status', 100);
            $table->text('descripcion');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_status_reserva');
    }
}
