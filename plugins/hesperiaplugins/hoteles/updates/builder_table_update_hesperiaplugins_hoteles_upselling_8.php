<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesUpselling8 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_upselling', function($table)
        {
            $table->integer('ind_activo')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_upselling', function($table)
        {
            $table->dropColumn('ind_activo');
        });
    }
}
