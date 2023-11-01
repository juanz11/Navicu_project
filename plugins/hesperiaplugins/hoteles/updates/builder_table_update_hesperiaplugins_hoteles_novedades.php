<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesNovedades extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_novedades', function($table)
        {
            $table->string('subtitulo', 200)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_novedades', function($table)
        {
            $table->dropColumn('subtitulo');
        });
    }
}
