{{-- ================= JS ================= --}}
<script>
$(function () {

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /* ================= DATATABLE LS ================= */
    let tableBelum = $('#table-belum').DataTable({
        processing:true,
        serverSide:false,
        ajax:{
            url: "{{ route('pajak.ls.belum') }}",
            data:function(d){
                d.opd   = $('#filter-opd').val();
                d.bulan = $('#filter-bulan').val();
                d.tahun = $('#filter-tahun').val(); // 🔥 TAMBAH INI
            }
        },
        columns:[
            {data:'DT_RowIndex', title:'No'},
            {data:'nama_skpd', title:'OPD'},
            {data:'nomor_sp2d', title:'No SP2D'},
            {data:'tanggal_sp2d', title:'Tgl SP2D'},
            {data:'nilai_sp2d', title:'Nilai SP2D'},
            {data:'pajak', title:'Pajak'},
            {data:'nilai_pajak', title:'Nilai Pajak'},
            {data:'aksi', title:'Aksi', orderable:false, searchable:false}
        ]
    });

    let tableSudah = $('#table-sudah').DataTable({
        processing:true,
        serverSide:false,
        ajax:{
            url: "{{ route('pajak.ls.sudah') }}",
            data:function(d){
                d.opd   = $('#filter-opd').val();
                d.bulan = $('#filter-bulan').val();
                d.tahun = $('#filter-tahun').val(); // 🔥 TAMBAH INI
            }
        },
        columns:[
            {data:'DT_RowIndex', title:'No'},
            {data:'nama_skpd', title:'OPD'},
            {data:'nomor_sp2d', title:'No SP2D'},
            {data:'tanggal_sp2d', title:'Tgl SP2D'},
            {data:'nilai_sp2d', title:'Nilai SP2D'},
            {data:'pajak', title:'Pajak'},
            {data:'nilai_pajak', title:'Nilai Pajak'},
            // {data:'status', title:'Status', orderable:false, searchable:false},
            {data:'aksi', title:'Aksi', orderable:false, searchable:false}
        ]
    });

    /* ================= FILTER ================= */
    $('#btn-filter').on('click', function (e) {
            e.preventDefault();
            tableBelum.ajax.reload();
            tableSudah.ajax.reload();
        });
        /* ================= EDIT ================= */
        $(document).on('click','.btn-edit', function () {
        let id = $(this).data('id');

        $.get("{{ route('pajak.ls.detail', '') }}/" + id, function (res) {

            // hidden id
            $('#id_ls').val(res.id);

            // info
            $('#nama_pajak').val(res.nama_pajak_potongan);
            $('#nilai_pajak').val(
                new Intl.NumberFormat('id-ID').format(res.nilai_sp2d_pajak_potongan)
            );

            // 🔥 ISI FIELD EDIT
            $('#akun_pajak').val(res.akun_pajak).trigger('change');
            $('#rek_belanja').val(res.rek_belanja);
            $('#ntpn').val(res.ntpn);
            $('#id_billing').val(res.id_billing);

            // 🔥 LOAD LOG DI SINI
            loadLog(id);

            // 🔥 PASTIKAN TAB FORM AKTIF
            $('.nav-tabs a[href="#tab-form"]').tab('show');

            $('#modalEditLs').modal('show');
        });
    });

    /* ================= SIMPAN ================= */
    $('#formEditLs').submit(function (e) {
        e.preventDefault();

        $.ajax({
            url: "{{ route('pajak.ls.simpan') }}",
            type: "POST",
            data: $(this).serialize(),

            success: function (res) {
                Swal.fire('Berhasil', res.message, 'success');
                $('#modalEditLs').modal('hide');
                tableBelum.ajax.reload();
                tableSudah.ajax.reload();
            },

            error: function (xhr) {
                Swal.fire(
                    'Gagal',
                    xhr.responseJSON?.message || 'Terjadi kesalahan',
                    'error'
                );
            }
        });
    });

    function loadLog(id) {
        $('#log-koreksi').html('<tr><td colspan="4">Loading...</td></tr>');

        $.get("{{ url('/bpkad/pajak-ls/log') }}/" + id, function (res) {

            if (res.length === 0) {
                $('#log-koreksi').html(
                    '<tr><td colspan="4" class="text-center">Belum ada koreksi</td></tr>'
                );
                return;
            }

            let html = '';
            let no = 1;

            res.forEach(r => {
                html += `
                    <tr>
                        <td class="text-muted">${r.created_at}</td>
                        <td><strong>${r.nama_user}</strong></td>
                        <td>
                            <div class="log-json">
                                ${JSON.stringify(r.sebelum, null, 2)}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-danger mb-1">
                                ${r.keterangan}
                            </span>
                            <div class="log-json mt-1">
                                ${JSON.stringify(r.sesudah, null, 2)}
                            </div>
                        </td>
                    </tr>
                `;
                no++;
            });

            $('#log-koreksi').html(html);
        });
    }

});
</script>