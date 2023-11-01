<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateHesperiapluginsHotelesUpgrades extends Migration
{
    public function up()
    {
        Schema::create('hesperiaplugins_hoteles_upgrades', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('upgradable_id');
            $table->string('upgradable_type', 150);
            $table->decimal('precio', 10, 0);
            $table->integer('moneda_id');
            $table->integer('upselling_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('cantidad');
        });
    }

    public function down()
    {
        Schema::dropIfExists('hesperiaplugins_hoteles_upgrades');
    }
}
