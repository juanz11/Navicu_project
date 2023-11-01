<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesObservacion extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_observacion', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->text('descripcion');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('usable_id');
            $table->string('usable_type', 250);
            $table->integer('observable_id');
            $table->string('observable_type', 250);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_observacion');
    }
}
