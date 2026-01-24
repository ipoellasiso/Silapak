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
        processing: true,
        serverSide: true,
        searching: true,
        ajax: {
            url: "{{ route('laporan.kpp.sudah') }}",
            data: function (d) {
                d.opd   = $('#filter-opd').val();
                d.bulan = $('#filter-bulan').val();
                d.tahun = $('#filter-tahun').val();
            }
        },
        columns: [
            { data:'DT_RowIndex', title:'No', orderable:false, searchable:false },

            // ✅ KOLOM ASLI SQL
            { data:'no_spm', title:'No SPM', searchable:true },

            // ❌ JANGAN DI-SEARCH (JOIN)
            { data:'tanggal_sp2d', title:'Tgl SP2D', searchable:false },
            { data:'nomor_sp2d', title:'No SP2D', searchable:true }, // ini aman

            { data:'nilai_sp2d', title:'Nilai SP2D', searchable:false },

            // ❌ HTML
            { data:'pajak', title:'Pajak', searchable:false },

            // ❌ FORMAT ANGKA
            { data:'nilai_pajak', title:'Nilai Pajak', searchable:false },

            { data:'aksi', title:'Aksi', orderable:false, searchable:false }
        ]
    });

        let tableBelumLoaded = false;
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            let target = $(e.target).attr('href');

            if (target === '#belum' && !tableBelumLoaded) {
                tableBelum.ajax.reload();
                tableBelumLoaded = true;
            }
        });

        let tableBelum = $('#table-belum').DataTable({
            processing:true,
            serverSide:false,
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
                {data:'status_sp2d', title:'Status', className:'text-center align-middle'},// ✅
                {data:'aksi', title:'Aksi', orderable:false, searchable:false, className:'text-center'}
            ]
        });

        //TOMBOL FILTER
        $('#btn-filter').on('click', function (e) {
            e.preventDefault(); // ⛔ cegah submit / reload halaman
            tableSudah.ajax.reload();
            tableBelum.ajax.reload();
            // tableBelumPosting.ajax.reload(); // 🔥
        });

        $(document).on('click','.btn-edit', function () {
            let id = $(this).data('id');

            $.get("{{ url('/bpkad/pajak/detail') }}/" + id, function (res) {
                $('#id_pajak').val(res.id);
                $('#nomor_tbp').val(res.nomor_tbp);
                $('#no_spm').val(res.no_spm);
                $('#jenis_pajak').val(res.nama_pajak_potongan);
                $('#nilai_pajak').val(
                    new Intl.NumberFormat('id-ID').format(res.nilai_tbp_pajak_potongan)
                );

                $('#akun_pajak').val(res.akun_pajak).trigger('change');
                $('#rek_belanja').val(res.rek_belanja);
                $('#nama_npwp').val(res.nama_npwp);
                $('#no_npwp').val(res.no_npwp);
                $('#ntpn').val(res.ntpn);
                $('#id_billing').val(res.id_billing);

                if(res.bukti_setoran){
                    $('#previewBukti').html(`
                        <a href="/storage/${res.bukti_setoran}"
                        target="_blank"
                        class="btn btn-sm btn-outline-info">
                            <i class="fas fa-eye"></i> Lihat Bukti Lama
                        </a>
                    `);
                }

                $('#modalInput').modal('show');
            });
        });

        $('#formInput').submit(function(e){
            e.preventDefault();

            let form = new FormData(this);

            $.ajax({
                url: "{{ route('bpkad.pajak.simpan') }}",
                type: "POST",
                data: form,
                processData: false,
                contentType: false,

                success: function(res){
                    Swal.fire('Berhasil', res.message, 'success');
                    $('#modalInput').modal('hide');
                    tableSudah.ajax.reload();
                    tableBelum.ajax.reload();
                },

                error: function(xhr){
                    Swal.fire(
                        'Gagal',
                        xhr.responseJSON?.message || 'Terjadi kesalahan',
                        'error'
                    );
                }
            });
        });

        $('#search-custom').on('keyup', function () {
            tableSudah.ajax.reload();
            tableBelum.ajax.reload();
        });

    });

    </script>
