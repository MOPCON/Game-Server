<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeKeypoolFieldLength extends Migration
{
    public static $table_name = 'key_pool';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable(self::$table_name)) {
            Schema::table(self::$table_name, function (Blueprint $table) {
                if (Schema::hasColumn(self::$table_name, 'account')) {
                    $table->string('account', 30)->change();
                }
                if (Schema::hasColumn(self::$table_name, 'passwd')) {
                    $table->string('passwd', 30)->change();
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
                if (Schema::hasColumn(self::$table_name, 'account')) {
                    $table->string('account', 10)->change();
                }
                if (Schema::hasColumn(self::$table_name, 'passwd')) {
                    $table->string('passwd', 10)->change();
                }
            });
        }
    }
}
