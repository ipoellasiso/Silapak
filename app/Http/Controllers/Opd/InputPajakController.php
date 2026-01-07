<?php

namespace App\Http\Controllers\Opd;

use App\Http\Controllers\Controller;
use App\Models\TbAkunPajak;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\TbPotonganGu;
use App\Models\UserModel;
use Illuminate\Support\Facades\Auth;

class InputPajakController extends Controller
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
            'title'                       => 'Data Pajak',
            'active_pengeluaran'          => 'active',
            'active_subopd'               => 'active',
            'active_sideinputtbp'         => 'active',
            'breadcumd'                   => 'PENATAUSAHAAN',
            'breadcumd1'                  => 'Pengeluaran',
            'breadcumd2'                  => 'Data Pajak',
            'userx' => UserModel::where('id', $userId)->first(['fullname','role','gambar']),
            'opd' => DB::table('users')
                        ->where('nama_opd', auth()->user()->nama_opd)
                        ->first(),
            'akun_pajak' => TbAkunPajak::where('status','AKTIF')->get(),
        ];

        return view('opd.input_pajak.index', $data);
    }

    /* =====================
     * BELUM INPUT
     * ===================== */
    public function dataBelumInput(Request $request)
    {
        if ($request->ajax()) {

            $data = DB::table('tb_potongangu')
                ->join('tb_tbp','tb_tbp.id_tbp','=','tb_potongangu.id_tbp')
                ->whereYear('tb_tbp.tanggal_tbp', $this->tahunAktif) // ✅ FIX
                ->where('tb_tbp.status','FINAL')
                ->where('tb_potongangu.status1','Terima')
                ->whereNull('tb_potongangu.status3')
                ->where('tb_tbp.nama_skpd', auth()->user()->nama_opd)
                ->select(
                    'tb_potongangu.id',
                    'tb_tbp.nomor_tbp',
                    'tb_potongangu.nama_pajak_potongan',
                    'tb_potongangu.nilai_tbp_pajak_potongan'
                );

            return datatables()->of($data)
                ->addIndexColumn()
                ->editColumn('nilai_tbp_pajak_potongan', function ($r) {
                    return number_format($r->nilai_tbp_pajak_potongan);
                })
                ->addColumn('aksi', function ($r) {
                    return '
                        <button class="btn btn-sm btn-primary btn-input"
                            data-id="'.$r->id.'">
                            <i class="fas fa-edit"></i> Input
                        </button>
                    ';
                })
                ->rawColumns(['aksi'])
                ->make(true);
        }
    }

    /* =====================
     * SUDAH INPUT
     * ===================== */
    public function dataSudahInput()
    {
        $data = DB::table('tb_potongangu')
            ->join('tb_tbp','tb_tbp.id_tbp','=','tb_potongangu.id_tbp')
            ->whereYear('tb_tbp.tanggal_tbp', $this->tahunAktif) // ✅ FIX
            ->where('tb_potongangu.status3','INPUT')
            ->where('tb_tbp.nama_skpd', auth()->user()->nama_opd)
            ->select(
                'tb_potongangu.id',
                'tb_tbp.nomor_tbp',
                'tb_potongangu.nama_pajak_potongan',
                'tb_potongangu.nilai_tbp_pajak_potongan',
                'tb_potongangu.ntpn',
                'tb_potongangu.bukti_setoran',
                'tb_potongangu.status4' // 🔥 WAJIB ADA
            );

        return datatables()->of($data)
            ->addIndexColumn()

            ->editColumn('nilai_tbp_pajak_potongan', function ($r) {
                return number_format($r->nilai_tbp_pajak_potongan);
            })

            // 🔥 INI KODE YANG KAMU TANYAKAN
            ->editColumn('status4', function ($r) {
                return $r->status4 === 'POSTING'
                    ? '<span class="badge bg-success">Sudah Dilaporan ke KPP</span>'
                    : '<span class="badge bg-secondary">On Proses</span>';
            })

            ->addColumn('aksi', function($r){

                $edit = '
                    
                ';

                $batal = '';
                if ($r->status4 !== 'POSTING') {
                    $batal = '
                        <button class="btn btn-sm btn-primary btn-input"
                            data-id="'.$r->id.'" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-warning btn-batal"
                            data-id="'.$r->id.'" title="Batal">
                            <i class="fas fa-undo"></i>
                        </button>
                    ';
                }

                $view = '';
                if ($r->bukti_setoran) {
                    $view = '
                        <a href="'.asset('storage/'.$r->bukti_setoran).'"
                            target="_blank"
                            class="btn btn-sm btn-info"
                            title="Lihat Bukti">
                            <i class="fas fa-eye"></i>
                        </a>
                    ';
                }

                return '
                    <div class="d-flex gap-1 justify-content-center">
                        '.$batal.$view.'
                    </div>
                ';
            })

            // 🔥 PENTING AGAR HTML TIDAK DI-ESCAPE
            ->rawColumns(['aksi','status4'])

            ->make(true);
    }

    /* =====================
     * SIMPAN INPUT PAJAK
     * ===================== */
    public function simpan(Request $request)
    {
        if ($request->ntpn) {
            $cekNtpn = TbPotonganGu::where('ntpn', $request->ntpn)
                ->where('id', '!=', $request->id)
                ->exists();

            if ($cekNtpn) {
                return response()->json([
                    'message' => 'NTPN sudah pernah digunakan!'
                ], 422);
            }
        }

        if ($request->id_billing) {
            $cekBilling = TbPotonganGu::where('id_billing', $request->id_billing)
                ->where('id', '!=', $request->id)
                ->exists();

            if ($cekBilling) {
                return response()->json([
                    'message' => '❌ E-Billing sudah pernah digunakan'
                ], 422);
            }
        }

        $pajak = TbPotonganGu::findOrFail($request->id);

        // 🔑 WAJIB FILE HANYA JIKA INPUT BARU
        $rules = [
            'id' => 'required',
            'akun_pajak' => 'required',
            'rek_belanja' => 'required',
            'nama_npwp' => 'required',
            'no_npwp' => 'required',
            'ntpn' => 'required',
        ];

        if ($pajak->status3 !== 'INPUT') {
            // INPUT BARU → WAJIB
            $rules['bukti_setoran'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5048';
        } else {
            // EDIT → OPTIONAL
            $rules['bukti_setoran'] = 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5048';
        }

        $request->validate($rules);

        // simpan nilai lama
        $ntpnLama  = $pajak->ntpn;
        $pathLama  = $pajak->bukti_setoran;

        // default pakai path lama
        $path = $pathLama;

        $namaOpd = str_replace(
            ' ',
            '_',
            strtoupper(auth()->user()->nama_opd)
        );

        /* ============================
        JIKA UPLOAD FILE BARU
        ============================ */
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

        /* ============================
        🔄 RENAME FILE JIKA NTPN BERUBAH
        ============================ */
        elseif ($ntpnLama && $ntpnLama !== $request->ntpn && $pathLama) {

            $ext = pathinfo($pathLama, PATHINFO_EXTENSION);

            $newFilename = $request->ntpn . '-' . $namaOpd . '.' . $ext;
            $newPath = 'bukti_setoran_pajak/' . $newFilename;

            if (Storage::disk('public')->exists($pathLama)) {
                Storage::disk('public')->move($pathLama, $newPath);
                $path = $newPath;
            }
        }

        /* ============================
        UPDATE DATA PAJAK
        ============================ */
        $pajak->update([
            'akun_pajak'   => $request->akun_pajak,
            'rek_belanja'  => $request->rek_belanja,
            'nama_npwp'    => $request->nama_npwp,
            'no_npwp'      => $request->no_npwp,
            'ntpn'         => $request->ntpn,
            'id_billing'   => $request->id_billing, // 🔥 INI
            'bukti_setoran'=> $path,
            'status3'      => 'INPUT'
        ]);

        return response()->json([
            'message' => 'Data pajak berhasil disimpan'
        ]);
    }

    public function detail($id)
    {
        $data = DB::table('tb_potongangu')
            ->join('tb_tbp','tb_tbp.id_tbp','=','tb_potongangu.id_tbp')
            ->where('tb_potongangu.id', $id)
            ->select(
                'tb_potongangu.id',
                'tb_tbp.nomor_tbp',
                'tb_tbp.no_spm',
                'tb_potongangu.nama_pajak_potongan',
                'tb_potongangu.nilai_tbp_pajak_potongan',
                'tb_potongangu.akun_pajak',
                'tb_potongangu.rek_belanja',
                'tb_potongangu.nama_npwp',
                'tb_potongangu.no_npwp',
                'tb_potongangu.ntpn',
                'tb_potongangu.bukti_setoran'
            )
            ->first();

        return response()->json($data);
    }

    public function batal(Request $request)
    {
        $pajak = TbPotonganGu::findOrFail($request->id);

        if ($pajak->status4 === 'POSTING') {
            return response()->json([
                'message' => 'Data sudah POSTING, tidak bisa dibatalkan'
            ], 403);
        }

        if ($pajak->bukti_setoran) {
            Storage::disk('public')->delete($pajak->bukti_setoran);
        }

        $pajak->update([
            'akun_pajak' => null,
            'rek_belanja' => null,
            'nama_npwp' => null,
            'no_npwp' => null,
            'ntpn' => null,
            'bukti_setoran' => null,
            'status3' => null
        ]);

        return response()->json(['message'=>'Data pajak dibatalkan']);
    }
    
    public function cekNtpnEbilling(Request $request)
    {
        $request->validate([
            'ntpn'     => 'nullable',
            'ebilling' => 'nullable',
            'id'       => 'nullable'
        ]);

        $ntpnExists = false;
        $ebillingExists = false;

        if ($request->ntpn) {
            $ntpnExists = TbPotonganGu::where('ntpn', $request->ntpn)
                ->where('id', '!=', $request->id)
                ->exists();
        }

        if ($request->ebilling) {
            $ebillingExists = TbPotonganGu::where('ebilling', $request->ebilling)
                ->where('id', '!=', $request->id)
                ->exists();
        }

        return response()->json([
            'ntpn_exists'     => $ntpnExists,
            'ebilling_exists' => $ebillingExists
        ]);
    }

}

