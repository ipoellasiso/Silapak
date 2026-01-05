<?php

namespace App\Http\Controllers;

use App\Exports\DataExport;
use App\Imports\BkuImport;
use App\Imports\BkusImport;
use App\Models\AkunpajakModel;
use App\Models\BankModel;
use App\Models\BkuModel;
use App\Models\bkusModel;
use App\Models\OpdModel;
use App\Models\Potongan2Model;
use App\Models\PotonganModel;
use App\Models\RekeningModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Psy\Command\WhereamiCommand;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DataExport2;
use App\Exports\DataExportAdmin;

class RealisasiControllerAdmin extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Get data
        public function index(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;
        $data = array(
            'title'                => 'Data Realisasi Belanja',
            'active_penerimaan'    => 'active',
            'active_sub'           => 'active',
            'active_sidebku'       => 'active',
            'breadcumd'            => 'Penatausahaan',
            'breadcumd1'           => 'Data',
            'breadcumd2'           => 'Realisasi Belanja',
            'userx'                => UserModel::where('id',$userId)->first(['fullname','role','gambar','tahun']),
            // 'total_jan_mandiri'    => bkusModel::where('id_bank', '1')->where('tahun', auth()->user()->tahun)->whereBetween('tb_transaksi.tgl_transaksi', ['2025-01-01', '2025-01-31'])->sum('nilai_transaksi'),
            // 'total_jan_bpd'        => bkusModel::where('id_bank', '2')->where('tahun', auth()->user()->tahun)->whereBetween('tb_transaksi.tgl_transaksi', ['2025-01-01', '2025-01-31'])->sum('nilai_transaksi'),
            // 'total_jan_btn'        => bkusModel::where('id_bank', '3')->where('tahun', auth()->user()->tahun)->whereBetween('tb_transaksi.tgl_transaksi', ['2025-01-01', '2025-01-31'])->sum('nilai_transaksi'),
            // 'total_jan'            => bkusModel::whereBetween('tb_transaksi.tgl_transaksi', ['2025-01-01', '2025-01-31'])->where('tahun', auth()->user()->tahun)->sum('nilai_transaksi'),
            
            );

        if ($request->ajax()) {

            $databku = DB::table('sp2d')
                        ->select('sp2d.nomor_spm', 'sp2d.nomor_sp2d', 'sp2d.tanggal_sp2d', 'sp2d.keterangan_sp2d', 'sp2d.nilai_sp2d', 'sp2d.jenis', 'sp2d.nama_skpd', 'belanja1.uraian', 'belanja1.norekening', 'belanja1.nilai',)
                        // ->join('opd', 'opd.nama_opd', '=', 'sp2d.nama_skpd')
                        ->join('belanja1', 'belanja1.id_sp2d', '=', 'sp2d.idhalaman')
                        // ->where('sp2d.nama_skpd', auth()->user()->nama_opd)
                        ->get();

            return Datatables::of($databku)
                    ->addIndexColumn()

                    ->addColumn('nilai_sp2d', function($row) {
                        return number_format($row->nilai_sp2d);
                    })

                    ->addColumn('nilai', function($row) {
                        return number_format($row->nilai);
                    })

                    ->rawColumns(['nilai_sp2d', 'nilai'])
                    ->make(true);
        }

        return view('Penatausahaan.Penerimaan.Realisasi_Admin.TampilRealisasiAdmin', $data);
    }
    
    public function getDataopd(Request $request)
    {
        $search = $request->searchOpd;
  
        if($search == ''){
            $opd = OpdModel::orderBy('nama_opd','asc')->select('id','nama_opd')->limit(5)->get();
        }else{
            $opd = OpdModel::orderBy('nama_opd','asc')->select('id','nama_opd')->where('nama_opd', 'like', '%' .$search . '%')->limit(5)->get();
        }
  
        $response = array();
        foreach($opd as $row){
            $response[] = array(
                "id"   => $row->id,
                "text" => $row->nama_opd
            );
        }

        return response()->json($response); 
    } 

    public function export()
    {
        $nama_file = 'Data Realisasi Belanja-'.date('Y-m-d_H-i-s').'.xlsx';
        return Excel::download(new DataExportAdmin, $nama_file);
        // return Excel::download(new Data)
    }
}
