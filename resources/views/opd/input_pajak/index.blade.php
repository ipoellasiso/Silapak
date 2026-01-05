@extends('Template.Layout')
@section('content')

<div class="card">
    <div class="card-body">
        <h4>{{ $title }}</h4>

        <br>
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#belum">
                Belum Input
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#sudah">
                Sudah Input
                </a>
            </li>
        </ul>

        <div class="tab-content mt-3">
            <div class="tab-pane fade show active" id="belum">
                <table id="tbBelum" class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nomor TBP</th>
                            <th>Jenis Pajak</th>
                            <th>Nilai Pajak</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="tab-pane fade" id="sudah">
                <table id="tbSudah" class="table table-hover" style="width:100%"></table>
            </div>
        </div>
    </div>
</div>

@include('opd.input_pajak.Fungsi.Modal.Tambah')
@include('opd.input_pajak.Fungsi.Fungsi')

@endsection
