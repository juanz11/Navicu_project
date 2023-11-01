<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesDescuentos2 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_descuentos', function($table)
        {
            $table->string('codigo_promocional', 10)->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_descuentos', function($table)
        {
            $table->dropColumn('codigo_promocional');
        });
    }
}
