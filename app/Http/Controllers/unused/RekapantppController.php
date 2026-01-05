<?php

namespace App\Http\Controllers;

use App\Models\OpdModel;
use App\Models\User;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RekapantppController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;
        $data = array(
            'title'                => 'Rekapan TPP',
            'active_penerimaan'    => 'active',
            'active_side_rektpp'   => 'active',
            'active_sub'           => 'active',
            'breadcumd'            => 'Penatausahaan',
            'breadcumd1'           => 'Rekapan',
            'breadcumd2'           => 'TPP',
            'userx'                => UserModel::where('id',$userId)->first(['fullname','role','gambar',]),
        );

        return view('Penatausahaan.Penerimaan.Rekapan_tpp.Tampilrekapantpp', $data);
    }

    public function viewdataindex(Request $request)
    {
        $userId = Auth::guard('web')->user()->id;
        $data = array(
            'title'                => 'Rekapan TPP',
            'active_penerimaan'    => 'active',
            'active_side_rektpp'   => 'active',
            'active_sub'           => 'active',
            'breadcumd'            => 'Penatausahaan',
            'breadcumd1'           => 'Rekapan',
            'breadcumd2'           => 'TPP',
            'userx'                => UserModel::where('id',$userId)->first(['fullname','role','gambar',]),
        );

        if ($request->tampilawal) {
            $datarealisasi = DB::table('sp2d')
                            ->select('sp2d.nomor_spm', 'sp2d.nomor_sp2d', 'sp2d.tanggal_sp2d', 'sp2d.keterangan_sp2d', 'sp2d.nilai_sp2d', 'sp2d.jenis', 'sp2d.nama_skpd', 'potongan2.jenis_pajak', 'potongan2.nilai_pajak', 'potongan2.ebilling', 'sp2dtpp.periode', 'sp2dtpp.status1' )
                            // ->join('opd', 'opd.nama_opd', '=', 'sp2d.nama_skpd')
                            ->join('potongan2', 'potongan2.id_potongan', '=', 'sp2d.idhalaman')
                            ->join('sp2dtpp', 'sp2dtpp.id_sp2d', '=', 'sp2d.idhalaman')
                            ->where('sp2d.nama_skpd','like', "%".$request->nama_skpd."%")
                            ->where('sp2dtpp.periode','like', "%".$request->periode."%")
                            ->where('sp2dtpp.status1','like', "%".$request->status1."%")
                            ->where('potongan2.jenis_pajak','like', "%".$request->jenis_pajak."%")
                            ->whereBetween('sp2d.tanggal_sp2d', [$request->tgl_awal, $request->tgl_akhir])
                            ->whereIn('potongan2.jenis_pajak', ['Taspen','Taperum,','PPH 21','Pajak Pertambahan Nilai,','Pajak Penghasilan Ps 4 (2)','Pajak Penghasilan Ps 23','Pajak Penghasilan Ps 22','Jaminan Hari Tua','Iuran Wajib Pegawai 8%','Iuran Wajib Pegawai 1%','Iuran Jaminan Kesehatan 4%','Iuran Jaminan Kematian','Iuran Jaminan Kecelakaan Kerja','Belanja Tunjangan PPh/Tunjangan Khusus PNS','Belanja Iuran Jaminan Kesehatan PPPK','Belanja Iuran Jaminan Kesehatan PNS','Belanja Iuran Jaminan Kematian PPPK','Belanja Iuran Jaminan Kematian PNS','Belanja Iuran Jaminan Kecelakaan Kerja PPPK','Belanja Iuran Jaminan Kecelakaan Kerja PNS','Askes'])
                            ->get();

            return view('Penatausahaan.Penerimaan.Rekapan_tpp.Viewdatacari',['datarealisasi' => $datarealisasi,]);
        } else {
            $datarealisasi = DB::table('sp2d')
                            ->select('sp2d.nomor_spm', 'sp2d.nomor_sp2d', 'sp2d.tanggal_sp2d', 'sp2d.keterangan_sp2d', 'sp2d.nilai_sp2d', 'sp2d.jenis', 'sp2d.nama_skpd', 'potongan2.jenis_pajak', 'potongan2.nilai_pajak', 'potongan2.ebilling', 'sp2dtpp.periode', 'sp2dtpp.status1' )
                            // ->join('opd', 'opd.nama_opd', '=', 'sp2d.nama_skpd')
                            ->join('potongan2', 'potongan2.id_potongan', '=', 'sp2d.idhalaman')
                            ->join('sp2dtpp', 'sp2dtpp.id_sp2d', '=', 'sp2d.idhalaman')
                            ->where('sp2d.nama_skpd','like', "%".$request->nama_skpd."%")
                            ->where('sp2dtpp.periode','like', "%".$request->periode."%")
                            ->where('sp2dtpp.status1','like', "%".$request->status1."%")
                            ->where('potongan2.jenis_pajak','like', "%".$request->jenis_pajak."%")
                            ->whereBetween('sp2d.tanggal_sp2d', [$request->tgl_awal, $request->tgl_akhir])
                            ->whereIn('potongan2.jenis_pajak', ['Taspen','Taperum,','PPH 21','Pajak Pertambahan Nilai,','Pajak Penghasilan Ps 4 (2)','Pajak Penghasilan Ps 23','Pajak Penghasilan Ps 22','Jaminan Hari Tua','Iuran Wajib Pegawai 8%','Iuran Wajib Pegawai 1%','Iuran Jaminan Kesehatan 4%','Iuran Jaminan Kematian','Iuran Jaminan Kecelakaan Kerja','Belanja Tunjangan PPh/Tunjangan Khusus PNS','Belanja Iuran Jaminan Kesehatan PPPK','Belanja Iuran Jaminan Kesehatan PNS','Belanja Iuran Jaminan Kematian PPPK','Belanja Iuran Jaminan Kematian PNS','Belanja Iuran Jaminan Kecelakaan Kerja PPPK','Belanja Iuran Jaminan Kecelakaan Kerja PNS','Askes'])
                            ->get();
            
            $data1         = DB::table('sp2d')
                            ->select('sp2d.nomor_spm', 'sp2d.nomor_sp2d', 'sp2d.tanggal_sp2d', 'sp2d.keterangan_sp2d', 'sp2d.nilai_sp2d', 'sp2d.jenis', 'sp2d.nama_skpd', 'potongan2.jenis_pajak', 'potongan2.nilai_pajak', 'potongan2.ebilling', 'sp2dtpp.periode', 'sp2dtpp.status1' )
                            // ->join('opd', 'opd.nama_opd', '=', 'sp2d.nama_skpd')
                            ->join('potongan2', 'potongan2.id_potongan', '=', 'sp2d.idhalaman')
                            ->join('sp2dtpp', 'sp2dtpp.id_sp2d', '=', 'sp2d.idhalaman')
                            ->where('sp2d.nama_skpd','like', "%".$request->nama_skpd."%")
                            ->where('sp2dtpp.periode','like', "%".$request->periode."%")
                            ->where('sp2dtpp.status1','like', "%".$request->status1."%")
                            ->where('potongan2.jenis_pajak','like', "%".$request->jenis_pajak."%")
                            ->whereBetween('sp2d.tanggal_sp2d', [$request->tgl_awal, $request->tgl_akhir])
                            ->whereIn('potongan2.jenis_pajak', ['Taspen','Taperum,','PPH 21','Pajak Pertambahan Nilai,','Pajak Penghasilan Ps 4 (2)','Pajak Penghasilan Ps 23','Pajak Penghasilan Ps 22','Jaminan Hari Tua','Iuran Wajib Pegawai 8%','Iuran Wajib Pegawai 1%','Iuran Jaminan Kesehatan 4%','Iuran Jaminan Kematian','Iuran Jaminan Kecelakaan Kerja','Belanja Tunjangan PPh/Tunjangan Khusus PNS','Belanja Iuran Jaminan Kesehatan PPPK','Belanja Iuran Jaminan Kesehatan PNS','Belanja Iuran Jaminan Kematian PPPK','Belanja Iuran Jaminan Kematian PNS','Belanja Iuran Jaminan Kecelakaan Kerja PPPK','Belanja Iuran Jaminan Kecelakaan Kerja PNS','Askes'])
                            // ->where('sp2d.nama_skpd','like', "%".$request->nama_skpd."%")
                            ->first();
            
            return view('Penatausahaan.Penerimaan.Rekapan_tpp.Viewdataindex',[
                'data' => $data,
                'datarealisasi' => $datarealisasi,
                'data1' => $data1,
            ]);
        }
    }

    public function getDataopd(Request $request)
    {
        $search = $request->searchOpd;
  
        if($search == ''){
            $opd = OpdModel::orderBy('id','asc')->select('id','nama_opd')->get();
        }else{
            $opd = OpdModel::orderBy('id','asc')->select('id','nama_opd')->where('nama_opd', 'like', '%' .$search . '%')->get();
        }
  
        $response = array();
        foreach($opd as $row){
            $response[] = array(
                "id"   => $row->nama_opd,
                "text" => $row->nama_opd
            );
        }

        return response()->json($response); 
    }

}
