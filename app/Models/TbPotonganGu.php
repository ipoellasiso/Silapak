<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TbPotonganGu extends Model
{
    protected $table = 'tb_potongangu';

    protected $fillable = [
        'id_tbp',
        'nama_pajak_potongan',
        'nilai_tbp_pajak_potongan',
        'id_billing',
        'akun_pajak',
        'rek_belanja',
        'nama_npwp',
        'no_npwp',
        'ntpn',
        'bukti_setoran',
        'status1',
        'status3',
        'status4'
    ];

    /**
     * Relasi:
     * Potongan pajak milik satu TBP
     */
    public function tbp()
    {
        return $this->belongsTo(
            TbTbp::class,
            'id_tbp',     // foreign key
            'id_tbp'      // owner key
        );
    }

    protected static function booted()
    {
        static::updating(function ($model) {
            if ($model->isDirty('nilai_tbp_pajak_potongan')) {
                abort(403, 'Nilai pajak tidak boleh diubah');
            }
        });
    }
    
}
