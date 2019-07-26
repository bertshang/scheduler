<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('totem.table_prefix').'task_results', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('task_id');
            $table->timestamp('ran_at')->useCurrent();
            $table->string('duration')->comment('定时任务执行时间');
            $table->longText('result')->comment('定时任务执行结果');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('totem.table_prefix').'task_results');
    }
}
