<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesDetalleReservacion extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_detalle_reservacion', function($table)
        {
            $table->decimal('precio', 10, 2)->change();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_detalle_reservacion', function($table)
        {
            $table->decimal('precio', 10, 0)->change();
        });
    }
}
