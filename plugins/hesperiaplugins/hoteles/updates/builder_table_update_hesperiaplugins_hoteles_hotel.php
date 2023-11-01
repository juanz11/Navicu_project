<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesHotel extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_hotel', function($table)
        {
            $table->integer('solo_adultos')->default(0);
            $table->decimal('porcentaje_markup', 10, 2)->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_hotel', function($table)
        {
            $table->dropColumn('solo_adultos');
            $table->dropColumn('porcentaje_markup');
        });
    }
}
