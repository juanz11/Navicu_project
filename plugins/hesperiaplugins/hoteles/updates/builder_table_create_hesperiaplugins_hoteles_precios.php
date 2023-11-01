<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesPrecios extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_precios_fechas', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('ocupacion');
            $table->decimal("precio", 10, 0);
            $table->integer('moneda_id');
            $table->integer('regimen_id');
            $table->integer('fecha_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_precios_fechas');
    }
}
