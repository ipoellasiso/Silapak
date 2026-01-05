<?php

namespace App\Http\Controllers;

use App\Models\OpdModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class OpdController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;
        $data = array(
            'title'                 => 'Data OPD',
            'active_master_data'    => 'active',
            'active_subopd'         => 'active',
            'active_sideopd'        => 'active',
            'breadcumd'             => 'Pengaturan',
            'breadcumd1'            => 'Master Data',
            'breadcumd2'            => 'Data OPD',
            'userx'                 => UserModel::where('id',$userId)->first(['fullname','role','gambar','tahun']),
        );

        if ($request->ajax()) {

            $dataopd = DB::table('opd')
                        ->select('id', 'nama_opd', 'nama_bendahara', 'alamat')
                        // ->where('tahun', auth()->user()->tahun)
                        ->get();

            return Datatables::of($dataopd)
                    ->addIndexColumn()
                    ->addColumn('action', function($row){

                            $btn = '
                                    <a href="javascript:void(0)" title="Edit Data" data-id="'.$row->id.'" class="editOpd btn btn-primary btn-sm">
                                        <i class="bi bi-pencil-square"></i> 
                                    </a>
                                    ';

                            $btn = $btn.'
                                    <a href="javascript:void(0)" title="Hapus Data" data-id="'.$row->id.'" class="deleteOpd btn btn-danger btn-sm">
                                        <i class="bi bi-trash3"></i> 
                                    </a>
                                    ';

                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }

        return view('Master_Data.Dataopd.Opd', $data);
    }

    public function store(Request $request)
    {

        $opdId = $request->id;

        $cekopd = OpdModel::where('nama_opd', $request->nama_opd)->where('id', '!=', $request->id)->first();
        if($cekopd)
        {
            return redirect()->back()->with('error', 'OPD Sudah Ada');
        } else {
            $details = [
                'nama_opd'          => $request->nama_opd,
                'nama_bendahara'    => $request->nama_bendahara,
                'alamat'            => $request->alamat,
            ];
        }
        
            OpdModel::updateOrCreate(['id' => $opdId], $details);
            return response()->json(['success' =>'Data Berhasil Disimpan']);
        
    }

    public function edit($id)
    {
        $where = array('id' => $id);
        $opd = OpdModel::where($where)->first();

        return response()->json($opd);
    }

    public function destroy($id)
    {

        OpdModel::where('id', $id)->delete();

        return response()->json(['success'=>'Data Berhasil Dihapus']);
    }

}
