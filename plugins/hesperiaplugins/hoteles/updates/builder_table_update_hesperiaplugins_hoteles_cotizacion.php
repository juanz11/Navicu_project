<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesCotizacion extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_cotizacion', function($table)
        {
            $table->text('usable_type');
            $table->renameColumn('reserva_id', 'usable_id');
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_cotizacion', function($table)
        {
            $table->dropColumn('usable_type');
            $table->renameColumn('usable_id', 'reserva_id');
        });
    }
}
