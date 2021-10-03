<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddQuestion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uid')->index();
            $table->integer('task_id');
            $table->integer('vkey_id')->default(0);
            $table->string('name', 100);
            $table->string('name_e', 100)->nullable();
            $table->text('description')->nullable();
            $table->text('description_e')->nullable();
            $table->timestamps();
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('vkey_id');
        });

        Schema::table('scoreboard', function (Blueprint $table) {
            $table->integer('question_id')->after('task_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questions');

        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('vkey_id')->after('mission_uid');
        });

        Schema::table('scoreboard', function (Blueprint $table) {
            $table->dropColumn('question_id');
        });
    }
}
