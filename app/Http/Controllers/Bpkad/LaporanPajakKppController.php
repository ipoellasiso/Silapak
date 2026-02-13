<?php

namespace App\Http\Controllers\Bpkad;

use App\Http\Controllers\Controller;
use App\Models\TbPotonganGu;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Exports\LaporanPajakKppExport;
use App\Models\TbSp2d;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class LaporanPajakKppController extends Controller
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
            'title'                     => 'Data Pajak GU',
            'active_pengeluaranvertbp'  => 'active',
            'active_subpvertbp'         => 'active',
            'active_sidedatapajak'      => 'active',
            'active_sidedatapajakgu'    => 'active',
            'breadcumd'                 => 'Penatausahaan',
            'breadcumd1'                => 'Pengelauran',
            'breadcumd2'                => 'Data Pajak GU',
            'userx' => UserModel::where('id',$userId)
                        ->first(['fullname','role','gambar']),
            'listOpd' => DB::table('opd')
                        ->orderBy('nama_opd')
                        ->get(),
            'akun_pajak' => DB::table('tb_akun_pajak')
                            ->where('status', 'AKTIF')
                            ->orderBy('kode_akun')
                            ->get(),
        ];

        return view('bpkad.laporan_pajak_kpp.index', $data);
    }

    public function data(Request $request)
    {
        // 🔹 Ambil SP2D
        $sp2d = $this->getSp2dCache();
        $sp2dBySpm = $sp2d->keyBy(fn($i)=>trim($i['nomor_spm']));

        // 🔹 QUERY UTAMA (JOIN SESUAI RELASI DB)
        $query = DB::table('tb_tbp as tbp')
            ->join('tb_potongangu as pot','tbp.id_tbp','=','pot.id_tbp')
            ->where('pot.status3', 'INPUT')
            ->select(
                'tbp.no_spm',
                'tbp.tanggal_tbp',
                'pot.rek_belanja',
                'pot.akun_pajak',
                'pot.nama_pajak_potongan as jenis_pajak',
                'pot.no_npwp',
                'pot.nama_npwp',
                'pot.nilai_tbp_pajak_potongan as nilai_pajak',
                'pot.id_billing',
                'pot.ntpn'
            );

        return DataTables::of($query)

            ->filterColumn('nilai_pajak', function ($query, $keyword) {
                $query->where('pot.nilai_tbp_pajak_potongan', 'like', "%{$keyword}%");
            })

            // 🔹 SP2D (API)
            ->addColumn('tanggal_sp2d', function ($r) use ($sp2dBySpm) {
                return $sp2dBySpm[trim($r->no_spm)]['tanggal_sp2d'] ?? '-';
            })
            ->addColumn('nomor_sp2d', function ($r) use ($sp2dBySpm) {
                return $sp2dBySpm[trim($r->no_spm)]['nomor_sp2d'] ?? '-';
            })
            ->addColumn('nilai_sp2d', function ($r) use ($sp2dBySpm) {
                return number_format(
                    $sp2dBySpm[trim($r->no_spm)]['nilai_sp2d'] ?? 0
                );
            })

            // 🔹 SAFETY NULL
            ->editColumn('rek_belanja', fn($r) => $r->rek_belanja ?? '-')
            ->editColumn('akun_pajak', fn($r) => $r->akun_pajak ?? '-')
            ->editColumn('jenis_pajak', fn($r) => $r->jenis_pajak ?? '-')
            ->editColumn('no_npwp', fn($r) => $r->no_npwp ?? '-')
            ->editColumn('nama_npwp', fn($r) => $r->nama_npwp ?? '-')
            ->editColumn('nilai_pajak', fn($r) => number_format($r->nilai_pajak ?? 0))
            ->editColumn('id_billing', fn($r) => $r->id_billing ?? '-')
            ->editColumn('ntpn', fn($r) => $r->ntpn ?? '-')

            ->make(true);
    }

    /* =====================================================
     * BASE QUERY
     * ===================================================== */
    private function baseQuery(Request $request)
    {
        $query = DB::table('tb_tbp as tbp')
            ->join('tb_potongangu as pot','tbp.id_tbp','=','pot.id_tbp')
            ->whereYear('tbp.tanggal_tbp', $this->tahunAktif)
            ->where('pot.status3','INPUT');

        if ($request->opd) {
            $query->where('tbp.nama_skpd', $request->opd);
        }

        if ($request->tahun) {
            $query->whereYear('tbp.tanggal_tbp', $request->tahun);
        }

        if ($request->bulan) {
            $query->whereMonth('tbp.tanggal_tbp', $request->bulan);
        }

        return $query->select(
            'pot.id',
            'pot.status4',
            'pot.bukti_setoran',
            'tbp.no_spm',
            'tbp.nomor_tbp',
            'tbp.nama_skpd',
            'tbp.tanggal_tbp',
            'pot.rek_belanja',
            'pot.akun_pajak',
            'pot.nama_pajak_potongan as jenis_pajak',
            'pot.no_npwp',
            'pot.nama_npwp',
            'pot.nilai_tbp_pajak_potongan as nilai_pajak',
            'pot.id_billing',
            'pot.ntpn'
        );
    }

    /* =====================================================
     * DATA SUDAH SP2D
     * ===================================================== */
    public function dataSudahSp2d(Request $request)
    {
        $query = DB::table('tb_tbp as tbp')
            ->join('tb_potongangu as pot','tbp.id_tbp','=','pot.id_tbp')
            ->join('tb_sp2d as sp2d', function ($join) {
                $join->on(
                    DB::raw('sp2d.nomor_spm COLLATE utf8mb4_general_ci'),
                    '=',
                    DB::raw('tbp.no_spm COLLATE utf8mb4_general_ci')
                );
            })
            ->where('pot.status3', 'INPUT');

        // ✅ FILTER OPD
        if ($request->opd) {
            $query->where('tbp.nama_skpd', $request->opd);
        }

        // ✅ FILTER TAHUN & BULAN PAKAI TBP
        if ($request->tahun) {
            $query->whereYear('tbp.tanggal_tbp', $request->tahun);
        }

        if ($request->bulan) {
            $query->whereMonth('tbp.tanggal_tbp', $request->bulan);
        }

        $query->select(
            'pot.id',
            'pot.status4',
            'tbp.no_spm',
            'tbp.tanggal_tbp',
            'sp2d.tanggal_sp2d',
            'sp2d.nomor_sp2d',
            'sp2d.nilai_sp2d',
            'pot.nama_pajak_potongan as jenis_pajak',
            'pot.akun_pajak',
            'pot.id_billing',
            'pot.ntpn',
            'pot.nilai_tbp_pajak_potongan as nilai_pajak'
        );

        return DataTables::of($query)

        // 🔥 INI LETAKNYA (WAJIB DI SINI)
        ->filter(function ($query) use ($request) {
            if ($search = $request->input('search.value')) {
                $query->where(function ($q) use ($search) {
                    $q->where('pot.ntpn', 'like', "%{$search}%")
                    ->orWhere('pot.id_billing', 'like', "%{$search}%")
                    ->orWhere('tbp.no_spm', 'like', "%{$search}%")
                    ->orWhere('sp2d.nomor_sp2d', 'like', "%{$search}%");
                });
            }
        })

        ->addIndexColumn()
        ->editColumn('nilai_sp2d', fn($r)=>number_format($r->nilai_sp2d))
        ->editColumn('nilai_pajak', fn($r)=>number_format($r->nilai_pajak))

        ->addColumn('pajak', fn($r)=>'
            <div>
                <span class="badge bg-info">Jenis</span> '.$r->jenis_pajak.'<br>
                <span class="badge bg-secondary">Akun</span> '.$r->akun_pajak.'<br>
                <span class="badge bg-warning text-dark">Billing</span> '.$r->id_billing.'<br>
                <span class="badge bg-success">NTPN</span> '.$r->ntpn.'
            </div>
        ')

        ->addColumn('aksi', function ($r) {
            return $r->status4 === 'POSTING'
                ? '<span class="badge bg-success">POSTING</span>'
                : '<button class="btn btn-sm btn-primary btn-edit" data-id="'.$r->id.'">
                        <i class="fas fa-edit"></i>
                </button>';
        })

        ->rawColumns(['pajak','aksi'])
        ->make(true);
    }

    /* =====================================================
     * DATA BELUM SP2D
     * ===================================================== */
    public function dataBelumSp2d(Request $request)
    {
        $spmSudahSp2d = TbSp2d::where('tahun', $this->tahunAktif)
            ->pluck('nomor_spm')
            ->map(fn($v)=>trim($v));

        $query = $this->baseQuery($request)
            ->whereNotIn('tbp.no_spm', $spmSudahSp2d);

        return DataTables::of($query)
            ->addIndexColumn()

            ->editColumn('nilai_pajak', fn($r)=>number_format($r->nilai_pajak))

            ->addColumn('status_sp2d', fn()=>'
                <span class="badge bg-danger">❌ Belum SP2D</span>
            ')

            ->addColumn('tbp', fn($r)=>'
                <strong>SKPD</strong><br>'.$r->nama_skpd.'<br>
                <strong>TBP</strong><br>'.($r->nomor_tbp ?? '-').'
            ')

            ->addColumn('aksi', function ($r) {

                $edit = $r->status4 !== 'POSTING'
                    ? '<button class="btn btn-sm btn-primary btn-edit" data-id="'.$r->id.'">
                        <i class="fas fa-edit"></i></button>'
                    : '';

                $view = $r->bukti_setoran
                    ? '<a href="'.asset('storage/'.$r->bukti_setoran).'"
                        target="_blank"
                        class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i></a>'
                    : '';

                return '<div class="d-flex gap-1">'.$edit.$view.'</div>';
            })

            ->rawColumns(['status_sp2d','tbp','aksi'])
            ->make(true);
    }

    /* =====================================================
     * Posting Massal
     * ===================================================== */
    public function postingMassal(Request $request)
    {
        $request->validate([
            'tahun' => 'required',
            'bulan' => 'required',
            'opd'   => 'nullable'
        ]);

        $query = DB::table('tb_potongangu')
            ->join('tb_tbp','tb_tbp.id_tbp','=','tb_potongangu.id_tbp')
            ->where('tb_potongangu.status3','INPUT')
            ->whereNull('tb_potongangu.status4')
            ->whereYear('tb_tbp.tanggal_tbp', $request->tahun)
            ->whereMonth('tb_tbp.tanggal_tbp', $request->bulan);

        if ($request->opd) {
            $query->where('tb_tbp.nama_skpd', $request->opd);
        }

        $ids = $query->pluck('tb_potongangu.id');

        if ($ids->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data untuk diposting'
            ], 422);
        }

        TbPotonganGu::whereIn('id', $ids)->update([
            'status4' => 'POSTING',
            'tanggal_posting' => now()->toDateString(),
            'posted_by' => auth()->id(),
            'log_posting' => 'Posting massal KPP'
        ]);

        return response()->json([
            'message' => 'Posting KPP berhasil ('.count($ids).' data)'
        ]);
    }

    /* =====================================================
     * DETAIL
     * ===================================================== */
    public function detail($id)
    {
        return DB::table('tb_potongangu')
            ->join('tb_tbp','tb_tbp.id_tbp','=','tb_potongangu.id_tbp')
            ->where('tb_potongangu.id',$id)
            ->select(
                'tb_potongangu.*',
                'tb_tbp.nomor_tbp',
                'tb_tbp.no_spm'
            )
            ->first();
    }

    /* =====================================================
     * SIMPAN
     * ===================================================== */
    public function simpan(Request $request)
    {
        $pajak = TbPotonganGu::findOrFail($request->id);

        /* =====================
        * 🔒 VALIDASI AKSES
        * ===================== */

        if ($pajak->status3 !== 'INPUT') {
            return response()->json([
                'message' => 'Data belum INPUT, tidak bisa diedit BPKAD'
            ], 403);
        }

        if ($pajak->status4 === 'POSTING') {
            return response()->json([
                'message' => 'Data sudah POSTING dan bersifat final'
            ], 403);
        }

        /* =====================
        * VALIDASI FORM
        * ===================== */
        $rules = [
            'id'          => 'required',
            'akun_pajak'  => 'required',
            'rek_belanja' => 'required',
            'nama_npwp'   => 'required',
            'no_npwp'     => 'required',
            'ntpn'        => 'required',
            'id_billing'  => 'required',
            'bukti_setoran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5048'
        ];

        $request->validate($rules);

        /* =====================
        * 🔎 VALIDASI DUPLIKAT NTPN & ID BILLING
        * ===================== */

        // cek ntpn dipakai record lain
        $cekNtpn = TbPotonganGu::where('ntpn', $request->ntpn)
            ->where('id', '!=', $request->id)
            ->exists();

        if ($cekNtpn) {
            return response()->json([
                'message' => 'NTPN sudah digunakan pada data pajak lain'
            ], 422);
        }

        // cek id_billing dipakai record lain
        $cekBilling = TbPotonganGu::where('id_billing', $request->id_billing)
            ->where('id', '!=', $request->id)
            ->exists();

        if ($cekBilling) {
            return response()->json([
                'message' => 'ID Billing sudah digunakan pada data pajak lain'
            ], 422);
        }

        /* =====================
        * DATA LAMA
        * ===================== */
        $ntpnLama = $pajak->ntpn;
        $pathLama = $pajak->bukti_setoran;
        $path     = $pathLama;

        /* =====================
        * NAMA OPD (SAMA PERSIS OPD)
        * ===================== */
        $namaOpd = str_replace(
            ' ',
            '_',
            strtoupper(
                DB::table('tb_tbp')
                    ->where('id_tbp', $pajak->id_tbp)
                    ->value('nama_skpd')
            )
        );

        /* =====================
        * JIKA UPLOAD FILE BARU
        * ===================== */
        if ($request->hasFile('bukti_setoran')) {

            if ($pathLama) {
                Storage::disk('public')->delete($pathLama);
            }

            $file = $request->file('bukti_setoran');
            $ext  = $file->getClientOriginalExtension();

            $filename = $request->ntpn . '-' . $namaOpd . '.' . $ext;

            $path = $file->storeAs(
                'bukti_setoran_pajak',
                $filename,
                'public'
            );
        }

        /* =====================
        * 🔁 RENAME FILE JIKA NTPN BERUBAH
        * ===================== */
        elseif ($ntpnLama && $ntpnLama !== $request->ntpn && $pathLama) {

            $ext = pathinfo($pathLama, PATHINFO_EXTENSION);

            $newFilename = $request->ntpn . '-' . $namaOpd . '.' . $ext;
            $newPath = 'bukti_setoran_pajak/' . $newFilename;

            if (Storage::disk('public')->exists($pathLama)) {
                Storage::disk('public')->move($pathLama, $newPath);
                $path = $newPath;
            }
        }

        /* =====================
        * UPDATE DATA
        * ===================== */
        $pajak->update([
            'akun_pajak'    => $request->akun_pajak,
            'rek_belanja'   => $request->rek_belanja,
            'nama_npwp'     => $request->nama_npwp,
            'no_npwp'       => $request->no_npwp,
            'ntpn'          => $request->ntpn,
            'id_billing'    => $request->id_billing,
            'bukti_setoran' => $path,
            'updated_by'    => auth()->id(),
            'updated_at'    => now(),
        ]);

        return response()->json([
            'message' => 'Data pajak berhasil diperbarui oleh BPKAD'
        ]);
    }

}
