<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TbPajakPotonganLs extends Model
{
    protected $table = 'tb_pajak_potonganls';
    protected $fillable = [
        'sp2d_id','id_pajak_potongan','nama_pajak_potongan','kode_sinergi',
        'nama_sinergi','id_billing','nilai_sp2d_pajak_potongan'
    ];

    public function sp2d()
    {
        return $this->belongsTo(TbSp2d::class, 'sp2d_id');
    }
}
