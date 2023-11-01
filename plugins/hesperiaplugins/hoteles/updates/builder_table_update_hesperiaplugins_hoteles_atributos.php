<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesAtributos extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_atributos', function($table)
        {
            $table->integer('tipo_atributo_id');
        });
    }
    
    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_atributos', function($table)
        {
            $table->dropColumn('tipo_atributo_id');
        });
    }
}
