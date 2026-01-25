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
use App\Models\TbSp2d;

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
            'active_siderekonpajak1'    => 'active',
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

     /* =========================
     * SWITCH GU / LS
     * ========================= */
    public function data(Request $request)
    {
        return $request->jenis === 'LS'
            ? $this->dataLs($request)
            : $this->dataGu($request);
    }

    /* =========================
     * DATA GU
     * ========================= */
    private function dataGu(Request $request)
    {
        $sp2dBySpm = Cache::remember(
            'sp2d_gu_'.$this->tahunAktif,
            600,
            fn() => TbSp2d::where('tahun',$this->tahunAktif)
                ->get()
                ->keyBy(fn($i)=>trim($i->nomor_spm))
        );

        $query = DB::table('tb_tbp as tbp')
            ->join('tb_potongangu as pot','tbp.id_tbp','=','pot.id_tbp')
            ->whereYear('tbp.tanggal_tbp',$this->tahunAktif)
            ->where('pot.status3','INPUT')

            // ✅ HANYA STATUS GU YANG DIINGINKAN
            ->where('pot.status3', 'INPUT')
            ->where(function ($q) {
                $q->where('pot.status1', 'Terima')
                ->orWhere('pot.status1', 'TERIMA')
                ->orWhere('pot.status1', 'terima');
            });

        if ($request->opd)   $query->where('tbp.nama_skpd',$request->opd);
        if ($request->bulan) $query->whereMonth('tbp.tanggal_tbp',$request->bulan);

        $query->select(
            'pot.id',
            'tbp.no_spm',
            'tbp.nama_skpd',
            'tbp.nomor_tbp',
            'pot.nama_pajak_potongan',
            'pot.akun_pajak',
            'pot.nilai_tbp_pajak_potongan',
            'pot.id_billing',
            'pot.ntpn',
            'pot.status4'
        );

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('sp2d', fn($r)=>"
                <b>SPM:</b> $r->no_spm<br>
                <b>TBP:</b> $r->nomor_tbp
            ")

            ->addColumn('sp2d_info', function ($r) use ($sp2dBySpm) {
                if (!isset($sp2dBySpm[$r->no_spm])) {
                    return '<span class="badge bg-danger">Belum SP2D</span>';
                }
                return '
                    <b>No:</b> '.$sp2dBySpm[$r->no_spm]->nomor_sp2d.'<br>
                    <b>Tgl:</b> '.$sp2dBySpm[$r->no_spm]->tanggal_sp2d;
            })

            ->addColumn('pajak', fn($r)=>"
                <b>Ebilling:</b> $r->id_billing<br>
                <b>NTPN:</b> $r->ntpn
            ")

            ->addColumn('status', function ($r) {
                if ($r->status4 === 'POSTING') {
                    return '<span class="badge bg-success">FINAL</span>';
                }
                return '<span class="badge bg-warning">Siap Rekon</span>';
            })

            ->rawColumns(['sp2d','sp2d_info','pajak','status'])
            ->make(true);
    }

    /* =========================
     * DATA LS
     * ========================= */
    private function dataLs(Request $request)
    {
        $query = DB::table('tb_sp2d as s')
            ->join('tb_pajak_potonganls as p', 'p.sp2d_id', '=', 's.id')
            ->where('s.tahun', $this->tahunAktif)

            // 🔥 FILTER PAJAK KPP SAJA
            ->where(function ($q) {
                $q->where('p.nama_pajak_potongan', 'LIKE', '%PPH 21%')
                ->orWhere('p.nama_pajak_potongan', 'LIKE', '%PAJAK PERTAMBAHAN NILAI%')
                ->orWhere('p.nama_pajak_potongan', 'LIKE', '%PPN%')
                ->orWhere('p.nama_pajak_potongan', 'LIKE', '%PS 22%')
                ->orWhere('p.nama_pajak_potongan', 'LIKE', '%PASAL 22%')
                ->orWhere('p.nama_pajak_potongan', 'LIKE', '%PS 23%')
                ->orWhere('p.nama_pajak_potongan', 'LIKE', '%PASAL 23%')
                ->orWhere('p.nama_pajak_potongan', 'LIKE', '%4(2)%')
                ->orWhere('p.nama_pajak_potongan', 'LIKE', '%PASAL 4%');
            })

            // ✅ HANYA YANG SUDAH
            ->where(function ($q) {
                $q->where('p.status1', 'sudah')
                ->orWhere('p.status1', 'SUDAH')
                ->orWhere('p.status1', 'Sudah');
            });

        if ($request->opd) {
            $query->where('s.nama_skpd', $request->opd);
        }

        if ($request->bulan) {
            $query->whereMonth('s.tanggal_sp2d', $request->bulan);
        }

        $query->select(
            'p.id',
            's.nama_skpd',
            's.nomor_sp2d',
            's.tanggal_sp2d',
            'p.nama_pajak_potongan',
            'p.akun_pajak',
            'p.nilai_sp2d_pajak_potongan',
            'p.id_billing',
            'p.ntpn',
            'p.status1'
        );

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('sp2d', fn($r)=>"
                <b>No:</b> {$r->nomor_sp2d}<br>
                <b>Tgl:</b> {$r->tanggal_sp2d}
            ")

            ->addColumn('pajak', fn($r)=>"
                <b>Ebilling:</b> {$r->id_billing}<br>
                <b>NTPN:</b> {$r->ntpn}
            ")

            ->addColumn('status', fn($r)=>
                $r->status1 === 'POSTING'
                    ? '<span class="badge bg-success">FINAL</span>'
                    : '<span class="badge bg-warning">Siap Rekon</span>'
            )

            ->rawColumns(['sp2d','pajak','status'])
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
