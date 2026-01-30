<?php

namespace App\Http\Controllers\Bpkad;

use App\Http\Controllers\Controller;
use App\Models\TbPajakPotonganLs;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Models\TbPajakPotonganLsLog;

class LaporanPajakLsController extends Controller
{
    protected int $tahunAktif;

    public function __construct()
    {
        $this->middleware(function ($req, $next) {
            $this->tahunAktif = auth()->user()->tahun;
            return $next($req);
        });
    }

    public function index()
    {
        $userId = Auth::id();

        $data = [
            'title'                     => 'Data Pajak LS',
            'active_subpvertbp'         => 'active',
            'active_sidedatapajak'      => 'active',
            'active_sidedatapajakls'    => 'active',
            'breadcumd'                 => 'Penatausahaan',
            'breadcumd1'                => 'Pengeluaran',
            'breadcumd2'                => 'Data Pajak LS',

            // USER LOGIN
            'userx' => UserModel::where('id', $userId)
                ->first(['fullname','role','gambar']),

            // LIST OPD (FILTER)
            'listOpd' => DB::table('opd')
                ->orderBy('nama_opd')
                ->get(),

            // AKUN PAJAK (UNTUK MODAL EDIT)
            'akun_pajak' => DB::table('tb_akun_pajak')
                ->where('status', 'AKTIF')
                ->orderBy('kode_akun')
                ->get(),
        ];

        return view('bpkad.laporan_pajak_ls.index', $data);
    }

    private function baseLsQuery(Request $request)
    {
        $query = DB::table('tb_pajak_potonganls as pajak')
            ->join('tb_sp2d as sp2d', 'sp2d.id', '=', 'pajak.sp2d_id');

        // ✅ FILTER TAHUN
        $query->where(
            'sp2d.tahun',
            $request->filled('tahun') ? $request->tahun : $this->tahunAktif
        );

        // ✅ PAJAK YANG DIPAKAI KPP
        $query->where(function ($q) {
            $q->where('pajak.nama_pajak_potongan', 'like', '%Pajak Pertambahan Nilai%')
            ->orWhere('pajak.nama_pajak_potongan', 'like', '%PPh 21%')
            ->orWhere('pajak.nama_pajak_potongan', 'like', '%Pajak Penghasilan PS 22%')
            ->orWhere('pajak.nama_pajak_potongan', 'like', '%Pajak Penghasilan PS 23%')
            ->orWhere('pajak.nama_pajak_potongan', 'like', '%Pajak Penghasilan PS 24%');
        });

        // ❌ JANGAN TAMPILKAN PAJAK NILAI 0
        $query->where('pajak.nilai_sp2d_pajak_potongan', '>', 0);

        if ($request->opd) {
            $query->where('sp2d.nama_skpd', $request->opd);
        }

        if ($request->bulan) {
            $query->whereMonth('sp2d.tanggal_sp2d', $request->bulan);
        }

       return $query->select(
            'pajak.id',
            'pajak.status1',
            'sp2d.nama_skpd',
            'sp2d.nomor_sp2d',
            'sp2d.tanggal_sp2d',
            'sp2d.nilai_sp2d',
            'pajak.nama_pajak_potongan as jenis_pajak',
            'pajak.kode_sinergi',
            'pajak.nama_sinergi',
            'pajak.id_billing',
            'pajak.ntpn',
            'pajak.koreksi_kpp', 
            'pajak.status2',
            'pajak.nilai_sp2d_pajak_potongan as nilai_pajak',
            'pajak.akun_pajak',      
            'pajak.rek_belanja',
            'pajak.akun_pajak'   
            
        );
    }

