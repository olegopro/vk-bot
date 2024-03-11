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
            $table->id();
            $table->unsignedInteger('account_id');
            $table->integer('total_task_count')->default(0);
            $table->unsignedInteger('remaining_tasks_count')->default(0);
            $table->unsignedInteger('tasks_per_hour')->default(0);
            $table->string('likes_distribution')->nullable();
            $table->json('selected_times')->nullable();
            $table->string('status');
            $table->timestamp('started_at')->nullable();
            $table->timestamps();

            $table->foreign('account_id')
                ->references('account_id')
                ->on('accounts')
                ->onDelete('cascade');
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
