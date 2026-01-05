<?php

namespace App\Http\Controllers;

use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class MaintenanceController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }
    
    public function index(Request $request)
    {
        // $userId = Auth::guard('web')->user()->id;
        $data = array(
            'title'                 => 'Maintenance',
            'active_master_data'    => 'active',
            'active_subopd'         => 'active',
            'active_sideperiode'    => 'active',
            'breadcumd'             => 'Pengaturan',
            'breadcumd1'            => 'Master Data',
            'breadcumd2'            => 'Periode',
            // 'userx'                 => UserModel::where('id',$userId)->first(['fullname','role','gambar','tahun']),
        );

        return view('Maintenance', $data);
    }
}
