@extends('Template.Layout')

@section('content')
<div class="container-fluid">

    <h4>{{ $title }}</h4>

    <br>
    <div class="row mb-3">
        <div class="col-md-3">
            <select id="filter-opd" class="form-control">
                <option value="">-- Semua OPD --</option>
                @foreach($listOpd as $opd)
                    <option value="{{ $opd->nama_opd }}">
                        {{ $opd->nama_opd }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="col-md-2">
            <select id="filter-bulan" class="form-control">
                <option value="">-- Semua Bulan --</option>
                @for($i=1;$i<=12;$i++)
                    <option value="{{ sprintf('%02d',$i) }}">
                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                    </option>
                @endfor
            </select>
        </div>

        <div class="col-md-2">
            <select id="filter-tahun" class="form-control">
                @for($y = date('Y'); $y >= date('Y')-5; $y--)
                    <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
        </div>

        <div class="col-md-2">
            <button class="btn btn-primary" id="btn-filter">
                Tampilkan
            </button>
        </div>
    </div>

    <br>
    {{-- <div class="d-flex gap-2 mb-3">
        <button class="btn btn-success" id="btn-posting">
            <i class="fas fa-upload"></i> Posting Pajak
        </button>

        <button class="btn btn-outline-primary" id="btn-export">
            <i class="fas fa-file-excel"></i> Export Excel KPP
        </button>
    </div> --}}

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#sudah">
                ✅ Sudah SP2D
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#belum">
                ❌ Belum SP2D
            </a>
        </li>
        {{-- <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#belumPosting">
                ⏳ Pajak Belum Diposting
            </a>
        </li> --}}
    </ul>

    <br>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="sudah">
            <table class="table" id="table-sudah" style="width: 100%"></table>
        </div>

        <div class="tab-pane fade" id="belum">
            <table class="table" id="table-belum" style="width: 100%"></table>
        </div>

        <!-- 🔥 TAB BARU -->
        {{-- <div class="tab-pane fade" id="belumPosting">
            <table class="table" id="table-belum-posting" style="width: 100%"></table>
        </div> --}}

    </div>

</div>

@include('bpkad.laporan_pajak_kpp.Modal.Edit')
@include('bpkad.laporan_pajak_kpp.Fungsi.Fungsi')

@endsection

