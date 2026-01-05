<?php

namespace App\Imports;

use App\Models\AnggaranModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AnggaranImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new AnggaranModel([
            'id_rekening' => $row['id_rekening'],
            'id_opd' => $row['id_opd'],
            'nilai' => $row['nilai'],
            'tahun' => $row['tahun'],
        ]);
    }

    public function rules(): array
    {
        return [
            'id_rekening' => 'required|unique:tb_anggaran',
            // 'rekening' => 'required|unique:tb_rekening',
            // 'rekening2' => 'required|unique:tb_rekening',
        ];
    }
}
