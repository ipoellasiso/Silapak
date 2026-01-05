<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tb_belanjagu', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->string('id_tbp');

            $table->string('kode_rekening')->nullable();
            $table->string('uraian')->nullable();
            $table->decimal('jumlah', 14, 2)->nullable();

            $table->timestamps();

            $table->index('id_tbp');
            $table->foreign('id_tbp')
                ->references('id_tbp')
                ->on('tb_tbp')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_belanjagu');
    }
};
