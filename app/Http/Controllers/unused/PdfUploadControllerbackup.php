<?php

namespace App\Http\Controllers;

use App\Models\BelanjalsguModel;
use App\Models\PotonganModel;
use App\Models\Sp2dLogModel;
use App\Models\Sp2dModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class PdfUploadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ✅ Halaman utama (render tampilan)
    public function index()
    {
        $userId = Auth::guard('web')->user()->id;

        $data1 = [
            'title'                 => 'Data SP2D',
            'active_sub'            => 'active',
            'active_side_datasp2d'  => 'active',
            'breadcumd'             => 'Home',
            'breadcumd1'            => 'Data',
            'breadcumd2'            => 'SP2D',
            'userx'                 => UserModel::find($userId, ['fullname', 'role', 'gambar', 'tahun']),
        ];
         return view('Penatausahaan.Penerimaan.Data_Sp2d.Upload_modern', $data1);
    }
        
    protected function cleanNumber($val)
    {
        if (!$val) return 0;
        $v = trim($val);
        $v = preg_replace('/[^\d\.,]/', '', $v);
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);
        return (float) $v;
    }

    private function convertIndonesianDate($tanggal)
    {
        $bulan = [
            'Januari' => '01', 'Februari' => '02', 'Maret' => '03',
            'April' => '04', 'Mei' => '05', 'Juni' => '06',
            'Juli' => '07', 'Agustus' => '08', 'September' => '09',
            'Oktober' => '10', 'November' => '11', 'Desember' => '12'
        ];

        // Misal: "01 Oktober 2025"
        if (preg_match('/(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})/', trim($tanggal), $m)) {
            $hari = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $bulanAngka = $bulan[$m[2]] ?? '01';
            $tahun = $m[3];
            return "$tahun-$bulanAngka-$hari";
        }
        

        return now()->format('Y-m-d'); // fallback jika format aneh
    }

    public function upload(Request $request)
    {
        
        $request->validate([
            'pdf' => 'required|mimes:pdf|max:8192',
        ]);

        $file = $request->file('pdf');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('uploads/pdf', $fileName, 'public');

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile(storage_path('app/public/' . $path));
        $text = $pdf->getText();
        // dd($text);

        // === NORMALISASI TEKS ===
        $text = str_replace(["\t", "\r"], ' ', $text);
        $text = preg_replace('/\s{2,}/', ' ', $text);

        // === HEADER ===
        preg_match('/Nomor\s*SPM\s*[:\s]*(.+)/i', $text, $nomor_spm);
        preg_match('/Nomor\s*[:\s]*(\S+)/i', $text, $nomor_sp2d);
        preg_match('/Tanggal\s*[:\s]*(.+)/i', $text, $tanggal_sp2d);
        preg_match('/SKPD\s*[:\s]*(.+)/i', $text, $nama_skpd);
        preg_match('/Dari\s*[:\s]*(.+)/i', $text, $nama_bud_kbud);
        preg_match('/Tahun\s*Anggaran\s*[:\s]*(\d{4})/i', $text, $tahun);
        preg_match('/Bank\s*Pengirim\s*[:\s]*(.+)/i', $text, $nama_bank);
        preg_match('/Nomor\s*Rekening\s*(?:Bank)?\s*(\d+)/i', $text, $nomor_rekening);
        preg_match('/Kepada\s*[:\s]*(.+)/i', $text, $nama_pihak_ketiga);
        preg_match('/NPWP\s*[:\s]*([0-9\.]+)/i', $text, $npwp_pihak_ketiga);
        preg_match('/No\.\s*Rekening\s*Bank\s*[:\s]*([0-9]+)/i', $text, $no_rek_pihak_ketiga);
        preg_match('/Nama\s+di\s+Rekening\s+Bank\s*[:\s]*(.+)/i', $text, $nama_rek_pihak_ketiga);
        preg_match('/Bank\s+Penerima\s*[:\s]*(.+)/i', $text, $bank_pihak_ketiga);
        preg_match('/Keperluan\s+Untuk\s*[:\s]*(.+)/i', $text, $keterangan_sp2d);
        preg_match('/Uang\s+sebesar\s*Rp?\.?\s*([0-9\.,]+)/i', $text, $nilai_sp2d);
        preg_match('/Tanggal\s+[:\s]*(\d{1,2}\s+[A-Za-z]+\s+\d{4})/i', $text, $tanggal_spm);
        preg_match('/Kota\s+([A-Za-z\s]+),/i', $text, $nama_ibu_kota);
        preg_match('/KUASA\s+BENDAHARA\s+UMUM\s+DAERAH\s*\n([A-Z\s\.\']+)/i', $text, $nama_bud_kbud);
        preg_match('/NIP\s*([0-9]+)/i', $text, $nip_bud_kbud);

        // dd([
        //     'tanggal_sp2d_raw' => $tanggal_sp2d[1] ?? null,
        //     'tanggal_sp2d_fix' => !empty($tanggal_sp2d[1]) ? $this->convertIndonesianDate($tanggal_sp2d[1]) : null,
        //     ]);

        $idhalaman = 'SP2D-' . date('YmdHis') . '-' . rand(1000, 9999);

        $header_sp2d = [
            'idhalaman' => $idhalaman,
            'jenis' => 'SP2D',
            'tahun' => trim($tahun[1] ?? ''),
            'nomor_rekening' => trim($nomor_rekening[1] ?? ''),
            'nama_bank' => trim($nama_bank[1] ?? ''),
            'nomor_sp2d' => trim($nomor_sp2d[1] ?? ''),
            'tanggal_sp2d' => !empty($tanggal_sp2d[1]) ? $this->convertIndonesianDate($tanggal_sp2d[1]) : null,
            'nama_skpd' => trim($nama_skpd[1] ?? ''),
            'nama_sub_skpd' => '',
            'nama_pihak_ketiga' => trim($nama_pihak_ketiga[1] ?? ''),
            'no_rek_pihak_ketiga' => trim($no_rek_pihak_ketiga[1] ?? ''),
            'nama_rek_pihak_ketiga' => trim($nama_rek_pihak_ketiga[1] ?? ''),
            'bank_pihak_ketiga' => trim($bank_pihak_ketiga[1] ?? ''),
            'npwp_pihak_ketiga' => trim($npwp_pihak_ketiga[1] ?? ''),
            'keterangan_sp2d' => trim($keterangan_sp2d[1] ?? ''),
            'nilai_sp2d' => $this->cleanNumber($nilai_sp2d[1] ?? 0),
            'nomor_spm' => trim($nomor_spm[1] ?? ''),
            'tanggal_spm' => !empty($tanggal_spm[1]) ? $this->convertIndonesianDate($tanggal_spm[1]) : null,
            'nama_ibu_kota' => trim($nama_ibu_kota[1] ?? 'Kota Palu'),
            'nama_bud_kbud' => trim($nama_bud_kbud[1] ?? ''),
            'jabatan_bud_kbud' => 'Kuasa BUD',
            'nip_bud_kbud' => trim($nip_bud_kbud[1] ?? ''),
            'status1' => 'Draft',
            'status2' => 'Belum Diverifikasi'
        ];

        // === INISIALISASI ===
        $kegiatan = [];
        $sub_kegiatan = [];
        $rekening_belanja = [];
        $potongan = [];

        // === KEGIATAN & SUB KEGIATAN ===
        $lines = preg_split("/\r\n|\n|\r/", trim($text));

        foreach ($lines as $line) {
            $line = trim(preg_replace('/\s+/', ' ', $line));
            if ($line === '') continue;

            // SUB KEGIATAN
            if (preg_match('/^\d*\s*(5[\.\s]\d{2}[\.\s]\d{2}[\.\s]\d{1,2}[\.\s]\d{2}[\.\s]\d{4})\s*[-–]?\s*(.+)$/u', $line, $m)) {
                $kode = trim(str_replace(' ', '.', $m[1]));
                $uraian = trim(preg_replace('/^[-–\s\.]+/', '', $m[2]));
                $uraian = preg_replace('/Rp[\d\.\,]+.*/', '', $uraian);
                $sub_kegiatan[] = [
                    'kode' => $kode,
                    'uraian' => $uraian
                ];
                continue;
            }

            // KEGIATAN
            if (preg_match('/^\d*\s*(5[\.\s]\d{2}[\.\s]\d{2}[\.\s]\d{1,2}[\.\s]\d{2})\s*[-–]?\s*(.+)$/u', $line, $m)) {
                $kode = trim(str_replace(' ', '.', $m[1]));
                $uraian = trim(preg_replace('/^[-–\s\.]+/', '', $m[2]));
                $uraian = preg_replace('/Rp[\d\.\,]+.*/', '', $uraian);
                $kegiatan[] = [
                    'kode' => $kode,
                    'uraian' => $uraian
                ];
                continue;
            }

            // === POTONGAN UMUM (AUTO-DETECT SELURUH TABEL) ===
            // === POTONGAN — versi cerdas ===
            $potongan = [];
            $start = stripos($text, 'Potongan-Potongan');
            if ($start !== false) {
                $block = substr($text, $start);
                $block = preg_replace('/\s{2,}/', ' ', $block);

                // 💡 Pola fleksibel: tangkap semua baris dengan "nomor + uraian + Rp + angka"
                // Bisa mendeteksi "Iuran Wajib Pegawai8%" atau "Iuran Jaminan Kesehatan 4%"
                if (preg_match_all('/\d+\s+([A-Za-z\s%()0-9\/\-]+?)\s+Rp\s*([\d\.,]+)/u', $block, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $m) {
                        $uraian = ucwords(strtolower(trim(preg_replace('/\s+/', ' ', $m[1]))));
                        $nilai = $this->cleanNumber($m[2]);

                        if ($nilai > 0 && stripos($uraian, 'jumlah') === false) {
                            $potongan[] = [
                                'uraian' => $uraian,
                                'jumlah' => $nilai,
                                'id_billing' => null
                            ];
                        }
                    }
                }
            }
        }

        // === REKENING BELANJA ===
        $start = stripos($text, 'NO KODE REKENING');
        $endCandidates = ['Potongan', 'Potongan-Potongan'];
        $end = false;
        foreach ($endCandidates as $c) {
            $pos = stripos($text, $c);
            if ($pos !== false) { $end = $pos; break; }
        }

        if ($end === false) $end = strlen($text);

        $rekening_belanja = [];

        if ($start !== false && $end > $start) {
            $block = substr($text, $start, $end - $start);
            $blockLines = preg_split("/\r\n|\n|\r/", trim($block));

            for ($i = 0; $i < count($blockLines); $i++) {
            $line = trim(preg_replace('/\s+/', ' ', $blockLines[$i]));
            if ($line === '') continue;

            // Tangkap pola dasar kode rekening (minimal 5 segmen)
            if (preg_match('/(?:^\s*\d+\s+)?(5(?:[.\s]\d{1,3}){2,6})\s*[-–]?\s*(.+)$/u', $line, $m)) {
                $rawCode = $m[1];
                $kode = preg_replace('/\s+/', '.', trim($rawCode));
                $kode = preg_replace('/\.+/', '.', $kode);
                $kode = trim($kode, '.');

                $uraianLine = trim($m[2]);
                $nilai = 0;

                // 🧠 Jika baris berikutnya hanya 4 digit (misalnya "0003"), gabungkan ke kode
                if (isset($blockLines[$i + 1]) && preg_match('/^\s*0{0,1}\d{4}\b/', trim($blockLines[$i + 1]), $nextCode)) {
                    $kode .= '.' . trim($nextCode[0]);
                    // skip line berikutnya agar tidak dianggap baris baru
                    $i++;
                }

                // Cari nilai Rp
                if (preg_match('/Rp\s*([\d\.\,]+)/i', $uraianLine, $v)) {
                    $nilai = $this->cleanNumber($v[1]);
                    $uraianLine = preg_replace('/Rp\s*[\d\.\,]+/i', '', $uraianLine);
                } elseif (isset($blockLines[$i + 1]) && preg_match('/Rp\s*([\d\.\,]+)/i', $blockLines[$i + 1], $v2)) {
                    $nilai = $this->cleanNumber($v2[1]);
                }

                $uraian = preg_replace('/^[\.\-\d\s]+/', '', $uraianLine);
                $uraian = preg_replace('/\s{2,}/', ' ', $uraian);
                $uraian = ucwords(strtolower(trim($uraian)));

                // Simpan hasil valid
                if (strlen($kode) >= 10 && stripos($uraian, 'Jumlah') === false && $uraian !== '') {
                    $exists = array_filter($rekening_belanja, fn($r) => $r['kode'] === $kode && $r['nilai'] === $nilai);
                    if (empty($exists)) {
                        $rekening_belanja[] = [
                            'kode' => $kode,
                            'uraian' => $uraian,
                            'nilai' => $nilai
                        ];
                    }
                }
            }
        }

        // === HILANGKAN DUPLIKAT ===
        $kegiatan = array_values(array_unique($kegiatan, SORT_REGULAR));
        $sub_kegiatan = array_values(array_unique($sub_kegiatan, SORT_REGULAR));
        $rekening_belanja = array_values(array_unique($rekening_belanja, SORT_REGULAR));

        $nomorSp2d = trim($nomor_sp2d[1] ?? '');
        if (Sp2dModel::where('nomor_sp2d', $nomorSp2d)->exists()) {
            return response()->json([
                'success' => false,
                'message' => "❌ SP2D dengan nomor '$nomorSp2d' sudah ada di database."
            ]);
        }

        $sp2d = Sp2dModel::create($header_sp2d);

        foreach ($rekening_belanja as $rek) {
            BelanjalsguModel::create([
                'id_sp2d' => $sp2d->idhalaman,
                'norekening' => $rek['kode'],
                'uraian' => $rek['uraian'],
                'nilai' => $rek['nilai'],
                'status1' => 'Draft',
                'status2' => 'Belum Diverifikasi'
            ]);
        }

        foreach ($potongan as $p) {
            PotonganModel::create([
                'id_potongan' => $sp2d->idhalaman,
                'jenis_pajak' => $p['uraian'],
                'nilai_pajak' => $p['jumlah'],
                'ebilling' => $p['id_billing'] ?? null,
                'status1' => 'Draft',
                'status2' => 'Belum Diverifikasi'
            ]);
        }

        // return redirect('/sp2d')->with('success', 'SP2D berhasil diproses dan disimpan ke database.');
        return response()->json([
            'success' => true,
            'message' => '✅ Data SP2D berhasil disimpan!',
            'redirect' => route('sp2d.index'),
        ]);
    
        }
    } 
}
