@extends('Template.Layout')

@section('content')
<h4>{{ $title }}</h4>

<br>

{{-- FILTER --}}
<div class="row mb-3">
    <div class="col-md-3">
        <select id="filter-opd" class="form-control">
            <option value="">Semua OPD</option>
            @foreach($listOpd as $o)
                <option value="{{ $o->nama_opd }}">{{ $o->nama_opd }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <select id="filter-bulan" class="form-control">
            <option value="">Semua Bulan</option>
            @for($i=1;$i<=12;$i++)
                <option value="{{ $i }}">{{ DateTime::createFromFormat('!m',$i)->format('F') }}</option>
            @endfor
        </select>
    </div>

    <div class="col-md-3">
        <select id="filter-tahun" class="form-control">
            @for($t=2023;$t<=date('Y')+1;$t++)
                <option value="{{ $t }}" {{ $t==2026?'selected':'' }}>{{ $t }}</option>
            @endfor
        </select>
    </div>

    <div class="col-md-3">
        <button id="btn-filter" class="btn btn-primary">Tampilkan</button>
        <button id="btn-export" class="btn btn-info">Export Excel</button>
    </div>
</div>

{{-- TAB --}}
<ul class="nav nav-tabs mb-2" id="rekonTab">
    <li class="nav-item">
        <a class="nav-link active" data-jenis="GU" href="#">GU</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-jenis="LS" href="#">LS</a>
    </li>
</ul>

<input type="hidden" id="jenis" value="GU">

{{-- ACTION BAR (TIDAK DIHAPUS DATATABLE) --}}
<div class="mb-3 border p-2 rounded bg-light">

    {{-- TOMBOL UTAMA --}}
    <button id="btn-pelaporan-pajak"
        class="btn btn-primary me-2">
        Pelaporan Pajak KPPN
    </button>
    <button id="btn-pelaporan-filter" class="btn btn-outline-primary">
        Pelaporan Pajak (Filter)
    </button>

    {{-- TOGGLE MODE --}}
    <button id="btn-toggle-posting"
        class="btn btn-outline-secondary">
        Mode Posting
    </button>

    {{-- GROUP POSTING (HIDDEN DEFAULT) --}}
    <div id="posting-area" class="d-none mt-2">

        <div class="btn-group me-2">
            <button id="btn-posting-select" class="btn btn-success">
                Posting (Select)
            </button>
            <button id="btn-posting-massal" class="btn btn-outline-success">
                Posting Massal
            </button>
        </div>

        <div class="btn-group">
            <button id="btn-unposting-select" class="btn btn-warning">
                UnPosting (Select)
            </button>
            <button id="btn-unposting-massal" class="btn btn-outline-danger">
                UnPosting Massal
            </button>
        </div>

    </div>

</div>


{{-- TABLE --}}
<table class="table table-bordered" id="table-rekon" width="100%"></table>

@include('bpkad.rekon_pajak_kpp.Fungsi.Fungsi')

@endsection
