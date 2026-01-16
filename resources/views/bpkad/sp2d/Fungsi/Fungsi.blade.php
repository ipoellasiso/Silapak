<script type="text/javascript">
$(function () {

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    var tableLS = $('#table-ls').DataTable({
        processing: true,
        serverSide: true,
        ajax: "/sp2d/ls",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'nomor_sp2d', name: 'nomor_sp2d'},
            {data: 'nama_skpd', name: 'nama_skpd'},
            {data: 'nama_pihak_ketiga', name: 'nama_pihak_ketiga'},
            {data: 'nilai_sp2d', name: 'nilai_sp2d'},
            {data: 'tanggal_sp2d', name: 'tanggal_sp2d'},
            // {data:'aksi', name:'aksi', orderable:false, searchable:false, className:'text-center'},
        ]
    });

    var tableGU = $('#table-gu').DataTable({
        processing: true,
        serverSide: true,
        ajax: "/sp2d/gu",
    });

    var tableKKPD = $('#table-kkpd').DataTable({
        processing: true,
        serverSide: true,
        ajax: "/sp2d/kkpd",
    });

    var tableHapus = $('#table-hapus').DataTable({
        processing: true,
        serverSide: true,
        ajax: "/sp2d/hapus",
    });

    // Adjust datatables saat tab aktif
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
    });

    // 🔹 SUBMIT FORM TARIK SP2D
    $("#formTarikSp2d").on("submit", function(e){
        e.preventDefault();

        let jsonText = $('#json_data').val().trim();

        if(jsonText === ""){
            return Swal.fire("Perhatian!", "Data JSON belum diisi!", "warning");
        }

        try { JSON.parse(jsonText) }
        catch(e){
            return Swal.fire("Format Salah!", "JSON tidak valid!", "error");
        }

        Swal.fire({
            title: 'Yakin Simpan?',
            text: "Data akan disimpan ke database",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if(result.isConfirmed){
                simpanData(jsonText);
            }
        });
    });

    // 🔹 FUNGSI SIMPAN AJAX
    function simpanData(jsonText){
        $('#btnProses').prop('disabled', true).text('Proses...');

        $.ajax({
            url: "/sp2d/tarik",
            type: "POST",
            data: { json_data: jsonText },
            success: function(res){
                Swal.fire("Success!", "Data berhasil disimpan!", "success");
                $('#btnProses').prop('disabled', false).text('Simpan');
                $('#json_data').val("");
                tableLS.ajax.reload();
            },
            error: function(xhr){
                $('#btnProses').prop('disabled', false).text('Simpan');
                Swal.fire("Error!", xhr.responseJSON?.message || "Terjadi kesalahan!", "error");
            }
        });
    }

    // HAPUS DATA
    $('body').on('click', '.btn-hapus', function(){
        var id = $(this).data('id');
        $.post('/sp2d/hapus', {id:id}, function(res){
            Swal.fire('Success','Data berhasil dihapus!','success');
            tableLS.ajax.reload();
        });
    });

    // RESTORE DATA
    $('body').on('click', '.btn-restore', function(){
        var id = $(this).data('id');
        $.post('/sp2d/restore', {id:id}, function(res){
            Swal.fire('Success','Data berhasil direstore!','success');
            tableHapus.ajax.reload();
        });
    });

    //Detail
    $('body').on('click', '.btn-detail', function(){
        let id = $(this).data('id');

        $.get('/sp2d/detail/' + id, function(res){
            let html = `
                <h5>SP2D: ${res.nomor_sp2d}</h5>
                <p><b>Pihak Ketiga:</b> ${res.nama_pihak_ketiga}</p>
                <p><b>Nilai:</b> ${Intl.NumberFormat().format(res.nilai_sp2d)}</p>
                <hr>
                <h6>Belanja</h6>
                <table class="table table-bordered">
                    <tr><th>Rek</th><th>Uraian</th><th>Jumlah</th></tr>
            `;

            res.belanja_ls.forEach(b=>{
                html += `<tr>
                    <td>${b.kode_rekening}</td>
                    <td>${b.uraian}</td>
                    <td>${Intl.NumberFormat().format(b.jumlah)}</td>
                </tr>`;
            });

            html += `</table><hr><h6>Pajak</h6>
                <table class="table table-bordered">
                    <tr><th>Pajak</th><th>Billing</th><th>Nilai</th></tr>
            `;

            res.pajak_potongan_ls.forEach(p=>{
                html += `<tr>
                    <td>${p.nama_pajak_potongan}</td>
                    <td>${p.id_billing}</td>
                    <td>${Intl.NumberFormat().format(p.nilai_sp2d_pajak_potongan)}</td>
                </tr>`;
            });

            html += `</table>`;

            $('#detailContent').html(html);
            $('#modalDetail').modal('show');
        });
    });

});
</script>
