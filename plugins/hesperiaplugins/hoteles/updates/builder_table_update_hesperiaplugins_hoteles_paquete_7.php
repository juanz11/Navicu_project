<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesPaquete7 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_paquete', function($table)
        {
            $table->string('ciudad')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_paquete', function($table)
        {
            $table->dropColumn('ciudad');
        });
    }
}
