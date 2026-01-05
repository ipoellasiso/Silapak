<?php

namespace App\Http\Controllers;

use App\Models\Sp2dLogModel;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class Sp2dLogController extends Controller
{
    public function index()
    {
        return DataTables::of(\App\Models\Sp2dLogModel::orderBy('uploaded_at', 'desc'))
        ->addIndexColumn()
        ->make(true);
        if ($request->ajax()) {

            $data = DB::table('sp2d_logs')
                        ->select('id', 'user_id', 'user_name', 'nomor_sp2d', 'nama_skpd', 'file_path', 'uploaded_at')
                        ->get();

            return Datatables::of($data)
                    ->addIndexColumn()
                    ->make(true);
        }
        return view('Penatausahaan.Penerimaan.Data_Sp2d.Tampil_sp2d');
    }
}
