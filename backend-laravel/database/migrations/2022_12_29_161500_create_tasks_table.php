<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->integer('job_id')->nullable();
            $table->integer('account_id')->unsigned();
            $table->integer('owner_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->integer('item_id')->unsigned();
            $table->text('error_message')->nullable();
            $table->tinyText('status');
            $table->boolean('is_cyclic')->nullable();
            $table->timestamp('run_at')->nullable();
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
        Schema::dropIfExists('tasks');
    }
}
