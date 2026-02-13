<?php

namespace App\Http\Controllers;

use App\Exports\DataRealisasianExport;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;


class DataRealisasiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Get data
        public function index(Request $request)
    {
        // $userId = Auth::guard('web')->user()->id;
        $userId = Auth::id();
        $data = array(
            'title'                => 'Data Realisasi Belanja',
            'active_penerimaan'    => 'active',
            'active_sub'           => 'active',
            'active_sidebku'       => 'active',
            'breadcumd'            => 'Penatausahaan',
            'breadcumd1'           => 'Data',
            'breadcumd2'           => 'Realisasi Belanja',
            'userx'                => UserModel::where('id',$userId)->first(['fullname','role','gambar','tahun']),
            );

        if ($request->ajax()) {

            $databku = DB::table('tb_sp2d')
                        ->select('tb_sp2d.nomor_spm', 'tb_sp2d.id', 'tb_sp2d.nomor_sp2d', 'tb_sp2d.tanggal_sp2d', 'tb_sp2d.keterangan_sp2d', 'tb_sp2d.nilai_sp2d', 'tb_sp2d.jenis', 'tb_sp2d.nama_skpd', 'tb_belanjals.uraian', 'tb_belanjals.kode_rekening', 'tb_belanjals.jumlah', 'tb_belanjals.kode_kegiatan', 'tb_belanjals.nama_kegiatan', 'tb_belanjals.kode_sub_kegiatan', 'tb_belanjals.nama_sub_kegiatan', 'tb_belanjals.sp2d_id')
                        ->join('tb_belanjals', 'tb_belanjals.sp2d_id', '=', 'tb_sp2d.id')
                        ->where('tb_sp2d.nama_skpd', auth()->user()->nama_opd)
                        ->get();

            return Datatables::of($databku)
                    ->addIndexColumn()

                    ->addColumn('nilai_sp2d', function($row) {
                        return number_format($row->nilai_sp2d);
                    })

                    ->addColumn('nilai', function($row) {
                        return number_format($row->jumlah);
                    })

                    ->rawColumns(['nilai_sp2d', 'nilai'])
                    ->make(true);
        }

        return view('opd.Data_Realisasi.Tampildatarealisasi', $data);
    }
    
    // public function getDataopd(Request $request)
    // {
    //     $search = $request->searchOpd;
  
    //     if($search == ''){
    //         $opd = OpdModel::orderBy('nama_opd','asc')->select('id','nama_opd')->limit(5)->get();
    //     }else{
    //         $opd = OpdModel::orderBy('nama_opd','asc')->select('id','nama_opd')->where('nama_opd', 'like', '%' .$search . '%')->limit(5)->get();
    //     }
  
    //     $response = array();
    //     foreach($opd as $row){
    //         $response[] = array(
    //             "id"   => $row->id,
    //             "text" => $row->nama_opd
    //         );
    //     }

    //     return response()->json($response); 
    // } 

    public function export()
    {
        $nama_file = 'Data Realisasi Belanja-'.date('Y-m-d_H-i-s').'.xlsx';
        return Excel::download(new DataRealisasianExport, $nama_file);
        // return Excel::download(new DataExport, $nama_file);
    }
}
