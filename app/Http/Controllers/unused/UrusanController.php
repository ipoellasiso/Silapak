<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use App\Models\UserModel;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class UrusanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;

        $data = [
            'title'                 => 'Data Urusan',
            'active_master_data'    => 'active',
            'active_subopd'         => 'active',
            'active_sideurusan'     => 'active',
            'breadcumd'             => 'Pengaturan',
            'breadcumd1'            => 'Master Data',
            'breadcumd2'            => 'Data Urusan',
            'userx'                 => UserModel::where('id',$userId)->first(['fullname','role','gambar','tahun']),
        ];

        if ($request->ajax()) {
            // Ambil data dari API Laravel yang berjalan di localhost
            $response = Http::get('http://127.0.0.1:8000/api/urusan');
            $urusanData = $response->json();

            return DataTables::of($urusanData)
                ->addIndexColumn()
                ->addColumn('action', function($row) {
                    $btn = '
                        <a href="javascript:void(0)" title="Edit Data" data-id="'.$row['UrusanID'].'" class="editUrusan btn btn-primary btn-sm">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <a href="javascript:void(0)" title="Hapus Data" data-id="'.$row['UrusanID'].'" class="deleteUrusan btn btn-danger btn-sm">
                            <i class="bi bi-trash3"></i>
                        </a>
                    ';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('Master_Data.Dataurusan.Tampilurusan', $data);
    }

     // 🔽 Tambahkan fungsi import di bawah ini
    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            // kirim file ke API
            $response = Http::attach(
                'file_excel',
                file_get_contents($request->file('file_excel')->getRealPath()),
                $request->file('file_excel')->getClientOriginalName()
            )->post('http://127.0.0.1:8000/api/urusan/import');

            $result = $response->json();

            if ($response->successful()) {
                return response()->json([
                    'status' => true,
                    'message' => $result['message'] ?? 'Data urusan berhasil diimport!'
                ], 200);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'] ?? 'Gagal import data!'
                ], 400);
            }

        } catch (\Exception $e) {
             dd($e->getMessage());
        }
    }

    public function exportExcel()
    {
        try {
            // Ambil data dari API
            $response = Http::get('http://127.0.0.1:8000/api/urusan');
            $urusanData = $response->json();

            if (!$response->successful() || empty($urusanData)) {
                return back()->with('error', 'Gagal mengambil data dari API!');
            }

            // Ubah jadi collection Excel-friendly
            $collection = collect($urusanData)->map(function ($item) {
                return ['nama_urusan' => $item['Nama_Urusan'] ?? ''];
            });

            // Nama file export
            $filename = 'data_urusan_' . Str::slug(now()->format('Y-m-d_His')) . '.xlsx';

            // Download file Excel langsung
            return Excel::download(new class($collection) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings {
                protected $data;
                public function __construct($data) { $this->data = $data; }
                public function collection() { return $this->data; }
                public function headings(): array { return ['nama_urusan']; }
            }, $filename);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }

}
