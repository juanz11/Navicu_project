<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesPaqueteHabitacion extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_paquete_habitacion', function($table)
        {
            $table->integer('regimen_id')->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_paquete_habitacion', function($table)
        {
            $table->dropColumn('regimen_id');
        });
    }
}
