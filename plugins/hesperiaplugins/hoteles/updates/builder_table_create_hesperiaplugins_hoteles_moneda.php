<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesMoneda extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_moneda', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('acronimo');
            $table->string('moneda');
            $table->string('ind_activo', 1);
        });
    }

    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_moneda');
    }
}