    public function dataSudahInput(Request $request)
    {
        $query = $this->baseLsQuery($request)
            ->where('pajak.status1', 'sudah')
            ->whereNotNull('pajak.akun_pajak')
            ->whereNotNull('pajak.rek_belanja')
            ->whereNotNull('pajak.id_billing')
            ->whereNotNull('pajak.ntpn');
        

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('nilai_sp2d', fn($r)=>number_format($r->nilai_sp2d))
            ->editColumn('nilai_pajak', fn($r)=>number_format($r->nilai_pajak))
            ->addColumn('pajak', fn($r)=>'
                <span class="badge bg-info">Jenis</span> '.$r->jenis_pajak.'<br>
                <span class="badge bg-secondary">Akun</span>'.($r->akun_pajak ?? '<span class="text-danger">-</span>').'<br>
                <span class="badge bg-warning">Billing</span> '.$r->id_billing.'<br>
                <span class="badge bg-success">NTPN</span> '.$r->ntpn.'
            ')
            ->addColumn('aksi', function ($r) {

                // 🔒 SUDAH POSTING → HILANGKAN EDIT
                if ($r->status2 === 'posting') {
                    return '<span class="badge bg-secondary">POSTING</span>';
                }

                // ✏️ BELUM POSTING (NULL) → BOLEH EDIT
                return '
                    <button class="btn btn-sm btn-warning btn-edit"
                        data-id="'.$r->id.'"
                        title="Edit Pajak">
                        <i class="fas fa-edit"></i>
                    </button>
                ';
            })
            ->rawColumns(['pajak','aksi'])
            ->make(true);
    }

    public function dataBelumInput(Request $request)
    {
        $query = $this->baseLsQuery($request)
            ->where(function ($q) {
                $q->where('pajak.status1', 'belum')
                ->orWhereNull('pajak.id_billing')
                ->orWhereNull('pajak.ntpn');
            });

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('nilai_sp2d', fn($r)=>number_format($r->nilai_sp2d))
            ->editColumn('nilai_pajak', fn($r)=>number_format($r->nilai_pajak))
            ->addColumn('pajak', fn($r)=>'
                <span class="badge bg-info">Jenis</span> '.$r->jenis_pajak.'<br>
                <span class="badge bg-info">Id_Billing</span> '.$r->id_billing.'<br>
                <span class="badge bg-danger">❌ Belum Lengkap</span>
            ')
            ->addColumn('aksi', fn($r)=>'
                <button class="btn btn-sm btn-primary btn-edit" data-id="'.$r->id.'">
                    <i class="fas fa-edit"></i>
                </button>
            ')
            ->rawColumns(['pajak','aksi'])
            ->make(true);
    }

    public function detail($id)
    {
        return TbPajakPotonganLs::with('sp2d')->findOrFail($id);
    }

    public function simpan(Request $request)
    {   
        $request->validate([
            'id' => 'required',
            'akun_pajak' => 'required',
            'rek_belanja' => 'required',
            'ntpn' => 'required',
            'id_billing' => 'required',
        ]);

        $pajak = TbPajakPotonganLs::findOrFail($request->id);

        // 🔒 Jika sudah posting
        if ($pajak->status_posting === 'posting') {
            return response()->json([
                'message' => 'Data sudah POSTING'
            ], 403);
        }

        /* ================= VALIDASI DUPLIKAT ================= */

        // ❌ NTPN sudah dipakai
        $cekNtpn = TbPajakPotonganLs::where('ntpn', $request->ntpn)
            ->where('id', '!=', $request->id)
            ->exists();

        if ($cekNtpn) {
            return response()->json([
                'message' => 'NTPN sudah digunakan pada data pajak lain'
            ], 422);
        }

        // ❌ ID Billing sudah dipakai
        $cekBilling = TbPajakPotonganLs::where('id_billing', $request->id_billing)
            ->where('id', '!=', $request->id)
            ->exists();

        if ($cekBilling) {
            return response()->json([
                'message' => 'ID Billing sudah digunakan pada data pajak lain'
            ], 422);
        }

        // 📌 SIMPAN KONDISI SEBELUM
        $sebelum = [
            'akun_pajak'  => $pajak->akun_pajak,
            'rek_belanja' => $pajak->rek_belanja,
            'ntpn'        => $pajak->ntpn,
            'id_billing'  => $pajak->id_billing,
        ];

        /* ================= SIMPAN DATA ================= */

        $pajak->update([
            'akun_pajak'  => $request->akun_pajak,
            'rek_belanja' => $request->rek_belanja,
            'ntpn'        => $request->ntpn,
            'id_billing'  => $request->id_billing,
            'status1'     => 'sudah',
            'koreksi_kpp' => 1
        ]);

        $jumlahKoreksi = TbPajakPotonganLsLog::where('pajak_ls_id', $pajak->id)->count();
        $urutanKoreksi = $jumlahKoreksi + 1;

        // 📌 SIMPAN LOG
        TbPajakPotonganLsLog::create([
            'pajak_ls_id' => $pajak->id,
            'user_id'     => auth()->id(),
            'aksi'        => 'KOREKSI KPP',
            'sebelum'     => $sebelum,
            'sesudah'     => [
                'akun_pajak'  => $request->akun_pajak,
                'rek_belanja' => $request->rek_belanja,
                'ntpn'        => $request->ntpn,
                'id_billing'  => $request->id_billing,
            ],
            'keterangan'  => 'KOREKSI KPP ke-' . $urutanKoreksi
        ]);

        return response()->json([
            'message' => 'Pajak LS berhasil diperbarui'
        ]);
    }

    public function log($id)
    {
        return TbPajakPotonganLsLog::query()
            ->join('users', 'users.id', '=', 'tb_pajak_potonganls_log.user_id')
            ->where('tb_pajak_potonganls_log.pajak_ls_id', $id)
            ->orderBy('tb_pajak_potonganls_log.created_at', 'asc')
            ->select(
                'tb_pajak_potonganls_log.*',
                'users.fullname as nama_user'
            )
            ->get();
    }

}
