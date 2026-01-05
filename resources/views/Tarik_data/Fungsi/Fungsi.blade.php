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

      /*------------------------------------------
      --------------------------------------------
      Render DataTable
      --------------------------------------------
      --------------------------------------------*/
    // var table = $('#tabelopd').DataTable({
    //     processing: true,
    //     serverSide: true,
    //     ajax: "/tampilopd",
    //     columns: [
    //         {data: 'DT_RowIndex', name: 'DT_RowIndex'},
    //         {data: 'nama_opd', name: 'nama_opd'},
    //         {data: 'nama_bendahara', name: 'nama_bendahara'},
    //         {data: 'alamat', name: 'alamat'},
    //         {data: 'action', name: 'action', orderable: false, searchable: false},
    //     ]
    // });

    tbpList = $('#datatable_tbp_list').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ url('pengajuan-tbp/list') }}",
        order: [[1, 'desc']], // order pakai kolom DB asli
        columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'nomor_tbp' },
                { data: 'no_spm' },
                { data: 'tanggal_tbp', className: 'text-center' },
                { data: 'nilai_tbp', className: 'text-end' },
                { data: 'total_pajak', className: 'text-end' },
                { data: 'status_badge', orderable: false, searchable: false, className: 'text-center' },
                { data: 'aksi', orderable: false, searchable: false, className: 'text-center' },
            ]
    });

    tbpBelum = $('#datatable_tbp_belum').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ url('pengajuan-tbp/belum-verifikasi') }}",
        order: [[1, 'desc']],
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nomor_tbp' },
            { data: 'nama_pajak_potongan' },
            { data: 'nilai_pajak' },
            { data: 'status1' },
            { data: 'aksi', orderable: false, searchable: false },
        ]
    });

    tbpTerima = $('#datatable_tbp_terima').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ url('pengajuan-tbp/terima') }}",
        order: [[1, 'desc']],
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nomor_tbp' },
            { data: 'nama_pajak_potongan' },
            { data: 'nilai_pajak' },
            { data: 'status1' },
        ]
    });

    tbpTolak = $('#datatable_tbp_tolak').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ url('pengajuan-tbp/tolak') }}",
        order: [[1, 'desc']],
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nomor_tbp' },
            { data: 'nama_pajak_potongan' },
            { data: 'nilai_pajak' },
            { data: 'status1' },
        ]
    });

    $(document).on('click', '.btn-hapus-tbp', function () {
        let id = $(this).data('id');
        let nomor = $(this).data('nomor');

        Swal.fire({
            title: 'Hapus TBP?',
            html: `<b>${nomor}</b><br>Data TBP dan seluruh pajaknya akan dihapus.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {

            if (result.isConfirmed) {

                $.ajax({
                    url: `/pengajuan-tbp/${id}/hapus`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (res) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        // reload datatable
                        tbpList.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan'
                        });
                    }
                });

            }

        });
    });

    $(document).on('click', '.btn-edit-tbp', function () {
        let id = $(this).data('id');

        $.get(`/pengajuan-tbp/${id}/edit`, function (res) {
            $('#edit_id_tbp').val(res.id_tbp);
            $('#edit_no_spm').val(res.no_spm);
            $('#edit_tanggal_tbp').val(res.tanggal_tbp);

            $('#modalEditTbp').modal('show');
        });
    });

    // SIMPAN EDIT
    $('#btnSimpanEdit').on('click', function () {

        let id = $('#edit_id_tbp').val();

        $.ajax({
            url: `/pengajuan-tbp/${id}`,
            type: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                no_spm: $('#edit_no_spm').val(),
                tanggal_tbp: $('#edit_tanggal_tbp').val(),
                keterangan_tbp: $('#edit_keterangan_tbp').val(),
            },
            success: function (res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                });

                $('#modalEditTbp').modal('hide');
                tbpList.ajax.reload(null, false);
                tbpBelum.ajax.reload(null, false);
                tbpTerima.ajax.reload(null, false);
                tbpTolak.ajax.reload(null, false);
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Data TBP gagal diperbarui'
                });
            }
        });
    });

});

</script>
