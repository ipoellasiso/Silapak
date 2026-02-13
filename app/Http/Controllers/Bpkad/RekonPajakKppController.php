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
use App\Models\PelaporanPajak;
use Carbon\Carbon;

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
            'active_subpvertbp'         => 'active',
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
        $query = DB::table('tb_tbp as tbp')
            ->join('tb_potongangu as pot', 'tbp.id_tbp', '=', 'pot.id_tbp')

            // 🔗 JOIN KE SP2D VIA NO_SPM
            ->leftJoin('tb_sp2d as sp2d', function ($join) {
                $join->on(
                    DB::raw("TRIM(tbp.no_spm) COLLATE utf8mb4_unicode_ci"),
                    '=',
                    DB::raw("TRIM(sp2d.nomor_spm) COLLATE utf8mb4_unicode_ci")
                );
            })

            // ✅ STATUS GU VALID
            ->where('pot.status1', 'Terima')
            ->where('pot.status3', 'INPUT')
            ->where(function ($q) {
                $q->whereNull('pot.status4')
                ->orWhere('pot.status4', 'pending');
            });

        // 🔥 FILTER
        if ($request->tahun) {
            $query->whereYear('tbp.tanggal_tbp', $request->tahun);
        }

        if ($request->bulan) {
            $query->whereMonth('tbp.tanggal_tbp', $request->bulan);
        }

        if ($request->opd) {
            $query->where('tbp.nama_skpd', $request->opd);
        }

        $query->select(
            'pot.id',
            'tbp.nama_skpd',
            'tbp.no_spm',
            'tbp.nomor_tbp',
            'tbp.tanggal_tbp',

            // 🔥 DATA SP2D
            'sp2d.nomor_sp2d',
            'sp2d.tanggal_sp2d',

            'pot.nama_pajak_potongan',
            'pot.akun_pajak',
            'pot.nilai_tbp_pajak_potongan',
            'pot.id_billing',
            'pot.ntpn',
            'pot.status4'
        );

        return DataTables::of($query)
            ->addIndexColumn()

            // ✅ INI WAJIB ADA
            ->addColumn('pilih', function ($r) {
                if ($r->status4 === 'TERLAPOR') {
                    return '<input type="checkbox" class="chk-posting" value="'.$r->id.'">';
                }
                return '';
            })

            ->addColumn('spm_tbp', fn($r)=>"
                <b>SPM:</b> {$r->no_spm}<br>
                <b>TBP:</b> {$r->nomor_tbp}
            ")

            ->addColumn('pajak', fn($r)=>"
                <b>Ebilling:</b> {$r->id_billing}<br>
                <b>NTPN:</b> {$r->ntpn}
            ")

            ->addColumn('status', function ($r) {
                if ($r->status4 === 'POSTING') {
                    return '<span class="badge bg-success">FINAL</span>';
                }
                if ($r->status4 === 'TERLAPOR') {
                    return '<span class="badge bg-info">Sudah Lapor</span>';
                }
                return '<span class="badge bg-warning">Belum Lapor</span>';
            })

            // ✅ JANGAN LUPA
            ->rawColumns(['pilih','spm_tbp','pajak','status'])
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
            'p.status1',
            'p.status2' // ← WAJIB
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

            ->addColumn('status', function ($r) {
                if ($r->status2 === 'POSTING') {
                    return '<span class="badge bg-success">FINAL</span>';
                }
                return '<span class="badge bg-warning">Siap Rekon</span>';
            })

            ->addColumn('pilih', function ($r) {

                // BELUM FINAL → untuk POSTING
                if ($r->status2 !== 'POSTING') {
                    return '<input type="checkbox" 
                                class="chk-posting" 
                                data-mode="posting" 
                                value="'.$r->id.'">';
                }

                // SUDAH FINAL → untuk UNPOSTING
                return '<input type="checkbox" 
                            class="chk-unposting" 
                            data-mode="unposting" 
                            value="'.$r->id.'">';
            })

            ->rawColumns(['sp2d','pajak','status','pilih'])
            ->make(true);
    }

    /* =====================
     * POSTING FINAL KPP
     * ===================== */
    public function posting(Request $request)
    {
        $request->validate([
            'bulan' => 'required',
            'tahun' => 'required',
            'opd'   => 'nullable'
        ]);

        // ==========================
        // QUERY DATA LS SAJA
        // ==========================
        $query = DB::table('tb_sp2d as s')
            ->join('tb_pajak_potonganls as p', 'p.sp2d_id', '=', 's.id')
            ->where('s.tahun', $request->tahun)
            ->whereMonth('s.tanggal_sp2d', $request->bulan)

            // hanya yang siap diposting
            ->whereNotNull('p.ntpn')
            ->where(function ($q) {
                $q->whereNull('p.status2')
                ->orWhere('p.status2', '!=', 'POSTING');
            })

            // hanya pajak KPP
            ->where(function ($q) {
                $q->where('p.nama_pajak_potongan', 'LIKE', '%PPH%')
                ->orWhere('p.nama_pajak_potongan', 'LIKE', '%PPN%')
                ->orWhere('p.nama_pajak_potongan', 'LIKE', '%PASAL%');
            });

        if ($request->opd) {
            $query->where('s.nama_skpd', $request->opd);
        }

        $ids = $query->pluck('p.id');

        if ($ids->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data LS untuk posting'
            ], 422);
        }

        // ==========================
        // UPDATE POSTING
        // ==========================
        DB::table('tb_pajak_potonganls')
            ->whereIn('id', $ids)
            ->update([
                'status2'         => 'POSTING',
                'tanggal_posting' => now(),
                'posted_by'       => auth()->id(),
                'updated_at'      => now()
            ]);

        return response()->json([
            'message' => 'Posting FINAL LS berhasil ('.count($ids).' data)'
        ]);
    }

    public function postingSelect(Request $request)
    {
        $request->validate([
            'ids' => 'required|array'
        ]);

        $validIds = DB::table('tb_pajak_potonganls')
            ->whereIn('id', $request->ids)
            ->whereNotNull('ntpn')
            ->where(function ($q) {
                $q->whereNull('status2')
                ->orWhere('status2', '!=', 'POSTING');
            })
            ->pluck('id');

        if ($validIds->isEmpty()) {
            return response()->json([
                'message' => 'Data terpilih tidak valid atau sudah FINAL'
            ], 422);
        }

        DB::table('tb_pajak_potonganls')
            ->whereIn('id', $validIds)
            ->update([
                'status2'    => 'POSTING',
                'posted_by'  => auth()->id(),
                'updated_at' => now()
            ]);

        return response()->json([
            'message' => 'Posting FINAL (Select) berhasil ('.count($validIds).' data)'
        ]);
    }

    public function postingMassal(Request $request)
    {
        $request->validate([
            'bulan' => 'required',
            'tahun' => 'required',
            'opd'   => 'nullable'
        ]);

        $query = DB::table('tb_sp2d as s')
            ->join('tb_pajak_potonganls as p', 'p.sp2d_id', '=', 's.id')
            ->where('s.tahun', $request->tahun)
            ->whereMonth('s.tanggal_sp2d', $request->bulan)
            ->whereNotNull('p.ntpn')
            ->where(function ($q) {
                $q->whereNull('p.status2')
                ->orWhere('p.status2', '!=', 'POSTING');
            });

        if ($request->opd) {
            $query->where('s.nama_skpd', $request->opd);
        }

        $ids = $query->pluck('p.id');

        if ($ids->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data untuk posting massal'
            ], 422);
        }

        DB::table('tb_pajak_potonganls')
            ->whereIn('id', $ids)
            ->update([
                'status2'    => 'POSTING',
                'posted_by'  => auth()->id(),
                'updated_at' => now()
            ]);

        return response()->json([
            'message' => 'Posting FINAL Massal berhasil ('.count($ids).' data)'
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

    public function unPostingSelect(Request $request)
    {
        // 🔒 role check (opsional tapi disarankan)
        if (!in_array(auth()->user()->role, ['Admin','Verifikasi'])) {
            return response()->json([
                'message' => 'Tidak punya hak UnPosting'
            ], 403);
        }

        $request->validate([
            'ids' => 'required|array'
        ]);

        $validIds = DB::table('tb_pajak_potonganls')
            ->whereIn('id', $request->ids)
            ->where('status2', 'POSTING')
            ->pluck('id');

        if ($validIds->isEmpty()) {
            return response()->json([
                'message' => 'Data terpilih bukan FINAL'
            ], 422);
        }

        DB::table('tb_pajak_potonganls')
            ->whereIn('id', $validIds)
            ->update([
                'status2'    => "pending",
                'posted_by'  => null,
                'updated_at' => now()
            ]);

        return response()->json([
            'message' => 'UnPosting (Select) berhasil ('.count($validIds).' data)'
        ]);
    }

    public function unPostingMassal(Request $request)
    {
        // 🔒 role check
        if (!in_array(auth()->user()->role, ['Admin','Verifikasi'])) {
            return response()->json([
                'message' => 'Tidak punya hak UnPosting massal'
            ], 403);
        }

        $request->validate([
            'tahun' => 'required',
            'bulan' => 'required',
            'opd'   => 'nullable'
        ]);

        $query = DB::table('tb_sp2d as s')
            ->join('tb_pajak_potonganls as p', 'p.sp2d_id', '=', 's.id')
            ->where('s.tahun', $request->tahun)
            ->whereMonth('s.tanggal_sp2d', $request->bulan)
            ->where('p.status2', 'POSTING');

        if ($request->opd) {
            $query->where('s.nama_skpd', $request->opd);
        }

        $ids = $query->pluck('p.id');

        if ($ids->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data FINAL untuk UnPosting'
            ], 422);
        }

        DB::table('tb_pajak_potonganls')
            ->whereIn('id', $ids)
            ->update([
                'status2'    => "pending",
                'posted_by'  => null,
                'updated_at' => now()
            ]);

        return response()->json([
            'message' => 'UnPosting Massal berhasil ('.count($ids).' data)'
        ]);
    }

    /* =====================
    * PELAPORAN PAJAK KE KPPN (LS)
    * ===================== */
    public function pelaporanPajak(Request $request)
    {
        $request->validate([
            'mode'         => 'required|in:selected,filter',
            'ids'          => 'nullable|array',
            'bulan_lapor'  => 'required|integer',
            'tahun_lapor'  => 'required|integer',
            'bulan'        => 'nullable|integer',
            'tahun'        => 'nullable|integer',
            'opd'          => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {

            $query = DB::table('tb_pajak_potonganls as p')
                ->join('tb_sp2d as s', 's.id', '=', 'p.sp2d_id')
                ->where('p.status2', 'pending')
                ->whereNotNull('p.ntpn');

            // ===============================
            // MODE SELECTED
            // ===============================
            if ($request->mode === 'selected') {
                if (empty($request->ids)) {
                    return response()->json(['message'=>'Tidak ada data dipilih'],422);
                }
                $query->whereIn('p.id', $request->ids);
            }

            // ===============================
            // MODE FILTER
            // ===============================
            if ($request->mode === 'filter') {

                if ($request->bulan) {
                    $query->whereMonth('s.tanggal_sp2d', $request->bulan);
                }

                if ($request->tahun) {
                    $query->whereYear('s.tanggal_sp2d', $request->tahun);
                }

                if ($request->opd) {
                    $query->where('s.nama_skpd', $request->opd);
                }
            }

            $pajaks = $query->select(
                'p.*',
                's.tanggal_sp2d'
            )->get();

            if ($pajaks->isEmpty()) {
                return response()->json([
                    'message' => 'Data tidak ditemukan atau sudah dilaporkan'
                ],422);
            }

            foreach ($pajaks as $p) {

                $tglSp2d = Carbon::parse($p->tanggal_sp2d);

                // ❌ cegah dobel
                if (PelaporanPajak::where('sumber_id',$p->id)
                    ->where('sumber_pajak','LS')->exists()) {
                    continue;
                }

                PelaporanPajak::create([
                    'sumber_pajak'     => 'LS',
                    'sumber_id'        => $p->id,
                    'jenis_pajak'      => $p->nama_pajak_potongan,
                    'akun_pajak'       => $p->akun_pajak,

                    // ✅ MASA PAJAK = SP2D
                    'masa_pajak_bulan' => $tglSp2d->month,
                    'masa_pajak_tahun' => $tglSp2d->year,

                    // ✅ MASA LAPOR = BULAN SEKARANG / FILTER
                    'masa_lapor_bulan' => $request->bulan_lapor,
                    'masa_lapor_tahun' => $request->tahun_lapor,

                    'nilai_pajak'      => $p->nilai_sp2d_pajak_potongan,
                    'id_billing'       => $p->id_billing,
                    'ntpn'             => $p->ntpn,

                    'tanggal_lapor'    => now(),
                    'status_lapor'     => 'TERLAPOR',
                    'lapor_by'         => auth()->id(),
                ]);

                DB::table('tb_pajak_potonganls')
                    ->where('id', $p->id)
                    ->update(['status1'=>'sudah']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pelaporan pajak berhasil ('.count($pajaks).' data)'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message'=>'Gagal pelaporan',
                'error'=>$e->getMessage()
            ],500);
        }
    }

    public function pelaporanPajakGu(Request $request)
    {
        $request->validate([
            'mode'        => 'required|in:selected,filter',
            'ids'         => 'nullable|array',
            'bulan_lapor' => 'required|integer',
            'tahun_lapor' => 'required|integer',
            'bulan'       => 'nullable|integer',
            'tahun'       => 'nullable|integer',
            'opd'         => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {

            $query = DB::table('tb_potongangu as p')
                ->join('tb_tbp as t', 't.id_tbp', '=', 'p.id_tbp')
                ->where('p.status1', 'Terima')
                ->where('p.status3', 'INPUT')
                ->whereNotNull('p.ntpn')
                ->where(function ($q) {
                    $q->whereNull('p.status4')
                    ->orWhere('p.status4', 'pending');
                });

            if ($request->mode === 'selected') {
                $query->whereIn('p.id', $request->ids);
            }

            if ($request->mode === 'filter') {
                if ($request->bulan) {
                    $query->whereMonth('t.tanggal_tbp', $request->bulan);
                }
                if ($request->tahun) {
                    $query->whereYear('t.tanggal_tbp', $request->tahun);
                }
                if ($request->opd) {
                    $query->where('t.nama_skpd', $request->opd);
                }
            }

            $data = $query->select('p.*','t.tanggal_tbp','t.nama_skpd')->get();

            if ($data->isEmpty()) {
                return response()->json(['message'=>'Data tidak ditemukan'],422);
            }

            foreach ($data as $p) {

                if (PelaporanPajak::where('sumber_pajak','GU')
                    ->where('sumber_id',$p->id)->exists()) {
                    continue;
                }

                $tgl = Carbon::parse($p->tanggal_tbp);

                PelaporanPajak::create([
                    'sumber_pajak'     => 'GU',
                    'sumber_id'        => $p->id,
                    'opd'              => $p->nama_skpd,
                    'jenis_pajak'      => $p->nama_pajak_potongan,
                    'akun_pajak'       => $p->akun_pajak,
                    'masa_pajak_bulan' => $tgl->month,
                    'masa_pajak_tahun' => $tgl->year,
                    'masa_lapor_bulan' => $request->bulan_lapor,
                    'masa_lapor_tahun' => $request->tahun_lapor,
                    'nilai_pajak'      => $p->nilai_tbp_pajak_potongan,
                    'id_billing'       => $p->id_billing,
                    'ntpn'             => $p->ntpn,
                    'tanggal_lapor'    => now(),
                    'status_lapor'     => 'TERLAPOR',
                    'lapor_by'         => auth()->id(),
                ]);

                DB::table('tb_potongangu')
                    ->where('id', $p->id)
                    ->update(['status4'=>'TERLAPOR']);
            }

            DB::commit();

            return response()->json([
                'message'=>'Pelaporan Pajak GU berhasil ('.count($data).' data)'
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message'=>$e->getMessage()],500);
        }
    }

    public function postingGuSelect(Request $request)
    {
        $request->validate([
            'ids'=>'required|array'
        ]);

        $ids = DB::table('tb_potongangu')
            ->whereIn('id',$request->ids)
            ->where('status4','TERLAPOR')
            ->pluck('id');

        if ($ids->isEmpty()) {
            return response()->json(['message'=>'Data belum dilaporkan'],422);
        }

        DB::table('tb_potongangu')
            ->whereIn('id',$ids)
            ->update(['status4'=>'POSTING']);

        return response()->json([
            'message'=>'Posting FINAL GU berhasil ('.count($ids).' data)'
        ]);
    }

    public function postingGuMassal(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer',
            'bulan' => 'required|integer',
            'opd'   => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {

            $query = DB::table('tb_potongangu as p')
                ->join('tb_tbp as t', 't.id_tbp', '=', 'p.id_tbp')

                // SYARAT WAJIB GU
                ->where('p.status1', 'Terima')
                ->where('p.status3', 'INPUT')
                ->whereNotNull('p.ntpn')
                ->where(function ($q) {
                    $q->whereNull('p.status4')
                    ->orWhere('p.status4', 'pending');
                })

                // FILTER PERIODE
                ->whereYear('t.tanggal_tbp', $request->tahun)
                ->whereMonth('t.tanggal_tbp', $request->bulan);

            if ($request->opd) {
                $query->where('t.nama_skpd', $request->opd);
            }

            $ids = $query->pluck('p.id');

            if ($ids->isEmpty()) {
                return response()->json([
                    'message' => 'Tidak ada data GU untuk posting'
                ], 422);
            }

            DB::table('tb_potongangu')
                ->whereIn('id', $ids)
                ->update([
                    'status4'    => 'POSTING',
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'message' => 'Posting GU Massal berhasil ('.count($ids).' data)'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'message' => 'Gagal posting GU massal',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

}
