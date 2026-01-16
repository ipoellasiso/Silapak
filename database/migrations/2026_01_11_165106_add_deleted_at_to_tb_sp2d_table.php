<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('tb_sp2d', function (Blueprint $table) {
            $table->softDeletes(); // ini otomatis bikin deleted_at
        });
    }

    public function down()
    {
        Schema::table('tb_sp2d', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
