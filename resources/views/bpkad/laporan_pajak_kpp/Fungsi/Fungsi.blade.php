<script type="text/javascript">
    $(function () {

      /*------------------------------------------
       --------------------------------------------
       Pass Header Token
       --------------------------------------------
       --------------------------------------------*/
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // 🔹 SIMPAN INSTANCE KE VARIABLE
    let tableSudah = $('#table-sudah').DataTable({
        processing:true,
        serverSide:true,
        ajax: {
            url: "{{ route('laporan.kpp.sudah') }}",
            data: function (d) {
                d.opd   = $('#filter-opd').val();
                d.bulan = $('#filter-bulan').val();
                d.tahun = $('#filter-tahun').val();
            }
        },
        columns:[
            {data:'DT_RowIndex', title:'No', orderable:false, searchable:false, className:'text-center align-middle'},
            {data:'no_spm', title:'No SPM'}, // ✅
            {data:'tanggal_sp2d', title:'Tgl SP2D', className:'text-center align-middle'},
            {data:'nomor_sp2d', title:'No SP2D', className:'text-center align-middle'},
            {data:'nilai_sp2d', title:'Nilai SP2D', className:'text-end align-middle'},
            {data:'pajak', title:'Pajak'}, // ✅
            {data:'nilai_pajak', title:'Nilai Pajak', className:'text-end align-middle'}
        ]
    });

    let tableBelum = $('#table-belum').DataTable({
        processing:true,
        serverSide:true,
        ajax: {
            url: "{{ route('laporan.kpp.belum') }}",
            data: function (d) {
                d.opd   = $('#filter-opd').val();
                d.bulan = $('#filter-bulan').val();
                d.tahun = $('#filter-tahun').val();
            }
        },
        columns:[
            {data: 'DT_RowIndex', title: 'No', orderable: false, searchable: false},
            {data:'no_spm', title:'No SPM'},
            {data:'tbp', title:'TBP'},
            {data:'jenis_pajak', title:'Jenis Pajak'},
            {data:'nilai_pajak', title:'Nilai Pajak'},
            {data:'status_sp2d', title:'Status', className:'text-center align-middle'} // ✅
        ]
    });

    // TABEL BELUM POSTING
    let tableBelumPosting;
    tableBelumPosting = $('#table-belum-posting').DataTable({
        processing:true,
        serverSide:true,
        ajax: {
            url: "{{ route('laporan.kpp.belumPosting') }}",
            data: function (d) {
                d.opd   = $('#filter-opd').val();
                d.bulan = $('#filter-bulan').val();
                d.tahun = $('#filter-tahun').val();
            }
        },
        columns:[
            {data:'DT_RowIndex', title:'No', orderable:false, searchable:false},
            {data:'no_spm', title:'SPM / TBP'},
            {data:'jenis_pajak', title:'Jenis Pajak'},
            {data:'nilai_pajak', title:'Nilai Pajak'},
            {data:'status', title:'Status', orderable:false, searchable:false}
        ]
    });

    //TOMBOL FILTER
    $('#btn-filter').on('click', function (e) {
        e.preventDefault(); // ⛔ cegah submit / reload halaman
        tableSudah.ajax.reload();
        tableBelum.ajax.reload();
        tableBelumPosting.ajax.reload(); // 🔥
    });

    // $('#filter-opd, #filter-bulan, #filter-tahun').change(function () {
    //     tableSudah.ajax.reload();
    //     tableBelum.ajax.reload();
    // });

    //POSTING MASSAL PAJAK KPP
    $('#btnPosting').click(function () {
        Swal.fire({
            title: 'Posting ke KPP?',
            text: 'Data akan dikunci dan tidak bisa diubah',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Posting'
        }).then((res) => {

            if(res.isConfirmed){
                $.post("{{ route('kpp.posting.massal') }}",{
                    tahun: $('#filter-tahun').val(),
                    bulan: $('#filter-bulan').val(),
                    opd: $('#filter-opd').val()
                },function(r){
                    Swal.fire('Berhasil', r.message, 'success');
                    tableSudah.ajax.reload();
                    tableBelum.ajax.reload();
                    tableBelumPosting.ajax.reload(); // 🔥
                }).fail(function(e){
                    Swal.fire('Gagal', e.responseJSON.message, 'error');
                });
            }
        });
    });

    $('#btn-export').click(function () {
        let tahun = $('#filter-tahun').val();
        let bulan = $('#filter-bulan').val();
        let opd   = $('#filter-opd').val();

        if (!tahun) {
            Swal.fire('Gagal','Tahun wajib dipilih','warning');
            return;
        }

        let url = "{{ route('laporan.kpp.export') }}"
            + "?tahun=" + tahun
            + "&bulan=" + bulan
            + "&opd=" + opd;

        window.location.href = url;
    });

});

</script>
