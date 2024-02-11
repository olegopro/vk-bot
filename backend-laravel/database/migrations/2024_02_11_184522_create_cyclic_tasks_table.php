<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCyclicTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cyclic_tasks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('job_id');
            $table->integer('account_id')->unsigned()->unique();
            $table->unsignedInteger('likes_per_hour');
            $table->timestamp('started_at')->nullable();
            $table->unsignedInteger('likes')->default(0);
            $table->string('status');
            $table->timestamps();

            $table->foreign('account_id')->references('account_id')->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cyclic_tasks');
    }
}
