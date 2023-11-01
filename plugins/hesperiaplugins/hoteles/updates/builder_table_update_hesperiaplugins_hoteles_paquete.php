<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateHesperiapluginsHotelesPaquete extends Migration
{
    public function up()
    {
        Schema::table('hesperiaplugins_hoteles_paquete', function($table)
        {
            $table->increments('id')->unsigned(false)->change();
            $table->integer('porcentaje')->nullable(false)->unsigned(false)->default(null)->change();
        });
    }

    public function down()
    {
        Schema::table('hesperiaplugins_hoteles_paquete', function($table)
        {
            $table->increments('id')->unsigned()->change();
            $table->decimal('porcentaje', 10, 2)->nullable(false)->unsigned(false)->default(null)->change();
        });
    }
}
