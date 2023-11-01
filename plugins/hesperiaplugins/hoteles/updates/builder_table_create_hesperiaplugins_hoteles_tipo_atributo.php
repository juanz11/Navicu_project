<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesTipoAtributo extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_tipo_atributo', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('nombre', 150);
            $table->text('descripcion');
            $table->string('codigo', 150);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_tipo_atributo');
    }
}
