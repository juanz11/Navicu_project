<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesDetalleReservacion2 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_detalle_reservacion', function($table)
        {
            $table->text('huespedes');
          
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_detalle_reservacion', function($table)
        {
            $table->dropColumn('huespedes');
           
        });
    }
}
