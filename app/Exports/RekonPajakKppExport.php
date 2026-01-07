<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RekonPajakKppExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $tahun;
    protected $bulan;
    protected $opd;

    public function __construct($tahun, $bulan = null, $opd = null)
    {
        $this->tahun = $tahun;
        $this->bulan = $bulan;
        $this->opd   = $opd;
    }

    public function query()
    {
        $query = DB::table('tb_potongangu as pot')
            ->join('tb_tbp as tbp','tbp.id_tbp','=','pot.id_tbp')
            ->whereYear('tbp.tanggal_tbp', $this->tahun)
            ->whereNull('pot.status4'); // 🔥 HANYA BELUM FINAL

        if ($this->bulan) {
            $query->whereMonth('tbp.tanggal_tbp', $this->bulan);
        }

        if ($this->opd) {
            $query->where('tbp.nama_skpd', $this->opd);
        }

        return $query
            ->orderBy('tbp.nama_skpd')        // ✅ WAJIB
            ->orderBy('tbp.tanggal_tbp')     // ✅ WAJIB
            ->select(
                'tbp.nama_skpd',
                'tbp.no_spm',
                'tbp.nomor_tbp',
                'tbp.tanggal_tbp',
                'pot.nama_pajak_potongan',
                'pot.akun_pajak',
                'pot.nilai_tbp_pajak_potongan',
                'pot.ntpn'
            );
    }

    public function headings(): array
    {
        return [
            'OPD',
            'No SPM',
            'No TBP',
            'Tanggal TBP',
            'Jenis Pajak',
            'Akun Pajak',
            'Nilai Pajak',
            'NTPN'
        ];
    }

    public function map($r): array
    {
        return [
            $r->nama_skpd,
            $r->no_spm,
            $r->nomor_tbp,
            $r->tanggal_tbp,
            $r->nama_pajak_potongan,
            $r->akun_pajak,
            $r->nilai_tbp_pajak_potongan,
            $r->ntpn
        ];
    }
}
