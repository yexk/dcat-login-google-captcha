<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdataAdminUsersTable extends Migration
{
    // 这里可以指定你的数据库连接
    public function getConnection()
    {
        return config('database.connection') ?: config('database.default');
    }

    public function config($key)
    {
        return config('admin.' . $key);
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table($this->config('database.users_table'), function (Blueprint $table) {
            $table->string('google_secret')->nullable();
            $table->tinyInteger('is_open_google')->default(0)->comment('是否启用，1是，0不是');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table($this->config('database.users_table'), function (Blueprint $table) {
            $table->removeColumn('google_secret');
            $table->removeColumn('is_open_google');
        });
    }
}
