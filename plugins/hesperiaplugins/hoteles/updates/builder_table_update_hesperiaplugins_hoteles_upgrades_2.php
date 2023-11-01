<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesUpgrades2 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_upgrades', function($table)
        {
            $table->decimal('porcentaje_markup', 10, 2)->default(0);
            $table->decimal('precio', 10, 2)->change();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_upgrades', function($table)
        {
            $table->dropColumn('porcentaje_markup');
            $table->decimal('precio', 10, 0)->change();
        });
    }
}
