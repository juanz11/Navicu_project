<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesUpselling4 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_upselling', function($table)
        {
            $table->integer('cantidad_min')->default(0)->change();
            $table->integer('cantidad_max')->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_upselling', function($table)
        {
            $table->integer('cantidad_min')->default(null)->change();
            $table->integer('cantidad_max')->default(null)->change();
        });
    }
}
