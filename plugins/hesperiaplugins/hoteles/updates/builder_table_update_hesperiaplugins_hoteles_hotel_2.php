<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesHotel2 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_hotel', function($table)
        {
            $table->dropColumn('atributos');
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_hotel', function($table)
        {
            $table->text('atributos')->nullable();
        });
    }
}
