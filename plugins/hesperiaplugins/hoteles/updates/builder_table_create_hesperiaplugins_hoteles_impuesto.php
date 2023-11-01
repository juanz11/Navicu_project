<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesImpuesto extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_impuesto', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('impuesto');
            $table->decimal('valor', 10, 0);
            $table->integer('hotel_id');
            $table->integer('moneda_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_impuesto');
    }
}
