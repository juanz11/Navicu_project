<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesRegimen extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_regimen', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('nombre', 150);
            $table->text('descripcion');
            $table->string('status', 1);
        });
    }

    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_regimen');
    }
}
