<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesReservacion7 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_reservacion', function($table)
        {
            $table->decimal('porcentaje_markup', 10, 2)->default(0);
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_reservacion', function($table)
        {
            $table->dropColumn('porcentaje_markup');
        });
    }
}
