<?php

namespace App\Http\Controllers\Bpkad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TbTbp;
use App\Models\TbPotonganGu;
use App\Models\UserModel;
use Illuminate\Support\Facades\Auth;

class VerifikasiTbpController extends Controller
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
            'title'                     => 'Verifikasi TBP',
            'active_pengeluaranvertbp'  => 'active',
            'active_subpvertbp'         => 'active',
            'active_sidevertbp'         => 'active',
            'breadcumd'                 => 'PENATAUSAHAAN',
            'breadcumd1'                => 'Pengeluaran',
            'breadcumd2'                => 'Verifikasi TBP',
            'userx' => UserModel::where('id', $userId)->first(['fullname','role','gambar']),
            'opd' => DB::table('users')
                        ->where('nama_opd', auth()->user()->nama_opd)
                        ->first(),
        ];

        return view('bpkad.verifikasi_tbp.index', $data);

    }

    /* =========================
     * TAB VERIFIKASI
     * ========================= */
    public function dataVerifikasi()
    {
        $data = DB::table('tb_tbp')
            ->join('tb_potongangu', 'tb_potongangu.id_tbp', '=', 'tb_tbp.id_tbp')
            ->whereYear('tb_tbp.tanggal_tbp', $this->tahunAktif)
            ->where('tb_tbp.status', 'DRAFT')
            ->select(
                'tb_tbp.id_tbp',
                'tb_tbp.nomor_tbp',
                'tb_tbp.nama_skpd',
                'tb_potongangu.id',
                'tb_potongangu.nama_pajak_potongan',
                'tb_potongangu.nilai_tbp_pajak_potongan'
            );

        return datatables()->of($data)
            ->addIndexColumn()
            ->editColumn('nilai_tbp_pajak_potongan', fn($r) =>
                'Rp ' . number_format($r->nilai_tbp_pajak_potongan, 0, ',', '.')
            )
            ->addColumn('aksi', fn($r) =>
                '<button class="btn btn-success btn-sm btn-terima"
                    data-id="'.$r->id_tbp.'"
                    data-idpajak="'.$r->id.'">
                    <i class="fas fa-check"></i> Terima
                </button>
                <button class="btn btn-danger btn-sm btn-tolak-ver"
                    data-id="'.$r->id_tbp.'"
                    data-idpajak="'.$r->id.'">
                    <i class="fas fa-times"></i> Tolak
                </button>'
            )
            ->addColumn('cek', fn($r) =>
                '<input type="checkbox" class="cek-tbp"
                    value="'.$r->id_tbp.'"
                    data-idpajak="'.$r->id.'">'
            )
            ->filterColumn('nama_pajak_potongan', function($query, $keyword) {
                $query->where('tb_potongangu.nama_pajak_potongan', 'like', "%{$keyword}%");
            })
            ->filterColumn('nilai_tbp_pajak_potongan', function($query, $keyword) {
                $query->where('tb_potongangu.nilai_tbp_pajak_potongan', 'like', "%{$keyword}%");
            })
            ->filterColumn('nomor_tbp', function($query, $keyword) {
                $query->where('tb_tbp.nomor_tbp', 'like', "%{$keyword}%");
            })
            ->filterColumn('nama_skpd', function($query, $keyword) {
                $query->where('tb_tbp.nama_skpd', 'like', "%{$keyword}%");
            })
            ->rawColumns(['aksi', 'cek'])
            ->make(true);
    }

    /* =========================
     * TAB TERIMA
     * ========================= */
    public function dataTerima()
    {
        $data = DB::table('tb_tbp')
            ->join('tb_potongangu', 'tb_potongangu.id_tbp', '=', 'tb_tbp.id_tbp')
            ->whereYear('tb_tbp.tanggal_tbp', $this->tahunAktif)
            ->where('tb_tbp.status', 'FINAL')
            ->select(
                'tb_tbp.id_tbp',
                'tb_tbp.nomor_tbp',
                'tb_tbp.nama_skpd',
                'tb_potongangu.id',
                'tb_potongangu.nama_pajak_potongan',
                'tb_potongangu.nilai_tbp_pajak_potongan',
                'tb_potongangu.status3'
            );

        return datatables()->of($data)
            ->addIndexColumn()
            ->editColumn('nilai_tbp_pajak_potongan', fn($r) =>
                'Rp ' . number_format($r->nilai_tbp_pajak_potongan, 0, ',', '.')
            )
            ->addColumn('aksi', function ($r) {
                if ($r->status3 === 'INPUT') {
                    return '
                        <button class="btn btn-sm btn-danger btn-tolak"
                            data-id="'.$r->id_tbp.'"
                            title="Tolak">
                            <i class="fas fa-times"></i> Pajak sudah diinput
                        </button>';
                }
                return '
                    <button class="btn btn-sm btn-success btn-tolak"
                        data-id="'.$r->id_tbp.'"
                        title="Tolak">
                        <i class="fas fa-times"></i> Pajak belum diinput
                    </button>';
            })
            ->filterColumn('nama_pajak_potongan', function($query, $keyword) {
                $query->where('tb_potongangu.nama_pajak_potongan', 'like', "%{$keyword}%");
            })
            ->filterColumn('nilai_tbp_pajak_potongan', function($query, $keyword) {
                $query->where('tb_potongangu.nilai_tbp_pajak_potongan', 'like', "%{$keyword}%");
            })
            ->filterColumn('nomor_tbp', function($query, $keyword) {
                $query->where('tb_tbp.nomor_tbp', 'like', "%{$keyword}%");
            })
            ->filterColumn('nama_skpd', function($query, $keyword) {
                $query->where('tb_tbp.nama_skpd', 'like', "%{$keyword}%");
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function tolakDariTerima(Request $request)
    {
        DB::transaction(function () use ($request) {

            // Pajak ditolak
            TbPotonganGu::where('id_tbp', $request->id_tbp)
                ->update([
                    'status1' => 'Tolak'
                ]);

            // TBP turun status
            TbTbp::where('id_tbp', $request->id_tbp)
                ->update([
                    'status' => 'DRAFT'
                ]);
        });

        return response()->json([
            'message' => 'TBP berhasil ditolak'
        ]);
    }

    /* =========================
     * TAB TOLAK
     * ========================= */
    public function dataTolak()
    {
        $data = TbTbp::where('status', 'TOLAK')->whereYear('tb_tbp.tanggal_tbp', $this->tahunAktif); // ✅ FIX

        return datatables()->of($data)
            ->addIndexColumn()
            ->addColumn('total_pajak', function ($r) {
                return number_format(
                    TbPotonganGu::where('id_tbp', $r->id_tbp)
                        ->sum('nilai_tbp_pajak_potongan')
                );
            })
            ->make(true);
    }

    /* =========================
     * PROSES TERIMA
     * ========================= */
    public function terima(Request $request)
    {
        DB::transaction(function () use ($request) {

            TbPotonganGu::where('id_tbp', $request->id_tbp)
                ->update(['status1' => 'Terima']);

            TbTbp::where('id_tbp', $request->id_tbp)
                ->update(['status' => 'FINAL']);
        });

        return response()->json(['message' => 'TBP diterima']);
    }

    /* =========================
     * PROSES TOLAK
     * ========================= */
    public function tolak(Request $request)
    {
        TbTbp::where('id_tbp', $request->id_tbp)
            ->update(['status' => 'DRAFT']);

        return response()->json(['message' => 'TBP ditolak']);
    }

    public function terimaMulti(Request $request)
    {
        $request->validate([
            'ids' => 'required|array'
        ]);

        DB::transaction(function () use ($request) {

            // Semua pajak → TERIMA
            TbPotonganGu::whereIn('id_tbp', $request->ids)
                ->update([
                    'status1' => 'Terima'
                ]);

            // Semua TBP → FINAL
            TbTbp::whereIn('id_tbp', $request->ids)
                ->update([
                    'status' => 'FINAL'
                ]);
        });

        return response()->json([
            'message' => 'TBP terpilih berhasil diverifikasi'
        ]);
    }
    
}