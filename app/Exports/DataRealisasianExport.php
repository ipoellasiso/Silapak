<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithStyles;
use Illuminate\Support\Facades\Auth;

class DataRealisasianExport implements WithStyles
{
    public function styles(Worksheet $sheet)
    {

        $style_col = [
            'font' => ['bold' => true],
            'aligment' => [
                'horizontal'=> \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'top'       => ['borderstyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'right'     => ['borderstyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'bottom'    => ['borderstyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'left'      => ['borderstyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ]
        ];

        $style_row = [
            'aligment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'top'       => ['borderstyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'right'     => ['borderstyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'bottom'    => ['borderstyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'left'      => ['borderstyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ]
        ];

        $sheet->setCellValue('A1', "Database Laporan Realisasi");
        $sheet->setCellValue('A2', "");
        $sheet->mergeCells('A1:L1');
        $sheet->mergeCells('A2:L2');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(15);

        // buat header tabelnya
        // $sheet->setCellValue('A4', "No");
        $sheet->setCellValue('A4', "Kode Rekening");
        $sheet->setCellValue('B4', "Rekening");
        $sheet->setCellValue('C4', "Nilai");
        $sheet->setCellValue('D4', "Nomor SP2D");
        $sheet->setCellValue('E4', "Nomor SPM");
        $sheet->setCellValue('F4', "Tanggal SP2D");
        $sheet->setCellValue('G4', "Nama Opd");
        $sheet->setCellValue('H4', "Keterangan SP2D");
        $sheet->setCellValue('I4', "Nilai SP2D");
        $sheet->setCellValue('J4', "Jenis Belanja");
        $sheet->setCellValue('K4', "Kegiatan GU");
        $sheet->setCellValue('L4', "Sub Kegiatan GU");
        

        // $sheet->getStyle('A4')->applyFromArray($style_col);
        $sheet->getStyle('A4')->applyFromArray($style_col);
        $sheet->getStyle('B4')->applyFromArray($style_col);
        $sheet->getStyle('C4')->applyFromArray($style_col);
        $sheet->getStyle('D4')->applyFromArray($style_col);
        $sheet->getStyle('E4')->applyFromArray($style_col);
        $sheet->getStyle('F4')->applyFromArray($style_col);
        $sheet->getStyle('G4')->applyFromArray($style_col);
        $sheet->getStyle('H4')->applyFromArray($style_col);
        $sheet->getStyle('I4')->applyFromArray($style_col);
        $sheet->getStyle('J4')->applyFromArray($style_col);
        $sheet->getStyle('K4')->applyFromArray($style_col);
        $sheet->getStyle('L4')->applyFromArray($style_col);

        $userId = Auth::id();
        $data_office = DB::table('tb_sp2d')
                        ->select('tb_sp2d.nomor_spm', 'tb_sp2d.nomor_sp2d', 'tb_sp2d.tanggal_sp2d', 'tb_sp2d.keterangan_sp2d', 'tb_sp2d.nilai_sp2d', 'tb_sp2d.jenis', 'tb_sp2d.nama_skpd', 'tb_belanjals.uraian', 'tb_belanjals.kode_rekening', 'tb_belanjals.jumlah', 'tb_belanjals.kode_kegiatan', 'tb_belanjals.nama_kegiatan', 'tb_belanjals.kode_sub_kegiatan', 'tb_belanjals.nama_sub_kegiatan')
                        ->join('tb_belanjals', 'tb_belanjals.sp2d_id', '=', 'tb_sp2d.id')
                        ->where('tb_sp2d.nama_skpd', auth()->user()->nama_opd)
                        ->get();
        
        $no = 1;
        $numrow = 5;

        foreach($data_office as $data){
            // $sheet->setCellValue('A'.$numrow, $no++);
            $sheet->setCellValue('A'.$numrow, $data->kode_rekening);
            $sheet->setCellValue('B'.$numrow, $data->uraian);
            $sheet->setCellValue('C'.$numrow, $data->jumlah);
            $sheet->setCellValue('D'.$numrow, $data->nomor_sp2d);
            $sheet->setCellValue('E'.$numrow, $data->nomor_spm);
            $sheet->setCellValue('F'.$numrow, date('d/m/y', strtotime($data->tanggal_sp2d)));
            $sheet->setCellValue('G'.$numrow, $data->nama_skpd);
            $sheet->setCellValue('H'.$numrow, $data->keterangan_sp2d);
            $sheet->setCellValue('I'.$numrow, $data->nilai_sp2d);
            $sheet->setCellValue('J'.$numrow, $data->jenis);
            $sheet->setCellValue('K'.$numrow, $data->nama_kegiatan);
            $sheet->setCellValue('L'.$numrow, $data->nama_sub_kegiatan);

            // Apply style row yang telah kita buat tadi di masing" baris
            // $sheet->getStyle('A'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('A'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('B'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('C'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('D'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('E'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('F'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('G'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('H'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('I'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('J'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('K'.$numrow)->applyFromArray($style_row);
            $sheet->getStyle('L'.$numrow)->applyFromArray($style_row);

            $numrow++;
        }

        // set witdh kolom
        // $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(23);
        $sheet->getColumnDimension('D')->setWidth(50);
        $sheet->getColumnDimension('E')->setWidth(50);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(60);
        $sheet->getColumnDimension('H')->setWidth(100);
        $sheet->getColumnDimension('I')->setWidth(23);
        $sheet->getColumnDimension('J')->setWidth(12);
        $sheet->getColumnDimension('K')->setWidth(50);
        $sheet->getColumnDimension('L')->setWidth(50);


        // set kolom menjadi auto
        $sheet->getDefaultRowDimension()->setRowHeight(-1);
        // set kertas menjadi landscape
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

    }
}
