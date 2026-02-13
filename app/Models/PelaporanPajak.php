<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PelaporanPajak extends Model
{
    protected $table = 'tb_pelaporan_pajak';

    protected $fillable = [
        'sumber_pajak',
        'sumber_id',

        'jenis_pajak',
        'akun_pajak',

        'masa_pajak_bulan',
        'masa_pajak_tahun',

        'masa_lapor_bulan',
        'masa_lapor_tahun',

        'nilai_pajak',

        'id_billing',
        'ntpn',

        'tanggal_lapor',
        'status_lapor',

        'lapor_by',
    ];

    protected $casts = [
        'masa_pajak_bulan' => 'integer',
        'masa_pajak_tahun' => 'integer',
        'masa_lapor_bulan' => 'integer',
        'masa_lapor_tahun' => 'integer',
        'nilai_pajak'      => 'float',
        'tanggal_lapor'    => 'date',
    ];
}
