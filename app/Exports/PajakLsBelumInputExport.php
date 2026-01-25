<?php

namespace App\Exports;

use App\Models\TbSp2d;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PajakLsBelumInputExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $batasHari = Carbon::now()->subDays(2)->startOfDay();

        $data = TbSp2d::whereDate('tanggal_sp2d', '<=', $batasHari)
            ->where(function ($q) {
                $q->whereDoesntHave('pajakPotonganLs')
                  ->orWhereHas('pajakPotonganLs', function ($p) {
                      $p->whereNull('status1');
                  });
            })
            ->with('pajakPotonganLs')
            ->get();

        $rows = collect();

        foreach ($data as $sp2d) {
            // kalau BELUM ADA pajak sama sekali
            if ($sp2d->pajakPotonganLs->count() == 0) {
                $rows->push([
                    'nomor_sp2d' => $sp2d->nomor_sp2d,
                    'tanggal_sp2d' => $sp2d->tanggal_sp2d,
                    'skpd' => $sp2d->nama_skpd,
                    'sub_skpd' => $sp2d->nama_sub_skpd,
                    'pihak_ketiga' => $sp2d->nama_pihak_ketiga,
                    'jenis_pajak' => '-',
                    'id_billing' => '-',
                    'ntpn' => '-',
                    'nilai_pajak' => 0,
                    'status_pajak' => 'BELUM INPUT'
                ]);
            }

            // kalau ADA pajak tapi status1 NULL
            foreach ($sp2d->pajakPotonganLs as $pajak) {
            // FILTER JENIS PAJAK
            if (
                is_null($pajak->status1) &&
                (
                    str_contains($pajak->nama_pajak_potongan, 'PPH 21') ||
                    str_contains($pajak->nama_pajak_potongan, 'Pajak Pertambahan Nilai') ||
                    str_contains($pajak->nama_pajak_potongan, 'Pajak Penghasilan Ps 22') ||
                    str_contains($pajak->nama_pajak_potongan, 'Pajak Penghasilan Ps 23') ||
                    str_contains($pajak->nama_pajak_potongan, 'Pajak Penghasilan Pasal 4 ayat (2)')
                )
            ) {
                $rows->push([
                    'nomor_sp2d'   => $sp2d->nomor_sp2d,
                    'tanggal_sp2d'=> $sp2d->tanggal_sp2d,
                    'skpd'        => $sp2d->nama_skpd,
                    'sub_skpd'    => $sp2d->nama_sub_skpd,
                    'pihak_ketiga'=> $sp2d->nama_pihak_ketiga,
                    'jenis_pajak' => $pajak->nama_pajak_potongan,
                    'id_billing'  => $pajak->id_billing ?? '-',
                    'ntpn'        => $pajak->ntpn ?? '-',
                    'nilai_pajak' => $pajak->nilai_sp2d_pajak_potongan,
                    'status_pajak'=> 'BELUM INPUT'
                ]);
            }
        }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Nomor SP2D',
            'Tanggal SP2D',
            'SKPD',
            'Sub SKPD',
            'Pihak Ketiga',
            'Jenis Pajak',
            'ID Billing',
            'NTPN',
            'Nilai Pajak',
            'Status Pajak'
        ];
    }
}
