<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesTipoPago extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_tipo_pago', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('nombre', 150);
            $table->text('descripcion');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_tipo_pago');
    }
}
