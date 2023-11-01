<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesPaqueteUpselling extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_paquete_upselling', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('paquete_id');
            $table->integer('upselling_id');
            $table->integer('obligatorio')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_paquete_upselling');
    }
}
