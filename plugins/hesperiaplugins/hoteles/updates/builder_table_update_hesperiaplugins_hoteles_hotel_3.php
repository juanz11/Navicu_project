<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesHotel3 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_hotel', function($table)
        {
            $table->string('tipo_hotel', 250)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_hotel', function($table)
        {
            $table->dropColumn('tipo_hotel');
        });
    }
}
