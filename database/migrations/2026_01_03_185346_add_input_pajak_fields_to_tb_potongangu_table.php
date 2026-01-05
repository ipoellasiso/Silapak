<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_potongangu', function (Blueprint $table) {

            $table->string('akun_pajak', 50)->nullable()->after('nama_pajak_potongan');
            $table->string('rek_belanja', 50)->nullable()->after('akun_pajak');

            $table->string('nama_npwp')->nullable()->after('rek_belanja');
            $table->string('no_npwp', 50)->nullable()->after('nama_npwp');

            $table->string('bukti_setoran')->nullable()->after('no_npwp');
        });
    }

    public function down(): void
    {
        Schema::table('tb_potongangu', function (Blueprint $table) {
            $table->dropColumn([
                'akun_pajak',
                'rek_belanja',
                'nama_npwp',
                'no_npwp',
                'bukti_setoran'
            ]);
        });
    }
};
