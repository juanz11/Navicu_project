<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesTags extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_tags', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('tag', 150);
            $table->string('descripcion', 250);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_tags');
    }
}
