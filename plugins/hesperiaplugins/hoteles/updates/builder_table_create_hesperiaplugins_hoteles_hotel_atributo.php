<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesHotelAtributo extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_hotel_atributo', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('atributo_id');
            $table->integer('hotel_id');
            $table->integer('orden');
            $table->primary(['atributo_id','hotel_id'], "hotel_atributo_pk");
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_hotel_atributo');
    }
}
