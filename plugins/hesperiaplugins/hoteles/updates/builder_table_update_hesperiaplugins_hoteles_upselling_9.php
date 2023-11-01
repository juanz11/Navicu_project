<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesUpselling9 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_upselling', function($table)
        {
            $table->string('adds_val', 250);
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_upselling', function($table)
        {
            $table->dropColumn('adds_val');
        });
    }
}
