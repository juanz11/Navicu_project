<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesTaggables extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_taggables', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('tag_id');
            $table->integer('taggable_id');
            $table->string('taggable_type', 250);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_taggables');
    }
}
