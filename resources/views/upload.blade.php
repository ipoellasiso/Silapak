@extends('Template.Layout')
@section('content')

<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fa fa-upload"></i> Upload Dokumen SP2D (PDF)</h5>
        </div>
        <div class="card-body">
            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Pilih File PDF</label>
                    <input type="file" name="pdf" class="form-control" accept="application/pdf" required>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="fa fa-cloud-upload-alt"></i> Upload
                </button>

                <div class="progress mt-3" style="height: 20px; display:none;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                </div>
            </form>

            <div id="preview" class="mt-4" style="display:none;">
                <h5 class="text-primary"><i class="fa fa-file-pdf"></i> Hasil Parsing SP2D</h5>
                <table class="table table-sm table-bordered mt-2">
                    <tr><th>Nomor SP2D</th><td id="prev_nomor_sp2d"></td></tr>
                    <tr><th>Nama SKPD</th><td id="prev_skpd"></td></tr>
                    <tr><th>Nilai SP2D</th><td id="prev_nilai" class="text-end fw-bold"></td></tr>
                </table>

                <h6 class="mt-4 text-primary">Rekening Belanja</h6>
                <table class="table table-bordered table-sm" id="tblBelanja">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Kode Rekening</th>
                            <th>Uraian</th>
                            <th class="text-end">Nilai (Rp)</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <h6 class="mt-4 text-danger">Potongan-potongan</h6>
                <table class="table table-bordered table-sm" id="tblPotongan">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Uraian</th>
                            <th class="text-end">Nilai (Rp)</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

                <a href="#" target="_blank" id="prev_link" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-eye"></i> Lihat PDF
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(function(){
    $('#uploadForm').on('submit', function(e){
        e.preventDefault();

        let formData = new FormData(this);
        let progressBar = $('.progress');
        let bar = $('.progress-bar');

        progressBar.show();
        bar.css('width', '0%').text('0%');

        $.ajax({
            xhr: function() {
                let xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(e) {
                    if (e.lengthComputable) {
                        let percent = Math.round((e.loaded / e.total) * 100);
                        bar.css('width', percent + '%').text(percent + '%');
                    }
                });
                return xhr;
            },
            url: "{{ route('sp2d.upload') }}",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res){
                if (res.success) {
                    bar.addClass('bg-success').text('Selesai');
                    $('#preview').show();

                    $('#prev_nomor_sp2d').text(res.preview.nomor_sp2d);
                    $('#prev_skpd').text(res.preview.nama_skpd);
                    $('#prev_nilai').text('Rp' + res.preview.nilai_sp2d);
                    $('#prev_link').attr('href', res.preview.pdf_link);

                    // Rekening Belanja
                    let belanjaHTML = '';
                    res.preview.rekening_belanja.forEach((r, i) => {
                        belanjaHTML += `<tr>
                            <td>${i+1}</td>
                            <td>${r.kode}</td>
                            <td>${r.uraian}</td>
                            <td class="text-end">Rp${r.nilai.toLocaleString('id-ID')}</td>
                        </tr>`;
                    });
                    $('#tblBelanja tbody').html(belanjaHTML);

                    // Potongan
                    let potHTML = '';
                    res.preview.potongan.forEach((p, i) => {
                        potHTML += `<tr>
                            <td>${i+1}</td>
                            <td>${p.uraian}</td>
                            <td class="text-end">Rp${p.jumlah.toLocaleString('id-ID')}</td>
                        </tr>`;
                    });
                    $('#tblPotongan tbody').html(potHTML);

                } else {
                    bar.addClass('bg-danger').text('Gagal');
                    alert(res.message);
                }
            },
            error: function(xhr){
                alert('❌ Terjadi kesalahan saat upload.');
                console.log(xhr.responseText);
            }
        });
    });
});
</script>
@endpush
