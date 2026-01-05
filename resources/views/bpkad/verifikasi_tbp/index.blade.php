@extends('Template.Layout')
@section('content')

<div class="card">
  <div class="card-body">
    <h4>{{ $title }}</h4>

    <br>
    <ul class="nav nav-tabs">
      <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#verifikasi">Verifikasi</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#terima">Terima</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tolak">Tolak</a>
      </li>
    </ul>

    <br>
    <div class="tab-content mt-3">

      <div class="tab-pane fade show active" id="verifikasi">
        {{-- TOMBOL AKSI MASSAL --}}
        <div class="mb-2 d-flex gap-2">
            <button class="btn btn-success btn-sm" id="btnVerifikasiPilih">
                <i class="fas fa-check"></i> Verifikasi Terpilih
            </button>

            <button class="btn btn-primary btn-sm" id="btnVerifikasiHalaman">
                <i class="fas fa-layer-group"></i> Verifikasi Halaman Ini
            </button>
        </div>
        <table id="tbVerifikasi" class="table table-hover" style="width:100%"></table>
      </div>

      <div class="tab-pane fade" id="terima">
        <table id="tbTerima" class="table table-hover" style="width:100%"></table>
      </div>

      <div class="tab-pane fade" id="tolak">
        <table id="tbTolak" class="table table-hover" style="width:100%"></table>
      </div>

    </div>
  </div>
</div>

@include('bpkad.verifikasi_tbp.Fungsi.Fungsi')

@endsection
