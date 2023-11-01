<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesDescuentos extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_descuentos', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('porcentaje');
            $table->text('concepto');
            $table->integer('hotel_id');
            $table->integer('ind_activo')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_descuentos');
    }
}
