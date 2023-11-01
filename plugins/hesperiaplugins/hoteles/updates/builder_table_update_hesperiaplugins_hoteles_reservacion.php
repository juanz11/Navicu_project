<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesReservacion extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_reservacion', function($table)
        {
            $table->dateTime('fecha_vigencia')->nullable();
            $table->integer('origen_id');
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_reservacion', function($table)
        {
            $table->dropColumn('fecha_vigencia');
            $table->dropColumn('origen_id');
        });
    }
}
