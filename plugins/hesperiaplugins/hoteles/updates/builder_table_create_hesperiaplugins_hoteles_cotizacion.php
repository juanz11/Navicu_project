<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesCotizacion extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_cotizacion', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('reserva_id');
            $table->integer('cotizable_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->text('cotizable_type');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_cotizacion');
    }
}
