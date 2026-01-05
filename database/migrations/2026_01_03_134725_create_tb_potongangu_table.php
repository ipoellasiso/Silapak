<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tb_potongangu', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();
            $table->string('id_tbp');

            // data pajak dari SIPD (READ ONLY)
            $table->string('nama_pajak_potongan')->nullable();
            $table->decimal('nilai_tbp_pajak_potongan', 14, 0)->nullable();

            // pelengkap oleh OPD
            $table->string('id_billing')->nullable();
            $table->string('ntpn')->nullable();

            // status
            $table->string('status1')->nullable();
            $table->string('status3')->nullable(); // BELUM SETOR / SUDAH SETOR
            $table->string('status4')->nullable(); // VERIFIKASI

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
        Schema::dropIfExists('tb_potongangu');
    }
};
