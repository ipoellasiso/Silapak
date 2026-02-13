@extends('Template.Layout')
@section('content')


<div class="card">
<!-- <div class="card-body"> -->
<ul class="nav nav-tabs nav-justified" id="myTabJustified" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="home-tab-justified" data-toggle="tab" href="#pajakls" role="tab" aria-controls="home-justified" aria-selected="true">{{ $title }}</a>
    </li>
</ul>

<div class="tab-content m-t-15" id="myTabContentJustified">
    <div class="tab-pane fade show active" id="lihatpajakls" role="tabpanel" aria-labelledby="home-tab-justified">    

            <!-- Content Wrapper START -->
            
                <div class="container">
                    <div class="card">
                        <img class="card-img-top" src="/app/assets/images/bukti_pemby_pajak/{{ $lihatpajakls->bukti_pemby }}" alt="">
                    </div>
                </div>

                <div class="container">
                    <div class="m-t-15 lh-2">
                        <div class="inline-block">
                            <img class="img-fluid" src="assets/images/logo/logo.png" alt="">
                            <address class="p-l-10">
                                <span class="font-weight-semibold text-dark">PEMERINTAH KOTA PALU</span><br>
                                <span>Badan Pengelola Keuangan Dan Aset Daerah (BPKAD)</span><br>
                                <span>Simelajang</span><br>
                                <abbr class="text-dark" title="Phone">Phone:</abbr>
                                <span>(0451) 123-4567</span>
                            </address>
                        </div>
                        <div class="float-right">
                            <a href="/tampilpajakls1" type="button" class="btn btn-secondary" data-dismiss="modal">
                                <i class="anticon anticon-undo"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-7">
                                    <div class="media m-b-30">
                                        <div class="avatar avatar-image">
                                            <img src="/app/assets/images/logo/logo-fold3.png" alt="">
                                        </div>
                                        <div class="media-body m-l-20">
                                            <h6 class="m-b-0">TANGGAL SPM :</h6>
                                            <span class="font-size-13 text-gray">{{ $lihatpajakls->tanggal_spm }}</span>
                                        </div>
                                    </div>
                                    <div class="media m-b-30">
                                        <div class="avatar avatar-image">
                                            <img src="/app/assets/images/logo/logo-fold3.png" alt="">                                        </div>
                                        <div class="media-body m-l-20">
                                            <h6 class="m-b-0">NOMOR SPM :</h6>
                                            <span class="font-size-13 text-gray">{{ $lihatpajakls->nomor_spm }}</span>
                                        </div>
                                    </div>
                                    <div class="media m-b-30">
                                        <div class="avatar avatar-image">
                                            <img src="/app/assets/images/logo/logo-fold3.png" alt="">
                                        </div>
                                        <div class="media-body m-l-20">
                                            <h6 class="m-b-0">NILAI SPM :</h6>
                                            <span class="font-size-13 text-gray">{{ $lihatpajakls->nilai_sp2d }}</span>
                                        </div>
                                    </div>
                                    <div class="media m-b-30">
                                        <div class="avatar avatar-image">
                                            <img src="/app/assets/images/logo/logo-fold3.png" alt="">
                                        </div>
                                        <div class="media-body m-l-20">
                                            <h6 class="m-b-0">TANGGAL SP2D :</h6>
                                            <span class="font-size-13 text-gray">{{ $lihatpajakls->nomor_sp2d }}</span>
                                        </div>
                                    </div>
                                    <div class="media m-b-30">
                                        <div class="avatar avatar-image">
                                            <img src="/app/assets/images/logo/logo-fold3.png" alt="">                                        </div>
                                        <div class="media-body m-l-20">
                                            <h6 class="m-b-0">NOMOR SP2D :</h6>
                                            <span class="font-size-13 text-gray">{{ $lihatpajakls->nomor_sp2d }}</span>
                                        </div>
                                    </div>
                                    <div class="media m-b-30">
                                        <div class="avatar avatar-image">
                                            <img src="/app/assets/images/logo/logo-fold3.png" alt="">
                                        </div>
                                        <div class="media-body m-l-20">
                                            <h6 class="m-b-0">NILAI SP2D :</h6>
                                            <span class="font-size-13 text-gray">{{ $lihatpajakls->nilai_sp2d }}</span>
                                        </div>
                                    </div>
                                    <div class="media m-b-30">
                                        <div class="avatar avatar-image">
                                            <img src="/app/assets/images/logo/logo-fold3.png" alt="">
                                        </div>
                                        <div class="media-body m-l-20">
                                            <h6 class="m-b-0">REKENING BELANJA :</h6>
                                            <span class="font-size-13 text-gray">{{ $lihatpajakls->rek_belanja }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="media m-b-30">
                                        <div class="avatar avatar-image">
                                            <img src="/app/assets/images/logo/logo-fold3.png" alt="">                                        </div>
                                        <div class="media-body m-l-20">
                                            <h6 class="m-b-0">AKUN PAJAK :</h6>
                                            <span class="font-size-13 text-gray">{{ $lihatpajakls->akun_pajak }}</span>
                                        </div>
                                    </div>
                                    <div class="media m-b-30">
                                        <div class="avatar avatar-image">
                                            <img src="/app/assets/images/logo/logo-fold3.png" alt="">                                        </div>
                                        <div class="media-body m-l-20">
                                            <h6 class="m-b-0">JENIS PAJAK :</h6>
                                            <span class="font-size-13 text-gray">{{ $lihatpajakls->jenis_pajak }}</span>
                                        </div>
                                    </div>
                                    <div class="media m-b-30">
                                        <div class="avatar avatar-image">
                                            <img src="/app/assets/images/logo/logo-fold3.png" alt="">
                                        </div>
                                        <div class="media-body m-l-20">
                                            <h6 class="m-b-0">NILAI PAJAK :</h6>
                                            <span class="font-size-13 text-gray">{{ $lihatpajakls->nilai_pajak }}</span>
                                        </div>
                                    </div>
                                    <div class="media m-b-30">
                                        <div class="avatar avatar-image">
                                            <img src="/app/assets/images/logo/logo-fold3.png" alt="">
                                        </div>
                                        <div class="media-body m-l-20">
                                            <h6 class="m-b-0">NAMA NPWP :</h6>
                                            <span class="font-size-13 text-gray">{{ $lihatpajakls->nama_npwp }}</span>
                                        </div>
                                    </div>
                                    <div class="media m-b-30">
                                        <div class="avatar avatar-image">
                                            <img src="/app/assets/images/logo/logo-fold3.png" alt="">                                        </div>
                                        <div class="media-body m-l-20">
                                            <h6 class="m-b-0">NOMOR NPWP :</h6>
                                            <span class="font-size-13 text-gray">{{ $lihatpajakls->nomor_npwp }}</span>
                                        </div>
                                    </div>
                                    <div class="media m-b-30">
                                        <div class="avatar avatar-image">
                                            <img src="/app/assets/images/logo/logo-fold3.png" alt="">
                                        </div>
                                        <div class="media-body m-l-20">
                                            <h6 class="m-b-0">EBILLING :</h6>
                                            <span class="font-size-13 text-gray">{{ $lihatpajakls->ebilling }}</span>
                                        </div>
                                    </div>
                                    <div class="media m-b-30">
                                        <div class="avatar avatar-image">
                                            <img src="/app/assets/images/logo/logo-fold3.png" alt="">
                                        </div>
                                        <div class="media-body m-l-20">
                                            <h6 class="m-b-0">NTPN :</h6>
                                            <span class="font-size-13 text-gray">{{ $lihatpajakls->ntpn }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>

@include('Penatausahaan.Pajakls1.Fungsi.Fungsipajaklssipd')
@include('Penatausahaan.Pajakls1.Modal.Datapajakls')
@include('Penatausahaan.Pajakls1.Modal.Tambah')
@include('Penatausahaan.Pajakls1.Fungsi.Fungsi')
@endsection