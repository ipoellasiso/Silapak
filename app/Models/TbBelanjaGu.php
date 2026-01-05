<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TbBelanjaGu extends Model
{
    protected $table = 'tb_belanjagu';

    protected $fillable = [
        'id_tbp',
        'kode_rekening',
        'uraian',
        'jumlah',
    ];

    /**
     * Relasi:
     * Belanja GU milik satu TBP
     */
    public function tbp()
    {
        return $this->belongsTo(
            TbTbp::class,
            'id_tbp',     // foreign key
            'id_tbp'      // owner key
        );
    }
}
