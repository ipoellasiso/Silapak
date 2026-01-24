@extends('Template.Layout')

@section('content')

<style>
    .log-json {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 8px;
        font-size: 12px;
        max-width: 100%;
        white-space: pre-wrap;   /* ⬅️ ini kunci */
        word-break: break-word;  /* ⬅️ ini kunci */
    }

    #modalEditLs .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
</style>

<div class="container-fluid">

    <h4>{{ $title }}</h4>

    <br>
    {{-- ================= FILTER ================= --}}
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
                @for($y = date('Y'); $y >= date('Y')-1; $y--)
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

    {{-- ================= TAB ================= --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#sudah">
                ✅ Sudah Input
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#belum">
                ❌ Belum Input
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="sudah">
            <table class="table" id="table-sudah"></table>
        </div>

        <div class="tab-pane fade" id="belum">
            <table class="table" id="table-belum" style="width:100%"></table>
        </div>
    </div>

</div>

{{-- ================= MODAL EDIT LS ================= --}}
@include('bpkad.laporan_pajak_ls.Modal.Edit')
@include('bpkad.laporan_pajak_ls.Fungsi.Fungsi')

@endsection
