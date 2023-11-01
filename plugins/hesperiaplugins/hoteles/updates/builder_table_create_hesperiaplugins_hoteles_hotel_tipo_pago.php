<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesHotelTipoPago extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_hotel_tipo_pago', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('hotel_id');
            $table->integer('tipo_pago_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_hotel_tipo_pago');
    }
}
