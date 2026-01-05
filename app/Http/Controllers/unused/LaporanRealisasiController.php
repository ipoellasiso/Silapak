<?php

namespace App\Http\Controllers;

use App\Models\Sp2dModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\UserModel;
use Maatwebsite\Excel\Facades\Excel;

class LaporanRealisasiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;
        $data = array(
            'title'                => 'Laporan Realisasi',
            'active_penerimaan'    => 'active',
            'active_side_rektpp'   => 'active',
            'active_sub'           => 'active',
            'breadcumd'            => 'Penatausahaan',
            'breadcumd1'           => 'Laporan',
            'breadcumd2'           => 'Realisasi',
            'userx'                => UserModel::where('id',$userId)->first(['fullname','role','gambar',]),
            'data2'                => Sp2dModel::distinct('sp2d.nomor_spm', 'sp2d.nomor_sp2d', 'sp2d.tanggal_sp2d', 'sp2d.keterangan_sp2d', 'sp2d.nilai_sp2d', 'sp2d.jenis', 'sp2d.nama_skpd', 'belanja1.uraian', 'belanja1.norekening', 'belanja1.nilai',)
                                    ->join('opd', 'opd.nama_opd', '=', 'sp2d.nama_skpd')
                                    ->join('sp2dtpp', 'sp2dtpp.id_sp2d', '=', 'sp2d.idhalaman')
                                    ->join('belanja1', 'belanja1.id_sp2d', '=', 'sp2d.idhalaman')
                                    // ->distinct('sp2d.nomor_sp2d')
                                    ->get(),
            // 'data2'                 => Sp2dModel::distinct('nomor_sp2d')->get(),
        );

        return view('Penatausahaan.Penerimaan.LapRealisasi.TampilLapRealisasi', $data);
    }
}
