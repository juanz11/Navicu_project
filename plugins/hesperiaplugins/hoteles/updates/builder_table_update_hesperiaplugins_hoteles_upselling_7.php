<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesUpselling7 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_upselling', function($table)
        {
            $table->string('codigo');
            $table->integer('disponible')->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_upselling', function($table)
        {
            $table->dropColumn('codigo');
            $table->integer('disponible')->default(null)->change();
        });
    }
}
