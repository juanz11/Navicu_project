<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesReservacion5 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_reservacion', function($table)
        {
            
            $table->text('info_adicional')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_reservacion', function($table)
        {
            
            $table->text('info_adicional')->nullable(false)->change();
        });
    }
}
