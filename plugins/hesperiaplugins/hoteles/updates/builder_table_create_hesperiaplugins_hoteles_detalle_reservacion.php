<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesDetalleReservacion extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_detalle_reservacion', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('reservacion_id');
            $table->integer('habitacion_id')->nullable();
            $table->string('ocupacion', 255)->nullable();
            $table->integer('paquete_id')->nullable();
            $table->decimal('precio', 10, 0);
            $table->integer('regimen_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_detalle_reservacion');
    }
}
