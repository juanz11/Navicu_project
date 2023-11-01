<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesHotel4 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_hotel', function($table)
        {
            $table->text('paquete_fechas')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_hotel', function($table)
        {
            $table->dropColumn('paquete_fechas');
        });
    }
}
