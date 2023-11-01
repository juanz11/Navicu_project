<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesUpselling extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_upselling', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('titulo', 150);
            $table->text('descripcion');
            $table->integer('hotel_id');
            $table->integer('cantidad_min');
            $table->integer('cantidad_max');
            $table->integer('sumable')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_upselling');
    }
}
