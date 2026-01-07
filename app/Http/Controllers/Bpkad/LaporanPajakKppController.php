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
            'active_subpvertbp'         => '',
            'active_sidevertbp'         => '',
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

    private function baseQuery(Request $request)
    {
        $query = DB::table('tb_tbp as tbp')
        ->join('tb_potongangu as pot','tbp.id_tbp','=','pot.id_tbp')
        ->whereYear('tbp.tanggal_tbp', $this->tahunAktif) // ✅ FIX
        ->where('pot.status3','INPUT');

        // 🔹 FILTER OPD
        if ($request->opd) {
            $query->where('tbp.nama_skpd', $request->opd);
        }

        // 🔹 FILTER TAHUN
        if ($request->tahun) {
            $query->whereYear('tbp.tanggal_tbp', $request->tahun);
        }

        // 🔹 FILTER BULAN
        if ($request->bulan) {
            $query->whereMonth('tbp.tanggal_tbp', $request->bulan);
        }

        return $query->select(
            'pot.id', // 🔥 TAMBAHKAN
            'pot.bukti_setoran',
            'pot.status4',
            'tbp.no_spm',
            'tbp.nama_skpd',
            'tbp.nomor_tbp',
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

    public function dataSudahSp2d(Request $request)
    {
        $sp2d = $this->getSp2dCache();
        $sp2dBySpm = $sp2d->keyBy(fn($i)=>trim($i['nomor_spm']));

        $query = $this->baseQuery($request)
            ->whereIn('tbp.no_spm', $sp2dBySpm->keys());

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('tanggal_sp2d', fn($r)=>
                $sp2dBySpm[trim($r->no_spm)]['tanggal_sp2d'] ?? '-'
            )
            ->addColumn('nomor_sp2d', fn($r)=>
                $sp2dBySpm[trim($r->no_spm)]['nomor_spm'] ?? '-'
            )
            ->addColumn('nilai_sp2d', fn($r)=>
                number_format($sp2dBySpm[trim($r->no_spm)]['nilai_sp2d'] ?? 0)
            )
            ->editColumn('nilai_pajak', fn($r)=>number_format($r->nilai_pajak))
            ->addColumn('pajak', function ($r) {
                return '
                    <div class="pajak-box">
                        <div><span class="badge bg-info">Jenis</span> '.$r->jenis_pajak.'</div>
                        <div><span class="badge bg-secondary">Akun</span> '.$r->akun_pajak.'</div>
                        <div><span class="badge bg-warning text-dark">Billing</span> '.$r->id_billing.'</div>
                        <div><span class="badge bg-success">NTPN</span> '.$r->ntpn.'</div>
                    </div>
                ';
            })
            ->editColumn('no_spm', function ($r) {
                return '
                    <div>
                        <strong>SKPD :</strong> '.($r->nama_skpd ?? '-').'
                        <br>
                        <strong>SPM :</strong> '.$r->no_spm.'
                        <br>
                        <strong>TBP :</strong> '.($r->nomor_tbp ?? '-').'
                    </div>
                ';
            })
            ->addColumn('aksi', function ($r) {

                $btnEdit = '';
                if ($r->status4 !== 'POSTING') {
                    $btnEdit = '
                        <button class="btn btn-sm btn-primary btn-edit"
                            data-id="'.$r->id.'" title="Edit Pajak">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                $btnView = '';
                if ($r->bukti_setoran) {
                    $btnView = '
                        <a href="'.asset('storage/'.$r->bukti_setoran).'"
                        target="_blank"
                        class="btn btn-sm btn-info"
                        title="Lihat Bukti Setoran">
                        <i class="fas fa-eye"></i>
                        </a>
                    ';
                }

                return '
                    <div class="d-flex justify-content-center gap-1">
                        '.$btnEdit.'
                        '.$btnView.'
                    </div>
                ';
            })
            ->rawColumns(['no_spm', 'pajak', 'aksi'])
            ->make(true);
    }

    public function dataBelumSp2d(Request $request)
    {
        $sp2dSpm = collect(
            Http::get('http://127.0.0.1:8001/api/sp2d')->json()
        )->pluck('nomor_spm')->map(fn($v)=>trim($v));

        $query = $this->baseQuery($request)
            ->whereNotIn('tbp.no_spm', $sp2dSpm);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('status_sp2d', function () {
                return '
                    <div class="text-center">
                        <span class="badge bg-danger px-3 py-2">
                            ❌ Belum SP2D
                        </span>
                    </div>
                ';
            })
            ->rawColumns(['status_sp2d'])
            ->editColumn('nilai_pajak', fn($r)=>number_format($r->nilai_pajak))
            ->editColumn('tbp', function ($r) {
                return '
                    <div class="">
                        <div class="mt-1">
                            <strong>SKPD</strong><br>'.$r->nama_skpd.'
                        </div>
                        <div class="mt-1">
                            <strong>TBP</strong><br>'.($r->nomor_tbp ?? '-').'
                        </div>
                    </div>
                ';
            })
            ->addColumn('aksi', function ($r) {

                $btnEdit = '';
                if ($r->status4 !== 'POSTING') {
                    $btnEdit = '
                        <button class="btn btn-sm btn-primary btn-edit"
                            data-id="'.$r->id.'" title="Edit Pajak">
                            <i class="fas fa-edit"></i>
                        </button>
                    ';
                }

                $btnView = '';
                if ($r->bukti_setoran) {
                    $btnView = '
                        <a href="'.asset('storage/'.$r->bukti_setoran).'"
                        target="_blank"
                        class="btn btn-sm btn-info"
                        title="Lihat Bukti Setoran">
                        <i class="fas fa-eye"></i>
                        </a>
                    ';
                }

                return '
                    <div class="d-flex justify-content-center gap-1">
                        '.$btnEdit.'
                        '.$btnView.'
                    </div>
                ';
            })
            ->rawColumns(['status_sp2d','tbp', 'aksi'])
            ->make(true);
    }

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

    // public function dataBelumPosting(Request $request)
    // {
    //     $sp2d = Http::get('http://127.0.0.1:8001/api/sp2d')->json();
    //     $sp2dBySpm = collect($sp2d)->keyBy(fn($i)=>trim($i['nomor_spm']));

    //     $query = DB::table('tb_tbp as tbp')
    //         ->join('tb_potongangu as pot','tbp.id_tbp','=','pot.id_tbp')
    //         ->where('pot.status3','INPUT')        // sudah input
    //         ->whereNull('pot.status4')             // 🔥 BELUM POSTING
    //         ->whereYear('tbp.tanggal_tbp', $request->tahun);

    //     if ($request->bulan) {
    //         $query->whereMonth('tbp.tanggal_tbp', $request->bulan);
    //     }

    //     if ($request->opd) {
    //         $query->where('tbp.nama_skpd', $request->opd);
    //     }

    //     $query->select(
    //         'tbp.no_spm',
    //         'tbp.nomor_tbp',
    //         'tbp.nama_skpd',
    //         'pot.nama_pajak_potongan as jenis_pajak',
    //         'pot.nilai_tbp_pajak_potongan as nilai_pajak'
    //     );

    //     return DataTables::of($query)
    //         ->addIndexColumn()

    //         ->editColumn('no_spm', function ($r) {
    //             return '
    //                 <div>
    //                     <strong>SKPD:</strong> '.$r->nama_skpd.'<br>
    //                     <strong>SPM:</strong> '.$r->no_spm.'<br>
    //                     <strong>TBP:</strong> '.$r->nomor_tbp.'
    //                 </div>
    //             ';
    //         })

    //         ->editColumn('nilai_pajak', fn($r)=>number_format($r->nilai_pajak))

    //         ->addColumn('status', function () {
    //             return '
    //                 <span class="badge bg-warning text-dark">
    //                     ⏳ Belum Posting
    //                 </span>
    //             ';
    //         })

    //         ->rawColumns(['no_spm','status'])
    //         ->make(true);
    // }

    // public function export(Request $request)
    // {
    //     $request->validate([
    //         'tahun' => 'required'
    //     ]);

    //     return Excel::download(
    //         new LaporanPajakKppExport(
    //             $request->tahun,
    //             $request->bulan,
    //             $request->opd
    //         ),
    //         'Laporan_Pajak_KPP_'.$request->tahun.'.xlsx'
    //     );
    // }

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
            'bukti_setoran' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5048'
        ];

        $request->validate($rules);

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
            'bukti_setoran' => $path,
            'updated_by'    => auth()->id(),
            'updated_at'    => now(),
        ]);

        return response()->json([
            'message' => 'Data pajak berhasil diperbarui oleh BPKAD'
        ]);
    }

    private function getSp2dCache()
    {
        return Cache::remember('sp2d_api_cache', 300, function () {
            return collect(
                Http::get('http://127.0.0.1:8001/api/sp2d')->json()
            );
        });
    }

}
