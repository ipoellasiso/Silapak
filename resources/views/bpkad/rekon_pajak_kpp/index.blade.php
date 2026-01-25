@extends('Template.Layout')

@section('content')
<h4>{{ $title }}</h4>

<br><br>
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
                <option value="{{ $i }}">
                    {{ DateTime::createFromFormat('!m',$i)->format('F') }}
                </option>
            @endfor
        </select>
    </div>

    <div class="col-md-3">
        <select id="filter-tahun" class="form-control">
            @for($t=2023;$t<=date('Y')+1;$t++)
                <option value="{{ $t }}" {{ $t == 2026 ? 'selected' : '' }}>
                    {{ $t }}
                </option>
            @endfor
        </select>
    </div>

    <div class="col-md-3">
        <button id="btn-filter" class="btn btn-primary">Tampilkan</button>
        <button id="btn-export" class="btn btn-info">Export Excel</button>
    </div>
</div>

<br>
<div class="col-md-3">
    <button id="btn-posting" class="btn btn-success">Posting</button>
    <button id="btn-unposting-massal" class="btn btn-danger">UnPosting</button>
</div>

<br><br>
<ul class="nav nav-tabs mb-3" id="rekonTab">
    <li class="nav-item">
        <a class="nav-link active" data-jenis="GU" href="#">GU</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-jenis="LS" href="#">LS</a>
    </li>
</ul>

<input type="hidden" id="jenis" value="GU">

<table class="table table-bordered" id="table-rekon" width="100%"></table>

@include('bpkad.rekon_pajak_kpp.Fungsi.Fungsi')

@endsection
