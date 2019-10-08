<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IntegrateQrcodeWithKeypool extends Migration
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
                if (! Schema::hasColumn(self::$table_name, 'slug')) {
                    $table->string('slug', 100)->default('')->after('note');
                }
                if (! Schema::hasColumn(self::$table_name, 'account')) {
                    $table->string('account', 10)->default('')->after('slug');
                }
                if (! Schema::hasColumn(self::$table_name, 'passwd')) {
                    $table->string('passwd', 10)->default('')->after('account');
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
                if (Schema::hasColumn(self::$table_name, 'slug')) {
                    $table->dropColumn('slug');
                }
                if (Schema::hasColumn(self::$table_name, 'account')) {
                    $table->dropColumn('account');
                }
                if (Schema::hasColumn(self::$table_name, 'passwd')) {
                    $table->dropColumn('passwd');
                }
            });
        }
    }
}
