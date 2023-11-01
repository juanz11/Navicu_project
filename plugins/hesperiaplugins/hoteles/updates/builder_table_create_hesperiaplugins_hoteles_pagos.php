<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesPagos extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_pagos', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('referencia', 150);
            $table->string('pagable_type', 150);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('pagable_id');
            $table->integer('codigo');
            $table->text('comprobante_e')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_pagos');
    }
}
