<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tb_tbp', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->string('id_tbp')->unique(); // ID TBP dari SIPD

            $table->string('nomor_tbp')->nullable();
            $table->date('tanggal_tbp')->nullable();

            $table->string('no_spm')->nullable();
            $table->string('no_npd')->nullable();

            $table->string('nama_skpd')->nullable();
            $table->decimal('nilai_tbp', 20, 0)->nullable();

            $table->string('status')->nullable();
            $table->string('statuspilihtbp')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_tbp');
    }
};
