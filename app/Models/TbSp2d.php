<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TbSp2d extends Model
{
    use SoftDeletes;
    protected $table = 'tb_sp2d';
    protected $fillable = [
        'jenis','nama_daerah','tahun','nomor_rekening','nama_bank',
        'nomor_sp2d','tanggal_sp2d','nama_skpd','nama_sub_skpd',
        'nama_pihak_ketiga','no_rek_pihak_ketiga','nama_rek_pihak_ketiga',
        'bank_pihak_ketiga','npwp_pihak_ketiga','keterangan_sp2d',
        'nilai_sp2d','cabang_bank','nomor_spm','tanggal_spm',
        'nama_ibu_kota','nama_bud_kbud','nip_bud_kbud','jabatan_bud_kbud'
    ];

    public function belanja()
    {
        return $this->hasMany(TbBelanjaLs::class, 'sp2d_id');
    }

    public function pajak()
    {
        return $this->hasMany(TbPajakPotonganLs::class, 'sp2d_id');
    }

    public function belanjaLs()
    {
        return $this->hasMany(TbBelanjaLs::class, 'sp2d_id');
    }

    public function pajakPotonganLs()
    {
        return $this->hasMany(TbPajakPotonganLs::class, 'sp2d_id');
    }

}
