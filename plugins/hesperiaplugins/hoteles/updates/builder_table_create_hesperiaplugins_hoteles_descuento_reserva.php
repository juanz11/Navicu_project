<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesDescuentoReserva extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_descuento_reserva', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('detalle_id');
            $table->integer('descuento_id');
            $table->integer('porcentaje');
            $table->string('concepto', 200);
        });
    }

    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_descuento_reserva');
    }
}
