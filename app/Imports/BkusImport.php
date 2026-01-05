<?php

namespace App\Imports;

use App\Models\bkusModel;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BkusImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new bkusModel([
            //
            'id_rekening' => $row['id_rekening'],
            'no_buku' => $row['no_buku'],
            'tgl_transaksi' => date('Y-m-d', strtotime($row['tgl_transaksi'])),
            'uraian' => $row['uraian'],
            'id_opd' => $row['id_opd'],
            'id_bank' => $row['id_bank'],
            'nilai_transaksi' => $row['nilai_transaksi'],
            'tahun' => $row['tahun'],
        ]);
    }

    public function rules(): array
    {
        return [
            'no_buku' => 'required|unique:tb_transaksi',
            'id_rekening' => 'required',
            'id_bank' => 'required',
            // 'id_opd' => 'required',
        ];
    }
}
