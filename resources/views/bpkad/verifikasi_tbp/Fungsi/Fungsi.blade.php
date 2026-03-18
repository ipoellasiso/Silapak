<script type="text/javascript">
$(function () {

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    let tbVerifikasi, tbTerima, tbTolak;

    /* ============================================================
     * TABEL VERIFIKASI — rinci per jenis pajak (PPN, PPh, dll)
     * ============================================================ */
    tbVerifikasi = $('#tbVerifikasi').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ url('/bpkad/verifikasi-tbp/data') }}",
        columns: [
            { data: 'cek',                       name: 'cek',                       orderable: false, searchable: false, title: '<input type="checkbox" id="checkAll">' },
            { data: 'DT_RowIndex',               name: 'DT_RowIndex',               title: 'No',          orderable: false, searchable: false },
            { data: 'nomor_tbp',                 name: 'nomor_tbp',                 title: 'Nomor TBP' },
            { data: 'nama_skpd',                 name: 'nama_skpd',                 title: 'OPD' },
            { data: 'nama_pajak_potongan',        name: 'nama_pajak_potongan',        title: 'Jenis Pajak' },
            { data: 'nilai_tbp_pajak_potongan',   name: 'nilai_tbp_pajak_potongan',   title: 'Nilai Pajak' },
            { data: 'aksi',                      name: 'aksi',                      title: 'Aksi', orderable: false, searchable: false }
        ]
    });

    // Check All
    $(document).on('click', '#checkAll', function () {
        $('.cek-tbp').prop('checked', this.checked);
    });

    /* ============================================================
     * TABEL TERIMA
     * ============================================================ */
    tbTerima = $('#tbTerima').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ url('/bpkad/verifikasi-tbp/terima') }}",
        columns: [
            {
                data: 'DT_RowIndex',
                name: 'DT_RowIndex',
                title: 'No',
                orderable: false,
                searchable: false
            },
            { data: 'nomor_tbp',                 name: 'nomor_tbp',                 title: 'Nomor TBP' },
            { data: 'nama_skpd',                 name: 'nama_skpd',                 title: 'OPD' },
            { data: 'nama_pajak_potongan',        name: 'nama_pajak_potongan',        title: 'Jenis Pajak' },  // ✅ rinci
            { data: 'nilai_tbp_pajak_potongan',   name: 'nilai_tbp_pajak_potongan',   title: 'Nilai Pajak' },  // ✅ per pajak
            {
                data: 'aksi',
                name: 'aksi',
                title: 'Aksi',
                orderable: false,
                searchable: false
            }
        ]
    });

    /* ============================================================
     * TABEL TOLAK
     * ============================================================ */
    tbTolak = $('#tbTolak').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ url('/bpkad/verifikasi-tbp/tolak') }}",
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', title: 'No',          orderable: false, searchable: false },
            { data: 'nomor_tbp',   name: 'nomor_tbp',   title: 'Nomor TBP' },
            { data: 'nama_skpd',   name: 'nama_skpd',   title: 'OPD' },
            { data: 'total_pajak', name: 'total_pajak', title: 'Total Pajak' }
        ]
    });

    /* ============================================================
     * TOMBOL TERIMA — di tab Verifikasi
     * ============================================================ */
    $(document).on('click', '.btn-terima', function () {
        let id      = $(this).data('id');
        let idPajak = $(this).data('idpajak');

        Swal.fire({
            title: 'Verifikasi Pajak ini?',
            text: 'Pajak ini akan ditetapkan FINAL',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Terima',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('verifikasi-tbp.terima') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id_tbp: id
                    },
                    success: function (res) {
                        Swal.fire('Berhasil', res.message, 'success');
                        tbVerifikasi.ajax.reload();
                        tbTerima.ajax.reload();
                    },
                    error: function () {
                        Swal.fire('Error', 'Gagal verifikasi TBP', 'error');
                    }
                });
            }
        });
    });

    /* ============================================================
     * TOMBOL TOLAK — di tab Verifikasi
     * ============================================================ */
    $(document).on('click', '.btn-tolak-ver', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Tolak TBP?',
            text: 'TBP ini akan dikembalikan ke DRAFT!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tolak',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("{{ url('/bpkad/verifikasi-tbp/tolak') }}", {
                    _token: "{{ csrf_token() }}",
                    id_tbp: id
                }, function (res) {
                    Swal.fire('Berhasil', res.message, 'success');
                    tbVerifikasi.ajax.reload();
                    tbTolak.ajax.reload();
                });
            }
        });
    });

    /* ============================================================
     * TOMBOL TOLAK — di tab Terima
     * ============================================================ */
    $(document).on('click', '.btn-tolak', function () {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Yakin?',
            text: 'TBP ini akan DITOLAK dari Terima!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Tolak',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post("{{ url('/bpkad/verifikasi-tbp/tolak-dari-terima') }}", {
                    _token: "{{ csrf_token() }}",
                    id_tbp: id
                }, function (res) {
                    Swal.fire('Berhasil', res.message, 'success');
                    tbVerifikasi.ajax.reload();
                    tbTerima.ajax.reload();
                    tbTolak.ajax.reload();
                });
            }
        });
    });

    /* ============================================================
     * VERIFIKASI TERPILIH (checkbox)
     * ============================================================ */
    $('#btnVerifikasiPilih').click(function () {
        // Ambil id_tbp unik dari checkbox yang dicentang
        let ids = [...new Set(
            $('.cek-tbp:checked').map(function () {
                return $(this).val();
            }).get()
        )];

        if (ids.length === 0) {
            Swal.fire('Info', 'Pilih data terlebih dahulu', 'info');
            return;
        }

        Swal.fire({
            title: 'Verifikasi TBP terpilih?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya'
        }).then((res) => {
            if (res.isConfirmed) {
                $.post("{{ url('/bpkad/verifikasi-tbp/terima-multi') }}", {
                    _token: '{{ csrf_token() }}',
                    ids: ids
                }, function (r) {
                    Swal.fire('Berhasil', r.message, 'success');
                    tbVerifikasi.ajax.reload();
                    tbTerima.ajax.reload();
                });
            }
        });
    });

    /* ============================================================
     * VERIFIKASI HALAMAN INI
     * ============================================================ */
    $('#btnVerifikasiHalaman').click(function () {
        // Ambil id_tbp unik dari semua baris halaman ini
        let ids = [...new Set(
            tbVerifikasi.rows({ page: 'current' }).data()
                .map(row => row.id_tbp)
                .toArray()
        )];

        if (ids.length === 0) {
            Swal.fire('Info', 'Tidak ada data', 'info');
            return;
        }

        Swal.fire({
            title: 'Verifikasi semua di halaman ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya'
        }).then((res) => {
            if (res.isConfirmed) {
                $.post("{{ url('/bpkad/verifikasi-tbp/terima-multi') }}", {
                    _token: '{{ csrf_token() }}',
                    ids: ids
                }, function (r) {
                    Swal.fire('Berhasil', r.message, 'success');
                    tbVerifikasi.ajax.reload();
                    tbTerima.ajax.reload();
                });
            }
        });
    });

});
</script>