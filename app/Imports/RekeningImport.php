<?php

namespace App\Imports;

use App\Models\RekeningModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class RekeningImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable, SkipsFailures;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new RekeningModel([
            'no_rekening' => $row['no_rekening'],
            'rekening' => $row['rekening'],
            'rekening2' => $row['rekening2'],
            'ket1' => $row['ket1'],
            'ket2' => $row['ket2'],
            'ket3' => $row['ket3'],
            'ket4' => $row['ket4'],

        ]);
    }

    public function rules(): array
    {
        return [
            'no_rekening' => 'required|unique:tb_rekening',
            // 'rekening' => 'required|unique:tb_rekening',
            // 'rekening2' => 'required|unique:tb_rekening',
        ];
    }
}
