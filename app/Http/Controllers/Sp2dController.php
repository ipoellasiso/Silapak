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
            $data = TbSp2d::where('tahun', $this->tahunAktif)
                ->where('jenis', 'GU')
                ->where('nama_skpd', auth()->user()->nama_opd)
                ->whereNull('deleted_at')
                ->select('id','nomor_sp2d','nama_skpd','nama_pihak_ketiga','nilai_sp2d','tanggal_sp2d');

            return datatables()->of($data)
                ->addIndexColumn()
                ->editColumn('nilai_sp2d', fn($r) => number_format($r->nilai_sp2d))
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

        if (!$json) {
            return response()->json(['success' => false, 'message' => 'JSON tidak valid!'], 422);
        }

        if (!isset($json['ls']) || !isset($json['ls']['header'])) {
            return response()->json(['success' => false, 'message' => 'Tidak ditemukan data SP2D LS dalam JSON!'], 422);
        }

        $header = $json['ls']['header'];

        // Cek duplikat SP2D
        $cek = TbSp2d::where('nomor_sp2d', $header['nomor_sp_2_d'])
            ->where('tahun', $header['tahun'])
            ->first();

        if ($cek) {
            return response()->json([
                'success' => false,
                'message' => 'SP2D sudah pernah diinput!'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $sp2d = TbSp2d::create([
                'jenis' => 'LS',
                'nama_daerah' => $header['nama_daerah'],
                'tahun' => $header['tahun'],
                'nomor_rekening' => $header['nomor_rekening'],
                'nama_bank' => $header['nama_bank'],
                'nomor_sp2d' => $header['nomor_sp_2_d'],
                'tanggal_sp2d' => substr($header['tanggal_sp_2_d'], 0, 10),
                'nama_skpd' => $header['nama_skpd'],
                'nama_sub_skpd' => $header['nama_sub_skpd'],
                'nama_pihak_ketiga' => $header['nama_pihak_ketiga'],
                'no_rek_pihak_ketiga' => $header['no_rek_pihak_ketiga'],
                'nama_rek_pihak_ketiga' => $header['nama_rek_pihak_ketiga'],
                'bank_pihak_ketiga' => $header['bank_pihak_ketiga'],
                'npwp_pihak_ketiga' => $header['npwp_pihak_ketiga'],
                'keterangan_sp2d' => $header['keterangan_sp2d'],
                'nilai_sp2d' => $header['nilai_sp2d'],
                'cabang_bank' => $header['cabang_bank'],
                'nomor_spm' => $header['nomor_spm'],
                'tanggal_spm' => substr($header['tanggal_spm'], 0, 10),
                'nama_ibu_kota' => $header['nama_ibu_kota'],
                'nama_bud_kbud' => $header['nama_bud_kbud'],
                'nip_bud_kbud' => $header['nip_bud_kbud'],
                'jabatan_bud_kbud' => $header['jabatan_bud_kbud'],
            ]);

            foreach ($json['ls']['detail_belanja'] as $row) {
                TbBelanjaLs::create([
                    'sp2d_id' => $sp2d->id,
                    'kode_rekening' => $row['kode_rekening'],
                    'uraian' => $row['uraian'],
                    'total_anggaran' => $row['total_anggaran'],
                    'jumlah' => $row['jumlah'],
                ]);
            }

            foreach ($json['ls']['pajak_potongan'] as $row) {
                TbPajakPotonganLs::create([
                    'sp2d_id' => $sp2d->id,
                    'id_pajak_potongan' => $row['id_pajak_potongan'],
                    'nama_pajak_potongan' => $row['nama_pajak_potongan'],
                    'kode_sinergi' => $row['kode_sinergi'],
                    'nama_sinergi' => $row['nama_sinergi'],
                    'id_billing' => $row['id_billing'],
                    'nilai_sp2d_pajak_potongan' => $row['nilai_sp2d_pajak_potongan'],
                ]);
            }

            DB::commit();
            return response()->json(['success' => true], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
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
        $sp2d = TbSp2d::with([
            'belanjaLs',
            'pajakPotonganLs'
        ])->where('id', $id)->first();

        if (!$sp2d) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($sp2d);
    }

}
