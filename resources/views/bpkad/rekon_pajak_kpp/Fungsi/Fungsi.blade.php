    <script>
    $(function () {

        /* =====================================================
        * CSRF
        * ===================================================== */
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        /* =====================================================
        * KONFIG KOLOM DATATABLE
        * ===================================================== */
        const columnsGU = [
            { data:'DT_RowIndex', title:'No', orderable:false, searchable:false },
            { data:'nama_skpd', title:'OPD' },
            { data:'sp2d', title:'SPM / TBP' },
            { data:'sp2d_info', title:'SP2D' },
            { data:'nama_pajak_potongan', title:'Jenis Pajak' },
            { data:'akun_pajak', title:'Akun' },
            {
                data:'nilai_tbp_pajak_potongan',
                title:'Nilai',
                className:'text-end',
                render: $.fn.dataTable.render.number('.', ',', 0)
            },
            { data:'pajak', title:'Ebilling / NTPN' },
            { data:'status', title:'Status' }
        ];

        const columnsLS = [
            { data:'DT_RowIndex', title:'No', orderable:false, searchable:false },
            { data:'nama_skpd', title:'OPD' },
            { data:'sp2d', title:'SP2D' },
            { data:'nama_pajak_potongan', title:'Jenis Pajak' },
            { data:'akun_pajak', title:'Akun' },
            {
                data:'nilai_sp2d_pajak_potongan',
                title:'Nilai',
                className:'text-end',
                render: $.fn.dataTable.render.number('.', ',', 0)
            },
            { data:'pajak', title:'Ebilling / NTPN' },
            { data:'status', title:'Status' }
        ];

        let table;

        function initTable(jenis) {

            if (table) {
                table.destroy();
                $('#table-rekon').empty();
            }

            table = $('#table-rekon').DataTable({
                processing: true,
                serverSide: true,
                order: [], // ⛔ jangan auto order DT_RowIndex
                ajax: {
                    url: "{{ route('kpp.rekon.data') }}",
                    data: function (d) {
                        d.opd   = $('#filter-opd').val();
                        d.bulan = $('#filter-bulan').val();
                        d.tahun = $('#filter-tahun').val();
                        d.jenis = jenis;
                    }
                },
                columns: jenis === 'GU' ? columnsGU : columnsLS
            });
        }

        /* LOAD PERTAMA */
        initTable('GU');
        $('#jenis').val('GU');

        /* TAB SWITCH */
        $('#rekonTab a').on('click', function (e) {
            e.preventDefault();

            $('#rekonTab a').removeClass('active');
            $(this).addClass('active');

            let jenis = $(this).data('jenis');
            $('#jenis').val(jenis);

            initTable(jenis);
        });

        /* FILTER */
        $('#btn-filter').on('click', function () {
            table.ajax.reload();
        });

        /* =====================================================
        * POSTING
        * ===================================================== */
        $('#btn-posting').on('click', function () {

            Swal.fire({
                title: 'Posting FINAL?',
                text: 'Data sesuai filter akan diposting',
                icon: 'warning',
                showCancelButton: true
            }).then((r) => {

                if (!r.isConfirmed) return;

                $.post("{{ route('kpp.rekon.posting') }}", {
                    tahun: $('#filter-tahun').val(),
                    bulan: $('#filter-bulan').val(),
                    opd:   $('#filter-opd').val(),
                    jenis: $('#jenis').val()
                }, function (res) {

                    Swal.fire('Berhasil', res.message, 'success');
                    table.ajax.reload();

                }).fail(function (xhr) {

                    Swal.fire(
                        'Gagal',
                        xhr.responseJSON?.message ?? 'Posting gagal',
                        'error'
                    );

                });

            });
        });

        /* =====================================================
        * UNPOSTING SATUAN
        * ===================================================== */
        $(document).on('click', '.btn-unposting', function () {

            let id = $(this).data('id');

            Swal.fire({
                title: 'UnPosting data ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33'
            }).then((r) => {

                if (!r.isConfirmed) return;

                $.post("{{ route('kpp.rekon.unposting') }}", {
                    id: id
                }, function (res) {

                    Swal.fire('OK', res.message, 'success');
                    table.ajax.reload();

                }).fail(function (xhr) {

                    Swal.fire(
                        'Gagal',
                        xhr.responseJSON?.message ?? 'UnPosting gagal',
                        'error'
                    );

                });

            });
        });

        /* =====================================================
        * UNPOSTING MASSAL
        * ===================================================== */
        $('#btn-unposting-massal').on('click', function () {

            Swal.fire({
                title: 'UnPosting Massal?',
                text: 'Semua data FINAL sesuai filter akan dibatalkan',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33'
            }).then((r) => {

                if (!r.isConfirmed) return;

                $.post("{{ route('kpp.rekon.unposting.massal') }}", {
                    tahun: $('#filter-tahun').val(),
                    bulan: $('#filter-bulan').val(),
                    opd:   $('#filter-opd').val(),
                    jenis: $('#jenis').val()
                }, function (res) {

                    Swal.fire('Berhasil', res.message, 'success');
                    table.ajax.reload();

                }).fail(function (xhr) {

                    Swal.fire(
                        'Gagal',
                        xhr.responseJSON?.message ?? 'UnPosting massal gagal',
                        'error'
                    );

                });

            });
        });

        /* =====================================================
        * EXPORT
        * ===================================================== */
        $('#btn-export').on('click', function () {

            let url = "{{ route('kpp.rekon.export') }}"
                + "?tahun=" + $('#filter-tahun').val()
                + "&bulan=" + $('#filter-bulan').val()
                + "&opd="   + $('#filter-opd').val()
                + "&jenis=" + $('#jenis').val();

            window.location = url;
        });

    });
    </script>
