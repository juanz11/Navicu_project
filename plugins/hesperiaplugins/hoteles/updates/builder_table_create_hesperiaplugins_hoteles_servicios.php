<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesServicios extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_servicios', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('titulo', 250);
            $table->text('informacion');
            $table->integer('hotel_id');
            $table->string('slug', 150);
        });
    }

    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_servicios');
    }
}
