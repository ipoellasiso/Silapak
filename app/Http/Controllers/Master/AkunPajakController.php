<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TbAkunPajak;
use App\Models\UserModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AkunPajakController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $data = [
            'title'                       => 'Data Akun Pajak',
            'active_pengeluaran'          => 'active',
            'active_subopd'               => 'active',
            'active_sideakun'             => 'active',
            'breadcumd'                   => 'PENATAUSAHAAN',
            'breadcumd1'                  => 'Pengeluaran',
            'breadcumd2'                  => 'Input Akun Pajak',
            'userx' => UserModel::where('id', $userId)->first(['fullname','role','gambar']),
            'opd' => DB::table('users')
                        ->where('nama_opd', auth()->user()->nama_opd)
                        ->first(),
        ];

        return view('master.akun_pajak.index', $data);
    }

    public function data()
    {
        $data = TbAkunPajak::orderBy('kode_akun');

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('aksi', function ($r) {
                return '
                <button class="btn btn-warning btn-sm edit"
                    data-id="'.$r->id.'">Edit</button>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_akun'=>'required',
            'nama_akun'=>'required',
            'jenis_pajak'=>'required'
        ]);

        TbAkunPajak::create($request->all());

        return response()->json(['message'=>'Akun pajak disimpan']);
    }

    public function show($id)
    {
        return TbAkunPajak::findOrFail($id);
    }
}

