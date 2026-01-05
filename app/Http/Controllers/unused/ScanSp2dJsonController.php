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

class ScanSp2dJsonController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index ()
    {
        $userId = Auth::guard('web')->user()->id;
        $data1 = array(
            'title'                 => 'Scan Sp2d',
            'active_home'           => 'active',
            'active_subscan'        => 'active',
            // 'active_sideopd'        => 'active',
            'breadcumd'             => 'Home',
            'breadcumd1'            => 'Dashboard',
            'breadcumd2'            => 'Dashboard',
            'userx'                => UserModel::where('id',$userId)->first(['fullname','role','gambar','tahun']),
        );

        return view('Master_Data.Scan_Sp2d.Scansp2d', $data1);
    }

    public function upload(Request $request)
    {
        $nomoracak = Str::random(10);

        $request->validate([
            'file' => 'required|mimes:jpg,jpeg,png,pdf'
        ]);

        $filePath = $request->file('file')->getPathname();

        // OCR
        $text = (new TesseractOCR($filePath))->lang('ind')->run();

        // --- Parsing rincian belanja (tabel) ---
        $lines = preg_split('/\r\n|\r|\n/', $text);

        $detailBelanja = [];
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Cari pola: Kode rekening + Uraian + Rp
            if (preg_match('/^([0-9\.\-]+)\s+(.+?)\s+Rp\s*([0-9\.\,]+)/', $line, $m)) {
                $kodeRekening = trim($m[1]);
                $uraian       = trim($m[2]);
                $jumlah       = str_replace(['.', ','], ['', '.'], $m[3]); // hilangkan titik, koma ganti titik

                $detailBelanja[] = [
                    "kode_rekening" => $kodeRekening,
                    "uraian" => $uraian,
                    "jumlah" => $jumlah
                ];
            }
        }

        // Simpan ke parsed
        $parsed["detail_belanja"] = $detailBelanja;


        // dd($text);
        // Parsing
        function ambilData($pattern, $text) {
            if (preg_match($pattern, $text, $m)) {
                return trim($m[1]);
            }
            return null;
        }

        $parsed = [
            "nomor_spm"     => ambilData('/Nomor\s*SPM\s*[:\-]?\s*([0-9\/\.\-A-Z]+)/i', $text),
            "nomor_sp2d" => ambilData('/Nomor\s*[:\-]?\s*([0-9\/\.\-A-Z]+)/i', $text),
            "tanggal_sp2d"  => ambilData('/Tanggal\s*[:\-]?\s*(.+?)(?=\n|SKPD|Bank)/i', $text),
            "skpd"          => ambilData('/SKPD\s*[:\-]?\s*(.+?)(?=\n|Bank)/i', $text),
            "bank_pengirim" => ambilData('/Bank Pengirim.*?\s*[:\-]?\s*(.+?)(?=\n|No)/i', $text),
            "rekening_tujuan" => ambilData('/Rekening.*?[:\-]?\s*([0-9]+)/i', $text),
            "nama_rekening" => ambilData('/Nama.*Rekening.*?[:\-]?\s*(.+?)(?=\n|Bank)/i', $text),
            "bank_penerima" => ambilData('/Bank Penerima.*?[:\-]?\s*(.+?)(?=\n|NPWP)/i', $text),
            "npwp"          => ambilData('/NPWP.*?[:\-]?\s*([0-9]+)/i', $text),
            "keperluan" => ambilData('/Keperluan.*?(?:Untuk)?\s*[:\-]?\s*(.+?)(?=\n|Jumlah|Rp)/i', $text),
            "jumlah_total"  => ambilData('/Rp\s*([0-9\.\,]+)/i', $text),
        ];

        if (empty($parsed['keperluan'])) {
            $lines = preg_split('/\r\n|\r|\n/', $text);

            $foundKeperluan = false;
            foreach ($lines as $line) {
                $line = trim($line);

                // Setelah ketemu kata Keperluan, flag aktif
                if (stripos($line, 'Keperluan') === 0) {
                    $foundKeperluan = true;
                    continue;
                }

                if ($foundKeperluan) {
                    // Lewati baris yang cuma "Untuk" atau "Pagu Anggaran"
                    if ($line === '' || strcasecmp($line, 'Untuk') === 0 || stripos($line, 'Pagu Anggaran') === 0) {
                        continue;
                    }

                    // Baris ini dianggap sebagai isi keperluan
                    $parsed['keperluan'] = $line;
                    break;
                }
            }
        }
        
        // dd($text);

        if (!empty($parsed['jumlah_total'])) {
            // hapus semua titik (pemisah ribuan), ganti koma jadi titik (decimal)
            $clean = str_replace('.', '', $parsed['jumlah_total']);
            $clean = str_replace(',', '.', $clean);

            // hasil: "7726875.00"
            // $parsed['jumlah_total'] = $clean;
        }


        // Simpan header SP2D
        $sp2d = Sp2dModel::create([
            "idhalaman" => $nomoracak,
            "nomor_spm" => $parsed["nomor_spm"],
            "nomor_sp2d" => $parsed["nomor_sp2d"],
            "tanggal_sp2d" => date('Y-m-d', strtotime($parsed['tanggal_sp2d'])),
            "nama_skpd" => $parsed["skpd"],
            // "bank_pengirim" => $parsed["bank_pengirim"],
            "no_rek_pihak_ketiga" => $parsed["rekening_tujuan"],
            "Nama_rek_pihak_ketiga" => $parsed["nama_rekening"],
            "bank_pihak_ketiga" => $parsed["bank_penerima"],
            "npwp_pihak_ketiga" => $parsed["npwp"],
            "keterangan_sp2d" => $parsed["keperluan"],
            "nilai_sp2d" => $clean
        ]);

        // Simpan detail belanja
        foreach ($parsed["detail_belanja"] ?? [] as $row) {
            BelanjalsguModel::create([
                "id_sp2d" => $nomoracak,
                // "sp2d_id" => $sp2d->id,
                "norekening" => $row["kode_rekening"],
                "uraian" => $row["uraian"],
                "nilai" => $row["jumlah"]
            ]);
        }

        // Simpan potongan (jika ada)
        foreach ($parsed["detail_potongan"] ?? [] as $row) {
            PotonganModel::create([
                "sp2d_id" => $sp2d->id,
                "jenis_potongan" => $row["jenis_potongan"],
                "uraian" => $row["uraian"],
                "jumlah" => $row["jumlah"]
            ]);
        }

        dd($parsed, $sp2d);

        return redirect('tampilrsp2dupload')->with('success', 'SP2D berhasil diproses dan disimpan ke database.');
    }
}
