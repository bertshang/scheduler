<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('totem.table_prefix').'tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description')->comment('定时任务具体描述');
            $table->string('command')->comment('命令');
            $table->string('parameters')->nullable()->comment('命令参数');
            $table->string('expression')->nullable()->comment('命令表达式');
            $table->string('timezone')->default('Asia/Shanghai')->comment('时区');
            $table->integer('is_active')->default(1)->comment('是否激活');
            $table->integer('dont_overlap')->default(false)->comment('是否重叠');
            $table->integer('run_in_maintenance')->default(false)->comment('在维护状态下运行');
            $table->string('notification_email_address')->nullable()->comment('通知邮件地址');
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
        Schema::dropIfExists(config('totem.table_prefix').'tasks');
    }
}
