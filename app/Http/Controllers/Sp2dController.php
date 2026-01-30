<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\UserModel;
use App\Models\TbSp2d;
use App\Models\TbBelanjaLs;
use App\Models\TbPajakPotonganLs;

class Sp2dController extends Controller
{
    protected int $tahunAktif;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->tahunAktif = auth()->user()->tahun;
            return $next($request);
        });
    }

    public function index()
    {
        $userId = Auth::id();

        $data = [
            'title'                 => 'Data SP2D LS',
            'active_pengeluaran'    => 'active',
            'active_subopd'         => 'active',
            'active_sp2d'           => 'active',
            'breadcumd'             => 'Pengaturan',
            'breadcumd1'            => 'Data Ingestion',
            'breadcumd2'            => 'Tarik SP2D LS',
            'userx'                 => UserModel::where('id',$userId)->first(['fullname','role','gambar']),
            'opd'                   => DB::table('users')
                                        ->where('nama_opd', auth()->user()->nama_opd)
                                        ->first(),
        ];

        return view('bpkad.sp2d.tarik', $data);
    }

    public function dataLS(Request $request)
    {
        if ($request->ajax()) {
            $data = TbSp2d::where('jenis', 'LS')
                ->select('id','nomor_sp2d','nama_skpd','nama_pihak_ketiga','nilai_sp2d','tanggal_sp2d');

            return datatables()->of($data)
                ->addIndexColumn()
                ->editColumn('nilai_sp2d', fn($r) => number_format($r->nilai_sp2d))
                ->addColumn('aksi', function($r){
                    return '
                        <button class="btn btn-info btn-sm btn-detail" data-id="'.$r->id.'">
                            Detail
                        </button>
                    ';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }
    }

    public function dataGU(Request $request)
    {
        if ($request->ajax()) {

            $data = TbSp2d::where('jenis', 'GU')
                ->where('tahun', $this->tahunAktif)
                ->select(
                    'id',
                    'nomor_sp2d',
                    'nama_skpd',
                    'nama_pihak_ketiga',
                    'nilai_sp2d',
                    'tanggal_sp2d'
                )
                ->orderBy('tanggal_sp2d','desc');

            return datatables()->of($data)
                ->addIndexColumn()
                ->editColumn('nilai_sp2d', fn($r) => number_format($r->nilai_sp2d))
                ->editColumn('tanggal_sp2d', fn($r) => date('d-m-Y', strtotime($r->tanggal_sp2d)))
                ->addColumn('aksi', function ($r) {
                    return '
                        <button class="btn btn-sm btn-info btn-detail-gu"
                            data-id="'.$r->id.'">
                            Detail
                        </button>
                    ';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }
    }

    public function dataKKPD(Request $request)
    {
        if ($request->ajax()) {
            $data = TbSp2d::where('tahun', $this->tahunAktif)
                ->where('jenis', 'KKPD')
                ->where('nama_skpd', auth()->user()->nama_opd)
                ->whereNull('deleted_at')
                ->select('id','nomor_sp2d','nama_skpd','nama_pihak_ketiga','nilai_sp2d','tanggal_sp2d');

            return datatables()->of($data)
                ->addIndexColumn()
                ->editColumn('nilai_sp2d', fn($r) => number_format($r->nilai_sp2d))
                ->make(true);
        }
    }

    public function dataHapus(Request $request)
    {
        if ($request->ajax()) {
            $data = TbSp2d::onlyTrashed()
                ->where('tahun', $this->tahunAktif)
                ->where('nama_skpd', auth()->user()->nama_opd)
                ->select('id','nomor_sp2d','nama_skpd','nama_pihak_ketiga','nilai_sp2d','tanggal_sp2d');

            return datatables()->of($data)
                ->addIndexColumn()
                ->editColumn('nilai_sp2d', fn($r) => number_format($r->nilai_sp2d))
                ->addColumn('aksi', function($r){
                    return '
                        <button class="btn btn-sm btn-success btn-restore" data-id="'.$r->id.'">
                            Restore
                        </button>
                    ';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'json_data' => 'required'
        ]);

        $json = json_decode($request->json_data, true);

        if (!$json || !isset($json['jenis'])) {
            return response()->json([
                'success' => false,
                'message' => 'Format JSON SIPD tidak valid'
            ], 422);
        }

        DB::beginTransaction();
        try {

            if ($json['jenis'] === 'GU' && !empty($json['gu'])) {
                $this->simpanSp2dGU($json['gu']);
            }

            if ($json['jenis'] === 'LS' && !empty($json['ls'])) {
                $this->simpanSp2dLS($json['ls']);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'SP2D berhasil disimpan'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function simpanSp2dGU(array $gu)
    {
        if (empty($gu['nomor_sp_2_d'])) {
            throw new \Exception('Nomor SP2D GU kosong');
        }

        if (TbSp2d::where('nomor_sp2d', $gu['nomor_sp_2_d'])->exists()) {
            throw new \Exception('SP2D GU sudah ada');
        }

        $sp2d = TbSp2d::create([
            'jenis' => 'GU',
            'nama_daerah' => $gu['nama_daerah'],
            'tahun' => $gu['tahun'],
            'nomor_rekening' => $gu['nomor_rekening'],
            'nama_bank' => $gu['nama_bank'],
            'nomor_sp2d' => $gu['nomor_sp_2_d'],
            'tanggal_sp2d' => substr($gu['tanggal_sp_2_d'], 0, 10),
            'nama_skpd' => $gu['nama_skpd'],
            'nama_pihak_ketiga' => $gu['nama_bp_bpp'],
            'npwp_pihak_ketiga' => $gu['npwp_bp_bpp'],
            'keterangan_sp2d' => $gu['keterangan_sp2d'],
            'nilai_sp2d' => $gu['nilai_sp2d'],
            'nomor_spm' => $gu['nomor_spm'],
            'tanggal_spm' => substr($gu['tanggal_spm'], 0, 10),
        ]);

        // ==== VAR KONTEKS KEGIATAN ====
        $kodeKegiatan = null;
        $namaKegiatan = null;
        $kodeSub = null;
        $namaSub = null;

        // ================= DETAIL BELANJA GU =================
        foreach ($gu['detail'] ?? [] as $row) {

            $kode = trim($row['kode_rekening']);

            // === KEGIATAN ===
            if (preg_match('/^Keg[i]?atan\s*:\s*([\d\.]+)\s*-\s*(.+)$/i', $kode, $m)) {
                $kodeKegiatan = $m[1];
                $namaKegiatan = $m[2];
                continue;
            }

            // === SUB KEGIATAN ===
            if (preg_match('/^Sub\s*Keg[i]?atan\s*:\s*([\d\.]+)\s*-\s*(.+)$/i', $kode, $m)) {
                $kodeSub = $m[1];
                $namaSub = $m[2];
                continue;
            }

            // === BELANJA (REKENING 5.x SAJA) ===
            if (preg_match('/^5\./', $kode)) {
                TbBelanjaLs::create([
                    'sp2d_id'           => $sp2d->id,
                    'jenis_sp2d'        => 'GU',
                    'kode_rekening'     => $kode,
                    'uraian'            => $row['uraian'],
                    'jumlah'            => $row['nilai'],
                    'kode_kegiatan'     => $kodeKegiatan,
                    'nama_kegiatan'     => $namaKegiatan,
                    'kode_sub_kegiatan' => $kodeSub,
                    'nama_sub_kegiatan' => $namaSub,
                ]);
            }
        }
    }

    private function simpanSp2dLS(array $ls)
    {
        $h = $ls['header'];

        if (empty($h['nomor_sp_2_d'])) {
            throw new \Exception('Nomor SP2D LS kosong');
        }

        if (TbSp2d::where('nomor_sp2d', $h['nomor_sp_2_d'])->exists()) {
            throw new \Exception('SP2D LS sudah ada');
        }

        $sp2d = TbSp2d::create([
            'jenis' => 'LS',
            'nama_daerah' => $h['nama_daerah'],
            'tahun' => $h['tahun'],
            'nomor_rekening' => $h['nomor_rekening'],
            'nama_bank' => $h['nama_bank'],
            'nomor_sp2d' => $h['nomor_sp_2_d'],
            'tanggal_sp2d' => substr($h['tanggal_sp_2_d'], 0, 10),
            'nama_skpd' => $h['nama_skpd'],
            'nama_sub_skpd' => $h['nama_sub_skpd'],
            'nama_pihak_ketiga' => $h['nama_pihak_ketiga'],
            'no_rek_pihak_ketiga' => $h['no_rek_pihak_ketiga'],
            'nama_rek_pihak_ketiga' => $h['nama_rek_pihak_ketiga'],
            'bank_pihak_ketiga' => $h['bank_pihak_ketiga'],
            'npwp_pihak_ketiga' => $h['npwp_pihak_ketiga'],
            'keterangan_sp2d' => $h['keterangan_sp2d'],
            'nilai_sp2d' => $h['nilai_sp2d'],
            'nomor_spm' => $h['nomor_spm'],
            'tanggal_spm' => substr($h['tanggal_spm'], 0, 10),
        ]);

        // BELANJA
        foreach ($ls['detail_belanja'] ?? [] as $row) {
            TbBelanjaLs::create([
                'sp2d_id' => $sp2d->id,
                'jenis_sp2d'     => 'LS', // 🔥 INI WAJIB
                'kode_rekening' => $row['kode_rekening'],
                'uraian' => $row['uraian'],
                'total_anggaran' => $row['total_anggaran'],
                'jumlah' => $row['jumlah'],
            ]);
        }

        // PAJAK (❌ SKIP NILAI 0)
        foreach ($ls['pajak_potongan'] ?? [] as $row) {
            if ($row['nilai_sp2d_pajak_potongan'] <= 0) continue;

            TbPajakPotonganLs::create([
                'sp2d_id' => $sp2d->id,
                'id_pajak_potongan' => $row['id_pajak_potongan'],
                'nama_pajak_potongan' => $row['nama_pajak_potongan'],
                'id_billing' => $row['id_billing'],
                'nilai_sp2d_pajak_potongan' => $row['nilai_sp2d_pajak_potongan'],
            ]);
        }
    }

    public function hapus(Request $request)
    {
        TbSp2d::where('id',$request->id)->delete();

        return response()->json(['message' => 'SP2D berhasil dihapus!']);
    }

    public function restore(Request $request)
    {
        TbSp2d::withTrashed()->where('id',$request->id)->restore();

        return response()->json(['message' => 'SP2D berhasil direstore!']);
    }

    public function detail($id)
    {
        $sp2d = TbSp2d::find($id);

        if (!$sp2d) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $sp2d->belanjaLs = TbBelanjaLs::where('sp2d_id', $id)
            ->where('jenis_sp2d', $sp2d->jenis)
            ->get();

        $sp2d->pajakPotonganLs = TbPajakPotonganLs::where('sp2d_id', $id)->get();

        return response()->json($sp2d);
    }

    public function totalSp2dBulanan()
{
    $tahun = $this->tahunAktif;

    $data = TbSp2d::selectRaw('
            MONTH(tanggal_sp2d) as bulan,
            COUNT(id) as jumlah,
            SUM(nilai_sp2d) as total
        ')
        ->where('tahun', $tahun)
        ->groupBy('bulan')
        ->orderBy('bulan')
        ->get();

    return response()->json($data);
}

}
