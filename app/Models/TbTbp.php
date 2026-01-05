<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TbTbp extends Model
{
    protected $table = 'tb_tbp';

    protected $fillable = [
        'id_tbp',
        'nomor_tbp',
        'tanggal_tbp',
        'no_spm',
        'no_npd',
        'nama_skpd',
        'nilai_tbp',
        'status',
        'statuspilihtbp',
    ];

    /**
     * Relasi:
     * 1 TBP punya banyak belanja GU
     */
    public function belanjaGu()
    {
        return $this->hasMany(
            TbBelanjaGu::class,
            'id_tbp',     // foreign key di tb_belanjagu
            'id_tbp'      // local key di tb_tbp
        );
    }

    /**
     * Relasi:
     * 1 TBP punya banyak potongan pajak GU
     */
    public function potonganGu()
    {
        return $this->hasMany(
            TbPotonganGu::class,
            'id_tbp',     // foreign key
            'id_tbp'      // local key
        );
    }
}
