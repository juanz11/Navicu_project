<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesImpuestoReserva extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_impuesto_reserva', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('impuesto_id');
            $table->integer('reserva_id');
            $table->decimal('valor', 10, 0);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_impuesto_reserva');
    }
}
