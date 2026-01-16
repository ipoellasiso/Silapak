<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\UserModel;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:web');
    }

    public function index(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;

        // ================ FILTER TAHUN ==================
        $tahun = $request->tahun ?? date('Y');

        $listTahun = DB::table('tb_potongangu')
            ->selectRaw('YEAR(created_at) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        // ================ STATISTIK CARD ==================
        $totalOpd  = DB::table('opd')->count();
        $totalUser = DB::table('users')->count();

        $totalBelum  = DB::table('tb_potongangu')->where('status1', 1)->count();
        $totalTerima = DB::table('tb_potongangu')->where('status1', 0)->count();
        $totalTolak  = DB::table('tb_potongangu')->where('status1', 2)->count();

        // ================ LINE CHART PER BULAN ==================
        $grafikBulan = array_fill(1, 12, 0);

        $dataBulan = DB::table('tb_potongangu')
            ->selectRaw('MONTH(created_at) as bulan, SUM(nilai_tbp_pajak_potongan) as total')
            ->whereYear('created_at', $tahun)
            ->groupBy('bulan')
            ->get();

        foreach ($dataBulan as $item) {
            $grafikBulan[$item->bulan] = (int) $item->total;
        }

        // ================ PIE CHART STATUS ==================
        $statusBelum  = DB::table('tb_potongangu')->where('status1', 1)->count();
        $statusTerima = DB::table('tb_potongangu')->where('status1', 0)->count();
        $statusTolak  = DB::table('tb_potongangu')->where('status1', 2)->count();

        // ================ RETURN DATA ==================
        return view('Dashboard.Dashboard_admin', [
            'title'       => 'Dashboard',
            'breadcumd'   => 'Home',
            'breadcumd1'  => 'Dashboard',
            'breadcumd2'  => 'Dashboard',
            'userx'       => UserModel::find($userId),
            'totalOpd'    => $totalOpd,
            'totalUser'   => $totalUser,
            'totalBelum'  => $totalBelum,
            'totalTerima' => $totalTerima,
            'totalTolak'  => $totalTolak,
            'chartBulan'  => array_values($grafikBulan),
            'tahun'       => $tahun,
            'listTahun'   => $listTahun,
            'statusPie'   => [
                'belum'  => $statusBelum,
                'terima' => $statusTerima,
                'tolak'  => $statusTolak
            ]
        ]);
    }
}
