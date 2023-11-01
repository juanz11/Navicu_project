<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesReservacion extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_reservacion', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('usuario_id');
            $table->string('huesped', 200);
            $table->date('checkin');
            $table->date('checkout');
            $table->decimal('total', 10, 0);
            $table->string('identificacion', 50);
            $table->string('contacto', 50);
            $table->text('comentarios');
            $table->integer('status_id');
            $table->integer('moneda_id');
            $table->integer('hotel_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_reservacion');
    }
}
