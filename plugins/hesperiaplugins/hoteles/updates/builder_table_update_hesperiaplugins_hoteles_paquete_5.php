<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesPaquete5 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_paquete', function($table)
        {
            $table->integer('min_noches')->default(0)->change();
            $table->integer('max_noches')->default(0)->change();
            $table->integer('hotel_id')->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_paquete', function($table)
        {
            $table->integer('min_noches')->default(null)->change();
            $table->integer('max_noches')->default(null)->change();
            $table->integer('hotel_id')->default(null)->change();
        });
    }
}
