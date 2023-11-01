<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesPaquete9 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_paquete', function($table)
        {
            $table->string('home_destacado', 255)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_paquete', function($table)
        {
            $table->dropColumn('home_destacado');
        });
    }
}
