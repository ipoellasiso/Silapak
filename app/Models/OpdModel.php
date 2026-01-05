<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpdModel extends Model
{
    use HasFactory;
    protected $table = "opd";
    protected $primaryKey = "id";
    protected $fillable = [
        'nama_opd',
        'nama_bendahara',
        'alamat'
    ];
}
