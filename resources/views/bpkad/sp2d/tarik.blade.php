@extends('Template.Layout')
@section('content')

<div class="card">
    <div class="card-body">

        <h4 class="card-title mb-3">{{ $title }}</h4>

        <br>
        {{-- 🔹 NAV TABS --}}
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#tab-tarik">Tarik Data Sp2d</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-ls">LS</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-gu">GU</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-kkpd">KKPD</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#tab-hapus">Hapus</a>
            </li>
        </ul>

        <br><br>
        {{-- 🔹 TAB CONTENT --}}
        <div class="tab-content">

            {{-- TAB 1: TARIK DATA --}}
            <div id="tab-tarik" class="tab-pane fade show active p-3">
                <form id="formTarikSp2d">
                    <textarea class="form-control" id="json_data" name="json_data" rows="12" placeholder="Paste JSON dari SIPD disini..."></textarea>
                    <button type="submit" id="btnProses" class="btn btn-primary mt-3">Simpan</button>
                </form>
            </div>

            {{-- TAB 2: SP2D LS --}}
            <div id="tab-ls" class="tab-pane fade p-3">
                <table id="table-ls" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No SP2D</th>
                            <th>SKPD</th>
                            <th>Pihak Ketiga</th>
                            <th>Nilai</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                </table>
            </div>

            {{-- TAB 3: SP2D GU --}}
            <div id="tab-gu" class="tab-pane fade p-3">
                <table id="table-gu" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No SP2D</th>
                            <th>SKPD</th>
                            <th>Pihak Ketiga</th>
                            <th>Nilai</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                </table>
            </div>

            {{-- TAB 4: SP2D KKPD --}}
            <div id="tab-kkpd" class="tab-pane fade p-3">
                <table id="table-kkpd" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No SP2D</th>
                            <th>SKPD</th>
                            <th>Pihak Ketiga</th>
                            <th>Nilai</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                </table>
            </div>

            {{-- TAB 5: SP2D HAPUS --}}
            <div id="tab-hapus" class="tab-pane fade p-3">
                <table id="table-hapus" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No SP2D</th>
                            <th>SKPD</th>
                            <th>Pihak Ketiga</th>
                            <th>Nilai</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>

        </div>

    </div>
</div>

@include('bpkad.sp2d.Modal.detail')
@include('bpkad.sp2d.Fungsi.Fungsi')

@endsection
