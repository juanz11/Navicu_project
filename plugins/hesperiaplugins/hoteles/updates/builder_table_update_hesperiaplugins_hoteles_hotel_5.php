<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesHotel5 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_hotel', function($table)
        {
            $table->integer('maximo_noches')->default(100);
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_hotel', function($table)
        {
            $table->dropColumn('maximo_noches');
        });
    }
}
