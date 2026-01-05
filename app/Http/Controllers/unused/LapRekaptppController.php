<?php

namespace App\Http\Controllers;

use App\Models\BelanjalsguModel;
use App\Models\OpdModel;
use App\Models\Sp2dModel;
use App\Models\Sp2dtppModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LapRekaptppController extends Controller
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
                'title'                => 'Data SP2D TPP',
                'active_penerimaan'    => 'active',
                'active_side_regsp2d'  => 'active',
                'active_sub'           => 'active',
                'breadcumd'            => 'Penatausahaan',
                'breadcumd1'           => 'Data',
                'breadcumd2'           => 'SP2D TPP',
                'userx'                => UserModel::where('id',$userId)->first(['fullname','role','gambar',]),
                'opd'                  => DB::table('users')
                                    // ->join('opd',  'opd.id', 'users.id_opd')
                                    // ->select('fullname','nama_opd')
                                    ->where('nama_opd', auth()->user()->nama_opd)
                                    ->first(),
            // 'total_1'            => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-01-01', '2025-01-31'])->sum('nilai_sp2d'),
            // 'total_2'            => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-02-01', '2025-02-28'])->sum('nilai_sp2d'),
            // 'total_3'            => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-03-01', '2025-03-31'])->sum('nilai_sp2d'),
            // 'total_4'            => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-04-01', '2025-04-30'])->sum('nilai_sp2d'),
            // 'total_5'            => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-05-01', '2025-05-31'])->sum('nilai_sp2d'),
            // 'total_6'            => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-06-01', '2025-06-30'])->sum('nilai_sp2d'),
            // 'total_7'            => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-07-01', '2025-07-31'])->sum('nilai_sp2d'),
            // 'total_8'            => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-08-01', '2025-08-31'])->sum('nilai_sp2d'),
            // 'total_9'            => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-09-01', '2025-09-30'])->sum('nilai_sp2d'),
            // 'total_10'           => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-10-01', '2025-10-31'])->sum('nilai_sp2d'),
            // 'total_11'           => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-11-01', '2025-11-30'])->sum('nilai_sp2d'),
            // 'total_12'           => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-12-01', '2025-12-31'])->sum('nilai_sp2d'),
            // 'totalsp2d'          => Sp2dModel::sum('nilai_sp2d'),
            // 'totalcount1'        => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-01-01', '2025-01-31'])->count('nomor_sp2d'),
            // 'totalcount2'        => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-02-01', '2025-02-28'])->count('nomor_sp2d'),
            // 'totalcount3'        => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-03-01', '2025-03-31'])->count('nomor_sp2d'),
            // 'totalcount4'        => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-04-01', '2025-04-30'])->count('nomor_sp2d'),
            // 'totalcount5'        => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-05-01', '2025-05-31'])->count('nomor_sp2d'),
            // 'totalcount6'        => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-06-01', '2025-06-30'])->count('nomor_sp2d'),
            // 'totalcount7'        => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-07-01', '2025-07-31'])->count('nomor_sp2d'),
            // 'totalcount8'        => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-08-01', '2025-08-31'])->count('nomor_sp2d'),
            // 'totalcount9'        => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-09-01', '2025-09-30'])->count('nomor_sp2d'),
            // 'totalcount10'        => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-10-01', '2025-10-31'])->count('nomor_sp2d'),
            // 'totalcount11'        => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-11-01', '2025-11-30'])->count('nomor_sp2d'),
            // 'totalcount12'        => Sp2dModel::whereBetween('sp2d.tanggal_sp2d', ['2025-12-01', '2025-12-31'])->count('nomor_sp2d'),
            // 'totalcount13'        => Sp2dModel::count('nomor_sp2d'),
        );

        if ($request->ajax()) {

            $datapajakls = DB::table('sp2d')
                        ->select('tanggal_sp2d', 'nomor_sp2d', 'nama_skpd', 'nama_pihak_ketiga', 'keterangan_sp2d', 'jenis', 'nilai_sp2d', 'nomor_spm', 'idhalaman', 'sp2d.status1', 'status2')
                        ->whereIn('jenis', ['LS'])
                        ->get();

            return Datatables::of($datapajakls)
                    ->addIndexColumn()
                    ->addColumn('action1', function($row){
                        if($row->status1 == 'Input')
                        {
                        $btn1 = '
                                    
                                ';
                        }else if($row->status2 == 'Batal'){
                        
                            $btn1 = '
                                    <a href="javascript:void(0)" data-toggle="tooltip" data-idhalaman="'.$row->idhalaman.'" class="updatesp2dtpp btn btn-outline-success m-b-xs btn-sm">Ubah
                                    </a>
                                ';
                        }else{
                        
                            $btn1 = '
                                    <a href="javascript:void(0)" data-toggle="tooltip" data-idhalaman="'.$row->idhalaman.'" class="editsp2dtpp btn btn-outline-danger m-b-xs btn-sm">Input
                                    </a>
                                ';
                        }

                        return $btn1;
                    })

                    ->addColumn('action2', function($row){
                        if($row->status2 == 'Input' | $row->status1 == 'Input')
                        {
                        $btn2 = '
                                    <a href="javascript:void(0)" data-toggle="tooltip" data-idhalaman="'.$row->idhalaman.'" class="batalsp2dtpp btn btn-outline-primary m-b-xs btn-sm">Batalkan
                                    </a>
                                ';
                        }else{
                        
                        $btn2 = '
                               
                            ';
                        }

                        return $btn2;
                    })
                    ->addColumn('nilai_sp2d', function($row) {
                        return number_format($row->nilai_sp2d);
                    })
                    // ->addColumn('nilai', function($row) {
                    //     return number_format($row->nilai);
                    // })
                    ->rawColumns(['nilai_sp2d', 'action1', 'action2'])
                    ->make(true);
                    
        }  

        return view('Penatausahaan.Penerimaan.Sp2d_tpp.Sp2dtpp', $data);   
    }

    public function store(Request $request)
    {

        $sp2dtppid = $request->idhalaman;

        $ceksp2dtpp = Sp2dtppModel::where('id_sp2d', $request->idhalaman)->where('id', '!=', $request->id)->first();

        if($ceksp2dtpp)
        {
            return response()->json(['error'=>'SP2D TPP ini sudah ada']);
        }

            $sp2d1 = [
                'status1' => 'Input',
                'status2' => 'Input',
            ];

            $details = [
                // 'id_belanja1'   => $request->id,
                'id_sp2d'       => $request->idhalaman,
                'periode'       => $request->periode,
                'status1'       => $request->status1,
                'status2'       => 'Input',
            ];
        

            Sp2dtppModel::updateOrCreate(['id' => $sp2dtppid], $details);
            Sp2dModel::updateOrCreate(['idhalaman' => $sp2dtppid], $sp2d1);
            return response()->json(['success' =>'Data Berhasil Disimpan']);
        
    }

    public function editsp2dtpp($id)
    {
        $where = array('idhalaman' => $id);
        $sp2dtpp = DB::table('sp2d')
                        ->select('tanggal_sp2d', 'nomor_sp2d', 'nama_skpd', 'nama_pihak_ketiga', 'keterangan_sp2d', 'jenis', 'nilai_sp2d', 'nomor_spm', 'idhalaman')
                        // ->join('belanja1', 'belanja1.id_sp2d', 'sp2d.idhalaman')
                        ->where($where)
                        ->first();

        return response()->json($sp2dtpp);
    }

    public function updatesp2dtpp($id)
    {
        $where = array('idhalaman' => $id);
        $sp2dtpp2 = DB::table('sp2d')
                        ->select('tanggal_sp2d', 'nomor_sp2d', 'nama_skpd', 'nama_pihak_ketiga', 'keterangan_sp2d', 'jenis', 'nilai_sp2d', 'nomor_spm', 'idhalaman')
                        // ->join('belanja1', 'belanja1.id_sp2d', 'sp2d.idhalaman')
                        ->where($where)
                        ->first();

        return response()->json($sp2dtpp2);
    }

    // public function batalsp2dtpp($id)
    // {
    //     $where = array('idhalaman' => $id);
    //     $pajaklssipd = Sp2dModel::where($where)->first();

    //     return response()->json($pajaklssipd);
    // }

    // public function batalsp2dtpp($idhalaman)
    // {
    //     $where = array('idhalaman' => $idhalaman);
    //     $sp2dtpp356 = Sp2dModel::where($where)->first();

    //     return response()->json($sp2dtpp356);
    // }

    public function batalsp2dtpp($idhalaman)
    {
        $where = array('idhalaman' => $idhalaman);
        $sp2dtpp356 = DB::table('sp2d')
                        ->select('tanggal_sp2d', 'nomor_sp2d', 'nama_skpd', 'nama_pihak_ketiga', 'keterangan_sp2d', 'jenis', 'nilai_sp2d', 'nomor_spm', 'idhalaman')
                        // ->join('belanja1', 'belanja1.id_sp2d', 'sp2d.idhalaman')
                        ->where($where)
                        ->first();

        return response()->json($sp2dtpp356);
    }

    public function batalupdate(Request $request, string $idhalaman)
    {

        Sp2dModel::where('idhalaman',$request->get('idhalaman'))
        ->update([
            'status1' => 'Batal',
            'status2' => 'Batal',
        ]);

            return redirect('sp2dtpp')->with('success','Data Berhasil Dibatalkan');
    }

    public function update(Request $request, string $idhalaman)
    {

        Sp2dModel::where('idhalaman',$request->get('idhalaman'))
        ->update([
            'status1' => 'Input',
            'status2' => 'Input',
        ]);

        Sp2dtppModel::where('id_sp2d',$request->get('idhalaman'))
        ->update([
            'periode'       => $request->periode,
            'status1'       => $request->status1,
        ]);

            return redirect('sp2dtpp')->with('success','Data Berhasil DiUpdate');
    }

    public function getDataopd(Request $request)
    {
        $search = $request->searchOpd;
  
        if($search == ''){
            $opd = OpdModel::orderBy('id','asc')->select('id','nama_opd')->get();
        }else{
            $opd = OpdModel::orderBy('id','asc')->select('id','nama_opd')->where('nama_opd', 'like', '%' .$search . '%')->get();
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

}
