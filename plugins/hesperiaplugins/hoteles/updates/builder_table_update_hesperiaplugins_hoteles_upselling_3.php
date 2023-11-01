<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesUpselling3 extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_upselling', function($table)
        {
            $table->integer('ind_calendario');
            $table->integer('disponible')->nullable();
            $table->integer('tipo_inventario')->default(1);
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_upselling', function($table)
        {
            $table->dropColumn('ind_calendario');
            $table->dropColumn('disponible');
            $table->dropColumn('tipo_inventario');
        });
    }
}
