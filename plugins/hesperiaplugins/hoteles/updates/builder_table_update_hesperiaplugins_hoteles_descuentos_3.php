<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesDescuentos3 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_descuentos', function($table)
        {
            $table->integer('minimo_noches')->default(0);
            $table->integer('noches_gratis')->default(0);
            $table->integer('noches_antelacion')->default(0);
            
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_descuentos', function($table)
        {
            $table->dropColumn('minimo_noches');
            $table->dropColumn('noches_gratis');
            $table->dropColumn('noches_antelacion');
    
        });
    }
}
