<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesImpuestables extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_impuestables', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('impuesto_id');
            $table->integer('impuestable_id');
            $table->string('impuestable_type', 250);
            $table->decimal('valor', 10, 0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_impuestables');
    }
}
