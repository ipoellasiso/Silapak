<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\AuditHelper;
use App\Models\AuditLog;


use App\Models\UserModel;
use App\Models\TbTbp;
use App\Models\TbBelanjaGu;
use App\Models\TbPotonganGu;

class PajakTbpController extends Controller
{
    protected int $tahunAktif;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->tahunAktif = auth()->user()->tahun;
            return $next($request);
        });
    }

    /* ============================
     * HALAMAN UTAMA
     * ============================ */
    public function indextbp(Request $request)
    {
        $userId = Auth::id();

        $data = [
            'title'                     => 'Pengajuan TBP',
            'active_pengeluaran'        => 'active',
            'active_subopd'             => 'active',
            'active_sidepengajuantbp'   => 'active',
            'breadcumd'                 => 'Penatausahaan',
            'breadcumd1'                => 'Pengeluaran',
            'breadcumd2'                => 'Pengajuan TBP',
            'userx' => UserModel::where('id', $userId)->first(['fullname','role','gambar']),
            'opd' => DB::table('users')
                        ->where('nama_opd', auth()->user()->nama_opd)
                        ->first(),
        ];

        return view('Tarik_data.Pajaktbp', $data);
    }

    /* ============================
     * LIST TBP (TAB ATAS)
     * ============================ */
    public function indextbplist(Request $request)
    {
        if ($request->ajax()) {

            $data = DB::table('tb_tbp')
                ->leftJoin('tb_potongangu', 'tb_tbp.id_tbp', '=', 'tb_potongangu.id_tbp')
                ->whereYear('tb_tbp.tanggal_tbp', $this->tahunAktif) // ✅ FIX
                ->where('tb_tbp.nama_skpd', auth()->user()->nama_opd)
                ->select(
                    'tb_tbp.id_tbp',
                    'tb_tbp.nomor_tbp',
                    'tb_tbp.no_spm',
                    'tb_tbp.tanggal_tbp',
                    'tb_tbp.nilai_tbp',
                    'tb_tbp.status',

                    // total pajak
                    DB::raw('COALESCE(SUM(tb_potongangu.nilai_tbp_pajak_potongan),0) as total_pajak'),

                    // 🔥 hitung jumlah pajak yang sudah INPUT
                    DB::raw("
                        SUM(
                            CASE 
                                WHEN tb_potongangu.status3 = 'INPUT' 
                                THEN 1 ELSE 0 
                            END
                        ) as total_input
                    ")
                )
                ->groupBy(
                    'tb_tbp.id_tbp',
                    'tb_tbp.nomor_tbp',
                    'tb_tbp.no_spm',
                    'tb_tbp.tanggal_tbp',
                    'tb_tbp.nilai_tbp',
                    'tb_tbp.status'
                )
                ->orderBy('tb_tbp.tanggal_tbp', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()

                ->editColumn('nilai_tbp', function ($row) {
                    return '<div class="text-end fw-semibold">'
                            .number_format($row->nilai_tbp).
                        '</div>';
                })

                ->editColumn('total_pajak', function ($row) {
                    return '<div class="text-end text-danger fw-semibold">'
                            .number_format($row->total_pajak).
                        '</div>';
                })

                ->addColumn('status_badge', function ($row) {
                    if ($row->status === 'FINAL') {
                        return '<span class="badge bg-success">
                                    <i class="fas fa-lock"></i> FINAL
                                </span>';
                    }
                    return '<span class="badge bg-warning text-dark">
                                <i class="fas fa-clock"></i> DRAFT
                            </span>';
                })

                ->addColumn('aksi', function ($row) {

                    // ❌ Jika SUDAH ADA INPUT → edit & hapus hilang
                    if ($row->total_input > 0) {
                        return '<span class="text-muted">Pajak sudah input</span>';
                    } else{
                        $btnEdit = '
                            <button class="btn btn-warning btn-sm btn-edit-tbp"
                                    data-id="'.$row->id_tbp.'">
                                <i class="fa fa-edit"></i>
                            </button>
                        ';
                    }

                    // Hapus hanya boleh kalau DRAFT
                    if ($row->status === 'DRAFT') {
                        $btnDelete = '
                            <button class="btn btn-danger btn-sm btn-hapus-tbp"
                                    data-id="'.$row->id_tbp.'">
                                <i class="fa fa-trash"></i>
                            </button>
                        ';
                    } else {
                        $btnDelete = '
                        
                        ';
                    }

                    return $btnEdit . ' ' . $btnDelete;
                })

                ->rawColumns(['nilai_tbp','total_pajak','status_badge','aksi'])
                ->make(true);
        }
    }

    /* ============================
     * BELUM VERIFIKASI
     * ============================ */
    public function indextbpbelumverifikasi(Request $request)
    {
        if ($request->ajax()) {

            $data = TbPotonganGu::join('tb_tbp', 'tb_tbp.id_tbp', '=', 'tb_potongangu.id_tbp')
                ->whereYear('tb_tbp.tanggal_tbp', $this->tahunAktif) // ✅ FIX
                ->where('tb_potongangu.status1', 'Belum_Verifikasi')
                ->where('tb_tbp.nama_skpd', auth()->user()->nama_opd)
                ->select([
                    'tb_potongangu.id',
                    'tb_tbp.nomor_tbp',
                    'tb_potongangu.nama_pajak_potongan',
                    'tb_potongangu.nilai_tbp_pajak_potongan',
                    'tb_potongangu.status1',
                ]);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nilai_pajak', function ($row) {
                    return number_format($row->nilai_tbp_pajak_potongan);
                })
                ->addColumn('aksi', function ($row) {
                    return '<button class="btn btn-outline-danger btn-sm">Hapus</button>';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }
    }

    /* ============================
     * DITERIMA
     * ============================ */
    public function indextbpterima(Request $request)
    {
    if ($request->ajax()) {

        $data = TbPotonganGu::join('tb_tbp', 'tb_tbp.id_tbp', '=', 'tb_potongangu.id_tbp')
            ->whereYear('tb_tbp.tanggal_tbp', $this->tahunAktif) // ✅ FIX
            ->where('tb_potongangu.status1', 'Terima')
            ->where('tb_tbp.nama_skpd', auth()->user()->nama_opd)
            ->select([
                'tb_tbp.nomor_tbp',
                'tb_potongangu.nama_pajak_potongan',
                'tb_potongangu.nilai_tbp_pajak_potongan',
                'tb_potongangu.status1',
            ]);

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('nilai_pajak', fn($row) => number_format($row->nilai_tbp_pajak_potongan))
            ->make(true);
    }
    }

    /* ============================
     * DITOLAK
     * ============================ */
    public function indextbptolak(Request $request)
    {
        if ($request->ajax()) {

            $data = TbPotonganGu::join('tb_tbp', 'tb_tbp.id_tbp', '=', 'tb_potongangu.id_tbp')
                ->whereYear('tb_tbp.tanggal_tbp', $this->tahunAktif) // ✅ FIX
                ->where('tb_potongangu.status1', 'Tolak')
                ->where('tb_tbp.nama_skpd', auth()->user()->nama_opd)
                ->select([
                    'tb_tbp.nomor_tbp',
                    'tb_potongangu.nama_pajak_potongan',
                    'tb_potongangu.nilai_tbp_pajak_potongan',
                    'tb_potongangu.status1',
                ]);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nilai_pajak', fn($row) => number_format($row->nilai_tbp_pajak_potongan))
                ->make(true);
        }
    }

    /* ============================
     * SIMPAN JSON TBP (INTI)
     * ============================ */
    public function save_jsontbp(Request $request)
    {
        $request->validate([
            'no_spm' => 'required',
            'jsontextareatbp' => 'required',
        ]);

        $payload = json_decode($request->jsontextareatbp, true);

        if (!$payload) {
            return back()->with('error', 'JSON tidak valid');
        }

        if (empty($payload['pajak_potongan'])) {
            return back()->with('error', 'TBP ini tidak memiliki pajak');
        }

        if (TbTbp::where('nomor_tbp', $payload['nomor_tbp'])->exists()) {
            return back()->with('error', 'TBP sudah ada');
        }

        DB::transaction(function () use ($payload, $request) {

            $idTbp = Str::uuid()->toString();

            /* HEADER TBP */
            TbTbp::create([
                'id_tbp' => $idTbp,
                'nomor_tbp' => $payload['nomor_tbp'],
                'tanggal_tbp' => Carbon::parse($payload['tanggal_tbp'])->format('Y-m-d'),
                'nilai_tbp' => $payload['nilai_tbp'],
                'keterangan_tbp' => $payload['keterangan_tbp'] ?? null,
                'no_npd' => $payload['nomor_npd'] ?? null,
                'nama_skpd' => $payload['nama_skpd'],
                'no_spm' => $request->no_spm,
                'status' => 'DRAFT',
            ]);

            /* DETAIL BELANJA */
            foreach ($payload['detail'] as $row) {
                TbBelanjaGu::create([
                    'id_tbp' => $idTbp,
                    'kode_rekening' => $row['kode_rekening'],
                    'uraian' => $row['uraian'],
                    'jumlah' => $row['jumlah'],
                ]);
            }

            /* PAJAK (READ ONLY) */
            foreach ($payload['pajak_potongan'] as $pajak) {
                TbPotonganGu::create([
                    'id_tbp' => $idTbp,
                    'nama_pajak_potongan' => $pajak['nama_pajak_potongan'],
                    'nilai_tbp_pajak_potongan' => $pajak['nilai_tbp_pajak_potongan'],
                    'id_billing' => $pajak['id_billing'] ?? null,
                    'status1' => 'Belum_Verifikasi',
                ]);
            }
        });

        return back()->with('status', 'TBP berhasil diajukan');
    }

    public function hapusTbp($id)
    {
        try {
            DB::transaction(function () use ($id) {

                $tbp = TbTbp::where('id_tbp', $id)->firstOrFail();
                $oldData = $tbp->toArray();

                TbPotonganGu::where('id_tbp', $id)->delete();
                TbBelanjaGu::where('id_tbp', $id)->delete();
                $tbp->delete();

                AuditHelper::log(
                    'DELETE',
                    'tb_tbp',
                    $id,
                    $oldData,
                    null
                );
            });

            return response()->json([
                'status' => 'success',
                'message' => 'TBP berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }

        if ($tbp->status === 'FINAL') {
            return response()->json([
                'status' => 'error',
                'message' => 'TBP sudah FINAL dan tidak bisa diubah'
            ], 403);
        }

    }

    public function editTbp($id)
    {
        $tbp = TbTbp::where('id_tbp', $id)->firstOrFail();

        return response()->json($tbp);
    }

    public function updateTbp(Request $request, $id)
    {
        $request->validate([
            'no_spm' => 'required',
            'tanggal_tbp' => 'required|date',
        ]);

        $tbp = TbTbp::where('id_tbp', $id)->firstOrFail();
        $oldData = $tbp->toArray();

        $tbp->update([
            'no_spm' => $request->no_spm,
            'tanggal_tbp' => $request->tanggal_tbp,
            'status' => "DRAFT",
        ]);

        AuditHelper::log(
            'UPDATE',
            'tb_tbp',
            $id,
            $oldData,
            $tbp->fresh()->toArray()
        );

        return response()->json([
            'status' => 'success',
            'message' => 'TBP berhasil diperbarui'
        ]);

        if ($tbp->status === 'FINAL') {
            return response()->json([
                'status' => 'error',
                'message' => 'TBP sudah FINAL dan tidak bisa diubah'
            ], 403);
        }

    }

    public function auditLog()
    {

        $userId = Auth::id();

        $data = [
            'title'                     => 'Audit Log TBP',
            'active_pengeluaranlogtbp'  => 'active',
            'active_subplogtbp'         => 'active',
            'active_sidelogtbp'         => 'active',
            'breadcumd'                 => 'PENATAUSAHAAN',
            'breadcumd1'                => 'Pengeluaran',
            'breadcumd2'                => 'Audit Log TBP',
            'userx' => UserModel::where('id', $userId)->first(['fullname','role','gambar']),
            'opd' => DB::table('users')
                        ->where('nama_opd', auth()->user()->nama_opd)
                        ->first(),
        ];

        return view('Audit.index', $data);
    }

    public function auditLogData(Request $request)
    {
        $data = AuditLog::orderBy('created_at', 'desc');

        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('created_at', fn($r) => $r->created_at->format('d-m-Y H:i'))
            ->editColumn('old_data', fn($r) => '<pre>'.json_encode($r->old_data, JSON_PRETTY_PRINT).'</pre>')
            ->editColumn('new_data', fn($r) => '<pre>'.json_encode($r->new_data, JSON_PRETTY_PRINT).'</pre>')
            ->rawColumns(['old_data','new_data'])
            ->make(true);
    }

    private function updateStatusTbpIfFinal($idTbp)
    {
        $sisa = TbPotonganGu::where('id_tbp', $idTbp)
            ->where('status1', '!=', 'Terima')
            ->count();

        if ($sisa === 0) {
            TbTbp::where('id_tbp', $idTbp)
                ->update(['status' => 'FINAL']);
        }
    }

    public function terimaPajak(Request $request)
    {
        $pajak = TbPotonganGu::findOrFail($request->id);

        $pajak->update([
            'status1' => 'Terima'
        ]);

        // 🔥 update status TBP otomatis
        $this->updateStatusTbpIfFinal($pajak->id_tbp);

        return response()->json([
            'status' => 'success',
            'message' => 'Pajak berhasil diterima'
        ]);
    }

}
