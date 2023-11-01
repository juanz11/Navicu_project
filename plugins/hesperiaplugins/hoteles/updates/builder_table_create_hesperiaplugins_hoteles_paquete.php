<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesPaquete extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_paquete', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('titulo', 150);
            $table->text('descripcion');
            $table->integer('min_noches');
            $table->integer('max_noches');
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
            $table->decimal('porcentaje', 10, 2);
            $table->integer('hotel_id');
            $table->integer('ind_activo');
            $table->string('slug', 250);
        });
    }

    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_paquete');
    }
}
