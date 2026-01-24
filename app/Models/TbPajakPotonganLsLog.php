<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TbPajakPotonganLsLog extends Model
{
    protected $table = 'tb_pajak_potonganls_log';
    public $timestamps = false; // ⬅️ TAMBAHKAN INI

    protected $fillable = [
        'pajak_ls_id',
        'user_id',
        'aksi',
        'sebelum',
        'sesudah',
        'keterangan'
    ];

    protected $casts = [
        'sebelum' => 'array',
        'sesudah' => 'array'
    ];
}

