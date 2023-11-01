<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesFechaCalendario extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_fecha_calendario', function($table)
        {
            $table->integer('disponible')->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_fecha_calendario', function($table)
        {
            $table->integer('disponible')->default(null)->change();
        });
    }
}
