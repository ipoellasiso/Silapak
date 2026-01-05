@extends('Template.Layout')

@section('content')

<div class="card">
    <div class="card-body">

        {{-- HEADER --}}
        <div class="row">
            <div class="col-md-6">
                <h4 class="card-title">{{ $title }}</h4>
            </div>
        </div>

        <hr>

        {{-- TABS --}}
        <ul class="nav nav-tabs" id="tbpTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#pengajuan">Pengajuan TBP</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#belum">Belum Diverifikasi</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#terima">Terima</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tolak">Tolak</a>
            </li>
        </ul>

        <div class="tab-content mt-3">

            {{-- ===================== TAB PENGAJUAN ===================== --}}
            <div class="tab-pane fade show active" id="pengajuan">

                <form method="POST" action="{{ url('simpanjsontbp') }}">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>NOMOR SPM</label>
                            <input type="text" name="no_spm" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label>Isi Data JSON TBP (dari SIPD)</label>
                            <textarea name="jsontextareatbp" class="form-control" rows="8" required></textarea>
                        </div>
                    </div>

                    <button class="btn btn-outline-primary">
                        <i class="fas fa-save"></i> Ajukan Pajak TBP
                    </button>
                </form>

                <hr>

                <div class="table-responsive mt-3">
                    <table id="datatable_tbp_list" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th width="40">No</th>
                                <th width="280">Nomor TBP</th>
                                <th width="280">Nomor SPM</th>
                                <th width="110">Tanggal</th>
                                <th width="140">Nilai TBP</th>
                                <th width="140">Nilai Pajak</th>
                                <th width="130">Status</th>
                                <th width="90">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>

            {{-- ===================== TAB BELUM VERIFIKASI ===================== --}}
            <div class="tab-pane fade" id="belum">
                <div class="table-responsive">
                    <table id="datatable_tbp_belum" class="table table-hover" style="width:100%">
                        <thead>
                            <tr class="text-center">
                                <th width="40">No</th>
                                <th width="300">Nomor TBP</th>
                                <th width="180">Jenis Pajak</th>
                                <th width="120">Nilai Pajak</th>
                                <th width="130">Status</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            {{-- ===================== TAB TERIMA ===================== --}}
            <div class="tab-pane fade" id="terima">
                <div class="table-responsive">
                    <table id="datatable_tbp_terima" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor TBP</th>
                                <th>Jenis Pajak</th>
                                <th>Nilai Pajak</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            {{-- ===================== TAB TOLAK ===================== --}}
            <div class="tab-pane fade" id="tolak">
                <div class="table-responsive">
                    <table id="datatable_tbp_tolak" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor TBP</th>
                                <th>Jenis Pajak</th>
                                <th>Nilai Pajak</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>
</div>

@include('Tarik_data.Modal.Edittbp')
@include('Tarik_data.Fungsi.Fungsi')

@if(session('status'))
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: '{{ session('status') }}',
    timer: 2000,
    showConfirmButton: false
});
</script>
@endif

@if(session('error'))
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal',
    text: '{{ session('error') }}'
});
</script>
@endif

@endsection


