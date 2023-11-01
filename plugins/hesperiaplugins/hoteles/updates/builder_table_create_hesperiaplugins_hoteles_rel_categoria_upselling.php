<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesRelCategoriaUpselling extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_rel_categoria_upselling', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('categoria_id');
            $table->integer('upselling_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_rel_categoria_upselling');
    }
}
