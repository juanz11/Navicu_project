<?php namespace HesperiaPlugins\Hoteles\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use October\Rain\Database\Schema\Blueprint;

class UpdateMonedaFields extends Migration
{
    const TABLE_NAME = 'hesperiaplugins_hoteles_moneda';
    
    public function up()
    {
        if (!Schema::hasTable(self::TABLE_NAME) || Schema::hasColumn(self::TABLE_NAME, 'tasa') || Schema::hasColumn(self::TABLE_NAME, 'defecto')) {
            return;
        }
        
        Schema::table(self::TABLE_NAME, function (Blueprint $obTable){
            $obTable->decimal('tasa', 10, 2)->default(0);
            $obTable->integer('defecto')->default(0);
        });
    }

    public function down(){
        
        if (!Schema::hasTable(self::TABLE_NAME) || !Schema::hasColumn(self::TABLE_NAME, 'tasa') || !Schema::hasColumn(self::TABLE_NAME, 'defecto')) {
            return;
        }

        Schema::table(self::TABLE_NAME, function (Blueprint $obTable)
        {
            
            $obTable->dropColumn('tasa');
            $obTable->dropColumn('defecto');
        });
        // Schema::drop('qchsoft_hotelesextension_table');
    }
}
