<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesHotelRegimen extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_hotel_regimen', function($table)
        {
            $table->integer('defecto')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_hotel_regimen', function($table)
        {
            $table->dropColumn('defecto');
        });
    }
}
