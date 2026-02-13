@extends('Template.Layout')
@section('content')


{{-- <div class="card"> --}}

    <div class="tab-content m-t-15" id="myTabContentJustified">
        <div class="tab-pane fade show active" id="bku" role="tabpanel" aria-labelledby="home-tab-justified">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h4 class="card-title">{{ $title }}</h4>
                        </div>
                        <div class="col-md-7">
                        </div>
                        <div class="col-md-1">
                            <div class="btn-group dropdown me-1 mb-1">
                                <button type="button" class="btn btn-outline-primary btn-tone m-r-5 btn-xs ml-auto dropdown-toggle" id="dropdownMenuOffset"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                    data-offset="5,20">
                                    <i class="fas fa-download"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuOffset">
                                    {{-- <a class="dropdown-item" href="javascript:void(0)" id="createBku">Tambah Data</a>
                                    <a class="dropdown-item" id="createimportbku" href="#">Upload Data</a> --}}
                                    <a class="dropdown-item" href="/datarealisasi/export" data-toggle="tooltip" data-placement="top" title="klik"> Download Data </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- class="m-t-25" --}}
                    <br><br>
                    <div class="m-t-25 table-responsive">
                        <table id="data-table" class="tabelrealisasi table table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center" width="100px">OPD</th>
                                    <th class="text-center" width="100px">Nomor SPM</th>
                                    <th class="">Tanggal SP2D</th>
                                    <th class="">Nomor SP2D</th>
                                    <th class="text-center">Keterangan SP2D</th>
                                    <th width="200px">Nilai SP2D</th>
                                    <th class="text-center">Nomor Rekening</th>
                                    <th class="text-center">Rekening</th>
                                    <th class="text-right" width="100px">Nilai Belanja</th>
                                    <th class="text-center">Jenis Belanja</th>
                                    {{-- <th width="100px">Action</th> --}}
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
{{-- </div> --}}

@include()


@endsection