<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesPrecioFechaCalendario extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_precio_fecha_calendario', function($table)
        {
            $table->decimal('precio_nino', 10, 2)->default(0);
            $table->decimal('precio', 10, 2)->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_precio_fecha_calendario', function($table)
        {
            $table->dropColumn('precio_nino');
            $table->decimal('precio', 10, 0)->default(null)->change();
        });
    }
}
