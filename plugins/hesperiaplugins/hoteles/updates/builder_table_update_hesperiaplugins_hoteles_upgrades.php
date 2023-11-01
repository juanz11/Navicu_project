<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesUpgrades extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_upgrades', function($table)
        {
            $table->dateTime('fecha_disfrute')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_upgrades', function($table)
        {
            $table->dropColumn('fecha_disfrute');
        });
    }
}
