<?php

namespace App\Http\Controllers;

use App\Models\BelanjalsguModel;
use App\Models\PotonganModel;
use App\Models\Sp2dModel;
use App\Models\UserModel;
use App\Services\Sp2dParser;
use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SimpanSp2dsipdController extends Controller
{
    public function index()
    {
        $userId = Auth::guard('web')->user()->id;

        // Ambil token otomatis
        $datatoken = DB::table('token')->select('token_sipd')->first();
        $nilaitoken = $datatoken->token_sipd ?? null;

        if (!$nilaitoken) {
            return redirect()->back()->with('error', 'Token tidak ditemukan.');
        }

        // Cek masa berlaku token
        $tokenPark = explode(".", $nilaitoken);
        $payload = $tokenPark[1] ?? null;

        if (!$payload) {
            return redirect()->back()->with('error', 'Token tidak valid.');
        }

        $decode = base64_decode($payload);
        $json   = json_decode($decode, true);
        $exp    = $json['exp'] ?? 0;

        if ($exp <= time()) {
            return redirect('tampiltoken')->with('edit', 'Token Kadaluarsa');
        }

        // Ambil query string ?id= (default 1 kalau kosong)
        $page = request()->query('id', 1);
        $limit = 10;

        $cookie = '__cf_bm=UVaXpimYsNn680qrc176.e7UdZ4l.0NcbDQdEjZY69A-1759319079-1.0.1.1-1WEJP0yEY63T_x4OX1yOSJQTyznzSumYsVhmzSUUtkiz9n1P_KRgL6r4V_fL8wqW0FTSt4B5e10e_hDafA7WzmFkL21lCiLVNdUjbW64j80; path=/; expires=Wed, 01-Oct-25 12:14:39 GMT; domain=.kemendagri.go.id; HttpOnly; Secure; SameSite=None'; // ambil lengkap dari DevTools -> Headers -> Cookie

        $urlls = "https://service.sipd.kemendagri.go.id/pengeluaran/strict/sp2d/pembuatan/index?status=ditransfer&page=1&limit=10&nilai_akhir=1000000000000";

        $ch = curl_init();

        curl_setopt_array($ch, [
        CURLOPT_URL => "https://service.sipd.kemendagri.go.id/pengeluaran/strict/sp2d/pembuatan/index?status=ditransfer&page=1&limit=10&nilai_akhir=1000000000000",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, 
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Accept: application/json, text/plain, */*",
            "Accept-Encoding: gzip, deflate, br, zstd",
            "Accept-Language: id,en-US;q=0.7,en;q=0.3",
            "Authorization: Bearer $nilaitoken",
            "Connection: keep-alive",
            "Host: service.sipd.kemendagri.go.id",
            "Origin: https://sipd.kemendagri.go.id",
            "Referer: https://sipd.kemendagri.go.id/",
            "Sec-Fetch-Dest: empty",
            "Sec-Fetch-Mode: cors",
            "Sec-Fetch-Site: same-site",
            "Sec-Ch-Ua: \"Not A(Brand\";v=\"99\", \"Chromium\";v=\"115\", \"Firefox\";v=\"143\"",
            "Sec-Ch-Ua-Mobile: ?0",
            "Sec-Ch-Ua-Platform: \"Windows\"",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0",
            "Cookie: __cf_bm=UVaXpimYsNn680qrc176.e7UdZ4l.0NcbDQdEjZY69A-1759319079-1.0.1.1-1WEJP0yEY63T_x4OX1yOSJQTyznzSumYsVhmzSUUtkiz9n1P_KRgL6r4V_fL8wqW0FTSt4B5e10e_hDafA7WzmFkL21lCiLVNdUjbW64j80; path=/; expires=Wed, 01-Oct-25 12:14:39 GMT; domain=.kemendagri.go.id; HttpOnly; Secure; SameSite=None"
        ],
    ]);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);

        if ($err) {
            dd("cURL Error #: " . $err);
        } else {
            dd($info, $response);
        }

        $data1 = [
            'title'          => 'DATA SP2D SIPD',
            'active_home'    => 'active',
            'active_subsipd' => 'active',
            'breadcumd'      => 'Home',
            'breadcumd1'     => 'Dashboard',
            'breadcumd2'     => 'Dashboard',
            'userx'          => UserModel::where('id', $userId)->first(['fullname','role','gambar','tahun']),
            'dt'             => $dt ?? [] // langsung isi array
        ];

        // dd($dt ?? []);
        // dd($data);
        // dd($response->json());

        return view('Master_Data.Sp2d_Sipd.Sp2d_Sipd', $data1);
    }
}
