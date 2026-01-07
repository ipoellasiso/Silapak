<?php

namespace App\Http\Controllers\Bpkad;

use App\Http\Controllers\Controller;
use App\Models\TbPotonganGu;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\RekonPajakKppExport;
use Illuminate\Support\Facades\Cache;

class RekonPajakKppController extends Controller
{
    protected int $tahunAktif;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->tahunAktif = auth()->user()->tahun;
            return $next($request);
        });
    }

    /* =====================
     * HALAMAN UTAMA
     * ===================== */
    public function index()
    {
        $userId = Auth::id();

        $data = [
            'title'                     => 'Rekon Pajak KPP',
            'active_pengeluaranvertbp'  => 'active',
            'active_siderekonpajak'     => 'active',
            'active_siderekonpajakgu'   => 'active',
            'breadcumd'                 => 'Penatausahaan',
            'breadcumd1'                => 'Pengelauran',
            'breadcumd2'                => 'Rekon Pajak KPP',
            'userx' => UserModel::where('id',$userId)
                        ->first(['fullname','role','gambar']),
            'listOpd' => DB::table('opd')
                        ->orderBy('nama_opd')
                        ->get(),
        ];

        return view('bpkad.rekon_pajak_kpp.index', $data);
    }

    /* =====================
     * DATA REKON (DATATABLE)
     * ===================== */
    public function data(Request $request)
    {
        $sp2d = Cache::remember('sp2d_api', now()->addMinutes(30), function () {
            try {
                return collect(
                    Http::get('http://127.0.0.1:8001/api/sp2d')->json()
                )->keyBy(fn($i) => trim($i['nomor_spm']));
            } catch (\Exception $e) {
                return collect();
            }
        });

        $query = DB::table('tb_tbp as tbp')
            ->join('tb_potongangu as pot','tbp.id_tbp','=','pot.id_tbp')
            ->whereYear('tbp.tanggal_tbp', $this->tahunAktif)
            ->whereRaw('UPPER(pot.status3) = "INPUT"');

        if ($request->opd) {
            $query->where('tbp.nama_skpd', $request->opd);
        }

        if ($request->bulan) {
            $query->whereMonth('tbp.tanggal_tbp', $request->bulan);
        }

        if ($request->tahun) {
            $query->whereYear('tbp.tanggal_tbp', $request->tahun);
        }

        // 🔥 FILTER SUDAH SP2D
        $sp2d = $this->getSp2dCache();
        $sp2dBySpm = $sp2d->keyBy(fn($i)=>trim($i['nomor_spm']));

        $query->select(
            'pot.id',
            'tbp.no_spm',
            'tbp.nama_skpd',
            'tbp.nomor_tbp',
            'pot.nama_pajak_potongan',
            'pot.akun_pajak',
            'pot.nilai_tbp_pajak_potongan',
            'pot.ntpn',
            'pot.id_billing',
            'pot.status4'
        );

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('sp2d', fn($r)=>"
                <b>SPM:</b> {$r->no_spm}<br>
                <b>TBP:</b> {$r->nomor_tbp}
            ")
            ->addColumn('pajak', fn($r)=>"
                <b>Ebilling:</b> {$r->id_billing}<br>
                <b>NTPN:</b> {$r->ntpn}
            ")
            ->addColumn('sp2d_info', function ($r) use ($sp2dBySpm) {

                $key = trim($r->no_spm);

                if (!isset($sp2dBySpm[$key])) {
                    return '<span class="badge bg-danger">Belum SP2D</span>';
                }

                return '
                    <div>
                        <b>Tgl:</b> '.$sp2dBySpm[$key]['tanggal_sp2d'].'<br>
                        <b>No:</b> '.$sp2dBySpm[$key]['nomor_sp2d'].'
                    </div>
                ';
            })
            ->addColumn('status', function ($r) {
                if ($r->status4 === 'POSTING') {
                    return '
                        <span class="badge bg-success mb-1">FINAL</span><br>
                        <button class="btn btn-sm btn-danger btn-unposting"
                            data-id="'.$r->id.'">
                            UnPosting
                        </button>
                    ';
                }

                return '<span class="badge bg-warning">Siap Rekon</span>';
            })
            ->rawColumns(['sp2d','sp2d_info','status', 'pajak'])
            ->make(true);
    }

    /* =====================
     * POSTING FINAL KPP
     * ===================== */
    public function posting(Request $request)
    {
        $request->validate([
            'bulan' => 'required',
            'opd'   => 'nullable'
        ]);

        $query = DB::table('tb_potongangu as pot')
            ->join('tb_tbp as tbp','tbp.id_tbp','=','pot.id_tbp')
            ->whereYear('tbp.tanggal_tbp', $this->tahunAktif)
            ->whereMonth('tbp.tanggal_tbp', $request->bulan)
            ->where('pot.status3','INPUT')
            ->whereNull('pot.status4')
            ->whereNotNull('pot.ntpn');

        if ($request->opd) {
            $query->where('tbp.nama_skpd', $request->opd);
        }

        $ids = $query->pluck('pot.id');

        if ($ids->isEmpty()) {
            return response()->json(['message'=>'Tidak ada data untuk posting'],422);
        }

        TbPotonganGu::whereIn('id',$ids)->update([
            'status4' => 'POSTING',
            'tanggal_posting' => now(),
            'posted_by' => auth()->id(),
            'log_posting' => 'Posting Rekonsiliasi KPP'
        ]);

        return response()->json([
            'message'=>'Posting FINAL berhasil ('.count($ids).' data)'
        ]);
    }

    /* =====================
     * EXPORT EXCEL KPP
     * ===================== */
    public function export(Request $request)
    {
        $request->validate([
            'tahun' => 'required',
            'bulan' => 'nullable',
            'opd'   => 'nullable'
        ]);

        return Excel::download(
            new RekonPajakKppExport(
                $request->tahun,
                $request->bulan,
                $request->opd
            ),
            'REKON_PAJAK_KPP_'.$request->tahun.'.xlsx'
        );
    }

    private function getSp2dCache()
    {
        return Cache::remember('sp2d_api_cache', 300, function () {
            return collect(
                Http::get('http://127.0.0.1:8001/api/sp2d')->json()
            );
        });
    }

    public function unPosting(Request $request)
    {
        $request->validate([
            'id' => 'required'
        ]);

        $pajak = TbPotonganGu::findOrFail($request->id);

        // 🔒 VALIDASI
        if ($pajak->status4 !== 'POSTING') {
            return response()->json([
                'message' => 'Data belum FINAL'
            ], 422);
        }

        // (opsional) cek role
        if (!in_array(auth()->user()->role, ['Admin','Verifikasi'])) {
            return response()->json([
                'message' => 'Tidak punya hak UnPosting'
            ], 403);
        }

        // 🔄 UNPOSTING
        $pajak->update([
            'status4' => null,
            'tanggal_posting' => null,
            'posted_by' => null,
            'log_posting' => 'UNPOSTING oleh '.auth()->user()->fullname,
        ]);

        return response()->json([
            'message' => 'UnPosting berhasil'
        ]);
    }

    public function unPostingMassal(Request $request)
    {
        $request->validate([
            'tahun' => 'required',
            'bulan' => 'required',
            'opd'   => 'nullable'
        ]);

        // 🔒 Cek role (opsional tapi disarankan)
        if (!in_array(auth()->user()->role, ['Admin','Verifikasi'])) {
            return response()->json([
                'message' => 'Tidak punya hak UnPosting massal'
            ], 403);
        }

        $query = DB::table('tb_potongangu as pot')
            ->join('tb_tbp as tbp','tbp.id_tbp','=','pot.id_tbp')
            ->where('pot.status4','POSTING')
            ->whereYear('tbp.tanggal_tbp', $request->tahun)
            ->whereMonth('tbp.tanggal_tbp', $request->bulan);

        if ($request->opd) {
            $query->where('tbp.nama_skpd', $request->opd);
        }

        $ids = $query->pluck('pot.id');

        if ($ids->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data FINAL untuk di-UnPosting'
            ], 422);
        }

        TbPotonganGu::whereIn('id', $ids)->update([
            'status4' => null,
            'tanggal_posting' => null,
            'posted_by' => null,
            'log_posting' => 'UNPOSTING MASSAL oleh '.auth()->user()->fullname,
            'updated_at' => now()
        ]);

        return response()->json([
            'message' => 'UnPosting massal berhasil ('.count($ids).' data)'
        ]);
    }


}
