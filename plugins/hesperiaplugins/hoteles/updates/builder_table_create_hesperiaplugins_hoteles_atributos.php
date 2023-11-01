<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesAtributos extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_atributos', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('nombre', 150);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_atributos');
    }
}
