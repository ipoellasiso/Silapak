<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TbBelanjaLs extends Model
{
    protected $table = 'tb_belanjals';
    protected $fillable = [
        'sp2d_id','kode_rekening','uraian','total_anggaran','jumlah'
    ];

    public function sp2d()
    {
        return $this->belongsTo(TbSp2d::class, 'sp2d_id');
    }
}

