<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LaporanPajakKppExport implements FromCollection, WithHeadings, WithMapping
{
    protected $tahun;
    protected $bulan;
    protected $opd;
    protected $sp2dBySpm;

    public function __construct($tahun, $bulan = null, $opd = null)
    {
        $this->tahun = $tahun;
        $this->bulan = $bulan;
        $this->opd   = $opd;

        // 🔹 AMBIL API SP2D SEKALI
        $sp2d = Http::get('http://127.0.0.1:8001/api/sp2d')->json();

        $this->sp2dBySpm = collect($sp2d)
            ->mapWithKeys(fn($i) => [ trim($i['nomor_spm']) => $i ]);
    }

    public function collection()
    {
        $query = DB::table('tb_tbp as tbp')
            ->join('tb_potongangu as pot','tbp.id_tbp','=','pot.id_tbp')
            ->where('pot.status4','POSTING')
            ->whereYear('tbp.tanggal_tbp', $this->tahun);

        if ($this->bulan) {
            $query->whereMonth('tbp.tanggal_tbp', $this->bulan);
        }

        if ($this->opd) {
            $query->where('tbp.nama_skpd', $this->opd);
        }

        return $query->select(
            'tbp.nama_skpd',
            'tbp.no_spm',
            'tbp.nomor_tbp',
            'pot.nama_pajak_potongan',
            'pot.akun_pajak',
            'pot.no_npwp',
            'pot.nama_npwp',
            'pot.ntpn',
            'pot.nilai_tbp_pajak_potongan'
        )->get();
    }

    public function headings(): array
    {
        return [
            'No SPM',
            'No TBP',
            'Tanggal SP2D',
            'Nomor SP2D',
            'Nilai SP2D',
            'Jenis Pajak',
            'Akun Pajak',
            'NPWP',
            'Nama NPWP',
            'NTPN',
            'Nilai Pajak',
             'OPD',
        ];
    }

    public function map($r): array
    {
        $sp2d = $this->sp2dBySpm[trim($r->no_spm)] ?? null;

        return [
            $r->no_spm,
            $r->nomor_tbp,
            $sp2d['tanggal_sp2d'] ?? '-',
            $sp2d['nomor_sp2d'] ?? '-',
            $sp2d ? $sp2d['nilai_sp2d'] : 0,
            $r->nama_pajak_potongan,
            $r->akun_pajak,
            $r->no_npwp,
            $r->nama_npwp,
            $r->ntpn,
            $r->nilai_tbp_pajak_potongan,
            $r->nama_skpd,
        ];
    }
}
