<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKeypoolTaskRelation extends Migration
{
    public static $table_name = 'tasks';

    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        if (Schema::hasTable(self::$table_name)) {
            Schema::table(self::$table_name, function (Blueprint $table) {
                if (! Schema::hasColumn(self::$table_name, 'vkey_id')) {
                    $table->integer('vkey_id')->default(0)->after('uid');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable(self::$table_name)) {
            Schema::table(self::$table_name, function (Blueprint $table) {
                if (Schema::hasColumn(self::$table_name, 'vkey_id')) {
                    $table->dropColumn('vkey_id');
                }
            });
        }
    }
}

