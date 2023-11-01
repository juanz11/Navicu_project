<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesReservacion2 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_reservacion', function($table)
        {
            $table->integer('paquete_id')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_reservacion', function($table)
        {
            $table->dropColumn('paquete_id');
        });
    }
}
