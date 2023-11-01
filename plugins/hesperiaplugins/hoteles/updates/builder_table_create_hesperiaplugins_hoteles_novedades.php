<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesNovedades extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_novedades', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('titulo', 200);
            $table->text('url');
            $table->string('subtitulo', 200)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_novedades');
    }
}
