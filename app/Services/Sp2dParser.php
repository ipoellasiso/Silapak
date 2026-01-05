<?php
namespace App\Services;
use Illuminate\Support\Str;

class Sp2dParser
{
    public static function parse(string $text): array
    {
        // Bersihkan text
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/\bmor\b/i', 'Nomor', $text);
        $text = str_replace("BADAN RISET DAN I", "BADAN RISET DAN INOVASI DAERAH", $text);
        $text = str_replace("Bank Penerima", "Bank Penerima: Bank MANDIRI", $text);

        $nomoracak = Str::random(10);
        

        // Ambil data header dengan regex
        preg_match('/Nomor SPM\s*:\s*([^\n]+)/i', $text, $m1);
        preg_match('/Nomor[:\s]+([0-9\/\.A-Z\-]+)/i', $text, $m2);
        preg_match('/Tanggal\s*:\s*([0-9]{1,2}\s?\w+\s?[0-9]{4})/i', $text, $m3);
        preg_match('/SKPD\s*:\s*(.+?)\s+Bank/i', $text, $m4);
        preg_match('/No\. Rekening Bank\s*:\s*([0-9]+)/i', $text, $m6);
        preg_match('/Nama.*Rekening.*:\s*(.+?)\s+Bank/i', $text, $m7);
        preg_match('/Dari KUASA BENDAHARA UMUM DAERAH\s*:\s*([0-9]+)/i', $text, $m9);
        preg_match('/Uang sebesar Rp\s*([0-9\.\,]+)/i', $text, $m11);

        // Ambil detail baris rekening (kode rekening, uraian, jumlah)
        preg_match_all('/([0-9\.]{5,})\s+([A-Za-z0-9\(\)\/\-\s]+?)\s+Rp([0-9\.\,]+)/', $text, $matches, PREG_SET_ORDER);

        $details = [];
        foreach ($matches as $row) {
            $details[] = [
                "kode_rekening" => trim($row[1]),
                "uraian" => trim($row[2]),
                "jumlah" => (float) str_replace(['.', ','], ['', '.'], $row[3])
            ];
        }

        return [
            "idhalaman" => $nomoracak,
            "nomor_spm" => $m1[1] ?? null,
            "nomor_sp2d" => $m2[1] ?? null,
            "tanggal_sp2d" => isset($m3[1]) ? date("Y-m-d", strtotime($m3[1])) : null,
            "skpd" => $m4[1] ?? null,
            "bank_pengirim" => "Bank Mandiri",
            "rekening_tujuan" => $m6[1] ?? null,
            "nama_rekening" => $m7[1] ?? null,
            "bank_penerima" => $m8[1] ?? null,
            "npwp" => $m9[1] ?? null,
            "keperluan" => $m10[1] ?? null,
            "jumlah_total" => isset($m11[1]) ? (float) str_replace(['.', ','], ['', '.'], $m11[1]) : 0,
            "detail_belanja" => $details, // detail belanja
            "detail_potongan" => [] // nanti parsing potongan di sini
        ];
    }
}
