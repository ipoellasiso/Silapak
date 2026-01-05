<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TbAkunPajak extends Model
{
    protected $table = 'tb_akun_pajak';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'jenis_pajak',
        'keterangan',
        'status'
    ];
}
