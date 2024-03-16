<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('show_followers')->default(true);
            $table->boolean('show_friends')->default(true);
            $table->integer('task_timeout')->unsigned()->default(30);
        });

        // Добавление начальной записи непосредственно после создания таблицы
        DB::table('settings')->insert([
            'show_followers' => false,
            'show_friends'   => true,
            'task_timeout'   => 30,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
