<?php

namespace App\Http\Controllers;

use App\Models\BelanjalsguModel;
use App\Models\PotonganModel;
use App\Models\Sp2dModel;
use App\Models\TbBelanjaLs;
use App\Models\TbPajakPotonganLs;
use App\Models\TbSp2d;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class Sp2dController1 extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ✅ Halaman utama (render tampilan)
    public function index()
    {
        $userId = Auth::guard('web')->user()->id;

        $data1 = [
            'title'                 => 'Data SP2D',
            'active_sub'            => 'active',
            'active_side_datasp2d'  => 'active',
            'breadcumd'             => 'Home',
            'breadcumd1'            => 'Data',
            'breadcumd2'            => 'SP2D',
            'userx'                 => UserModel::find($userId, ['fullname', 'role', 'gambar', 'tahun']),
        ];

        return view('Penatausahaan.Penerimaan.Data_Sp2d.Tampil_sp2d', $data1);
    }

    // ✅ API untuk DataTable AJAX
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $data = TbSp2d::select(['idhalaman', 'nomor_spm', 'tanggal_sp2d', 'nomor_sp2d', 'nama_skpd', 'nama_pihak_ketiga', 'keterangan_sp2d', 'jenis', 'nilai_sp2d']);
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<button class="btn btn-info btn-sm" onclick="showDetail(\''.$row->idhalaman.'\')"><i class="fa fa-eye"></i></button>';
                    $btn .= ' <button class="btn btn-danger btn-sm" onclick="deleteSp2d(\''.$row->idhalaman.'\')"><i class="fa fa-trash"></i></button>';
                    return $btn;
                })

                ->addColumn('nilai_sp2d', function($row) {
                        return number_format($row->nilai_sp2d);
                    })

                ->rawColumns(['action', 'nilai_sp2d'])
                ->make(true);
        }
    }

    public function show($id)
    {
        $sp2d = TbSp2d::with(['belanja', 'potongan'])
            ->where('idhalaman', $id)
            ->first();

        if (!$sp2d) {
            return response()->json(['error' => 'Data SP2D tidak ditemukan'], 404);
        }

        // Ubah nama field agar cocok dengan JS
        $sp2d->potongan = $sp2d->potongan->map(function ($p) {
            return [
                'uraian' => $p->jenis_pajak ?? '-',
                'jumlah' => $p->nilai_pajak ?? 0,
                'id_billing' => $p->ebilling ?? '-',
            ];
        });

        return response()->json($sp2d);
    }

    public function destroy($id)
    {
        $sp2d = TbSp2d::where('idhalaman', $id)->first();
        if (!$sp2d) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        // Hapus relasi
        TbBelanjaLs::where('id_sp2d', $id)->delete();
        TbPajakPotonganLs::where('id_potongan', $id)->delete();
        $sp2d->delete();

        return response()->json(['message' => 'Data SP2D berhasil dihapus']);
    }

    public function updateNilai(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'nilai' => 'required|string',
        ]);

        // Ambil data dari model BelanjalsguModel (tabel rekening belanja)
        $data = TbBelanjaLs::find($request->id);
        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan']);
        }

        // Bersihkan format angka "Rp" dan titik
        $cleanValue = preg_replace('/[^0-9]/', '', $request->nilai);
        $data->nilai = (int) $cleanValue;

        // Simpan perubahan (UPDATE)
        $data->save();

        return response()->json(['success' => true, 'message' => 'Nilai berhasil diperbarui']);
    }

}
