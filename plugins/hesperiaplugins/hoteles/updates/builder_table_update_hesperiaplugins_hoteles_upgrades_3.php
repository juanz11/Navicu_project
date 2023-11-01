<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesUpgrades3 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_upgrades', function($table)
        {
            $table->string('ocupacion', 150);
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_upgrades', function($table)
        {
            $table->dropColumn('ocupacion');
        });
    }
}
