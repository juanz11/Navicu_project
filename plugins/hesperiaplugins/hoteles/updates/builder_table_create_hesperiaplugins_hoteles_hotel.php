<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotel extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_hotel', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('nombre', 250);
            $table->text('descripcion');
            $table->text('direccion');
            $table->string('codigo_postal', 15);
            $table->string('slug', 250);
            $table->string('latitud', 100);
            $table->string('longitud', 100);
            $table->text('telefonos')->nullable();
            $table->text('emails')->nullable();
            $table->text('atributos')->nullable();
            $table->text('emails_notificacion')->nullable();
            $table->text('informacion')->nullable();
            $table->text('url_video');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_hotel');
    }
}
