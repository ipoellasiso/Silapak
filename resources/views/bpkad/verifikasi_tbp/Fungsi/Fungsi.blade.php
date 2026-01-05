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

    let tbVerifikasi, tbTerima, tbTolak;
    $(function () {

        tbVerifikasi = $('#tbVerifikasi').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ url('/bpkad/verifikasi-tbp/data') }}",
            columns: [
                {
                    data: 'cek',
                    orderable: false,
                    searchable: false,
                    title: `<input type="checkbox" id="checkAll">`
                },
                { data: 'nomor_tbp', title: 'Nomor TBP' },
                { data: 'nama_skpd', title: 'OPD' },
                { data: 'total_pajak', title: 'Total Pajak' },
                { data: 'aksi', title: 'Aksi', orderable:false }
            ]
        });

        $(document).on('click','#checkAll',function(){
            $('.cek-tbp').prop('checked', this.checked);
        });

        tbTerima = $('#tbTerima').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ url('/bpkad/verifikasi-tbp/terima') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', title: 'No', orderable: false, searchable: false },
                { data: 'nomor_tbp', name: 'nomor_tbp', title: 'Nomor TBP' },
                { data: 'nama_skpd', name: 'nama_skpd', title: 'OPD' },
                { data: 'total_pajak', name: 'total_pajak', title: 'Total Pajak' },
                {
                    data: 'aksi',
                    name: 'aksi',
                    title: 'Aksi',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        tbTolak = $('#tbTolak').DataTable({
            ajax: "{{ url('/bpkad/verifikasi-tbp/tolak') }}",
            columns: [
                { title:'No', data:'DT_RowIndex' },
                { title:'Nomor TBP', data:'nomor_tbp' },
                { title:'OPD', data:'nama_skpd' },
                { title:'Total Pajak', data:'total_pajak' }
            ]
        });

    });

    //Simpan tolak tbp di tabs terima
    $(document).on('click', '.btn-tolak', function () {

        let id = $(this).data('id');

        Swal.fire({
            title: 'Yakin?',
            text: 'TBP ini akan DITOLAK!',
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

    // tbp Terima di tas Verifikasi
    $(document).on('click', '.btn-terima', function () {

        let id = $(this).data('id');

        Swal.fire({
            title: 'Verifikasi TBP?',
            text: 'TBP akan ditetapkan menjadi FINAL',
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

                        $('#tbVerifikasi').DataTable().ajax.reload();
                        $('#tbTerima').DataTable().ajax.reload();
                    },
                    error: function () {
                        Swal.fire('Error', 'Gagal verifikasi TBP', 'error');
                    }
                });
            }
        });
    });

    // Verifikasi dipilih
    $('#btnVerifikasiPilih').click(function(){

        let ids = [];
        $('.cek-tbp:checked').each(function(){
            ids.push($(this).val());
        });

        if(ids.length === 0){
            Swal.fire('Info','Pilih data terlebih dahulu','info');
            return;
        }

        Swal.fire({
            title:'Verifikasi TBP terpilih?',
            icon:'warning',
            showCancelButton:true,
            confirmButtonText:'Ya'
        }).then((res)=>{
            if(res.isConfirmed){
                $.post("{{ url('/bpkad/verifikasi-tbp/terima-multi') }}",{
                    _token:'{{ csrf_token() }}',
                    ids:ids
                },function(r){
                    Swal.fire('Berhasil',r.message,'success');
                    tbVerifikasi.ajax.reload();
                    tbTerima.ajax.reload();
                });
            }
        });

    });

    //Verifikasi Perhalaman
    $('#btnVerifikasiHalaman').click(function(){

        let ids = tbVerifikasi
            .rows({ page:'current' })
            .data()
            .pluck('id_tbp')
            .toArray();

        if(ids.length === 0){
            Swal.fire('Info','Tidak ada data','info');
            return;
        }

        Swal.fire({
            title:'Verifikasi semua di halaman ini?',
            icon:'warning',
            showCancelButton:true,
            confirmButtonText:'Ya'
        }).then((res)=>{
            if(res.isConfirmed){
                $.post("{{ url('/bpkad/verifikasi-tbp/terima-multi') }}",{
                    _token:'{{ csrf_token() }}',
                    ids:ids
                },function(r){
                    Swal.fire('Berhasil',r.message,'success');
                    tbVerifikasi.ajax.reload();
                    tbTerima.ajax.reload();
                });
            }
        });

    });

});

</script>
