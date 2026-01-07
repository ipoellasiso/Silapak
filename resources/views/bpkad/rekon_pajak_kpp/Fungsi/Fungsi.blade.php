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

    let table = $('#table-rekon').DataTable({
        processing:true,
        serverSide:true,
        deferLoading: 0, // ⛔ jangan auto load
        ajax: {
            url: "{{ route('kpp.rekon.data') }}",
            data: function (d) {
                d.opd   = $('#filter-opd').val();
                d.bulan = $('#filter-bulan').val();
                d.tahun = $('#filter-tahun').val();
            },
            error: function () {
                Swal.fire(
                    'Gangguan',
                    'Data SP2D sedang tidak tersedia',
                    'warning'
                );
            }
        },
        columns:[
            {data:'DT_RowIndex',title:'No', orderable:false, searchable:false,},
            {data:'nama_skpd',title:'OPD'},
            {data:'sp2d',title:'SPM / TBP'},
            {data:'sp2d_info',title:'SP2D'},
            {data:'nama_pajak_potongan',title:'Jenis Pajak'},
            {data:'akun_pajak',title:'Akun'},
            {
                data: 'nilai_tbp_pajak_potongan',
                className: 'text-end',
                render: $.fn.dataTable.render.number('.', ',', 0)
            },
            {data:'pajak',title:'Ebilling/NTPN'},
            {data:'status',title:'Status'}
        ]
    });

    $('#btn-filter').click(()=>table.ajax.reload());

    $('#btn-posting').click(()=>{
        Swal.fire({title:'Posting FINAL?',icon:'warning',showCancelButton:true})
        .then(r=>{
            if(r.isConfirmed){
                $.post("{{ route('kpp.rekon.posting') }}",{
                    bulan:$('#filter-bulan').val(),
                    opd:$('#filter-opd').val(),
                    tahun:$('#filter-tahun').val()
                },res=>{
                    Swal.fire('OK',res.message,'success');
                    table.ajax.reload();
                });
            }
        });
    });

    $(document).on('click', '.btn-unposting', function () {

        let id = $(this).data('id');

        Swal.fire({
            title: 'UnPosting data ini?',
            text: 'Status FINAL akan dibatalkan',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, UnPosting'
        }).then((result) => {
            if (result.isConfirmed) {

                $.post("{{ route('kpp.rekon.unposting') }}", {
                    id: id,
                    _token: "{{ csrf_token() }}"
                }, function (res) {
                    Swal.fire('Berhasil', res.message, 'success');
                    table.ajax.reload();
                }).fail(function (xhr) {
                    Swal.fire('Gagal', xhr.responseJSON.message, 'error');
                });

            }
        });
    });

    $('#btn-unposting-massal').click(function () {
        Swal.fire({
            title: 'UnPosting Massal?',
            text: 'Semua data FINAL sesuai filter akan dibatalkan',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, UnPosting'
        }).then((result) => {
            if (result.isConfirmed) {

                $.post("{{ route('kpp.rekon.unposting.massal') }}", {
                    tahun: $('#filter-tahun').val(),
                    bulan: $('#filter-bulan').val(),
                    opd: $('#filter-opd').val(),
                    _token: "{{ csrf_token() }}"
                }, function (res) {
                    Swal.fire('Berhasil', res.message, 'success');
                    table.ajax.reload();
                }).fail(function (xhr) {
                    Swal.fire(
                        'Gagal',
                        xhr.responseJSON?.message ?? 'UnPosting gagal',
                        'error'
                    );
                });

            }
        });
    });

    $('#btn-export').click(()=>{
        window.location =
            "{{ route('kpp.rekon.export') }}"
            + "?tahun=" + $('#filter-tahun').val()
            + "&bulan=" + $('#filter-bulan').val()
            + "&opd="   + $('#filter-opd').val();
    });

});

</script>
