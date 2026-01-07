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

    let tbBelum, tbSudah;

    $(function(){

    tbBelum = $('#tbBelum').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ url('/opd/input-pajak/belum') }}",
        columns:[
            {data:'DT_RowIndex', name:'DT_RowIndex', orderable:false, searchable:false},
            {data:'nomor_tbp', name:'nomor_tbp'},
            {data:'nama_pajak_potongan', name:'nama_pajak_potongan'},
            {data:'nilai_tbp_pajak_potongan', name:'nilai_tbp_pajak_potongan'},
            {data:'aksi', orderable:false, searchable:false}
        ]
    });

    tbSudah = $('#tbSudah').DataTable({
        ajax: "{{ url('/opd/input-pajak/sudah') }}",
        columns:[
            {data:'DT_RowIndex', title:'No'},
            {data:'nomor_tbp', title:'TBP'},
            {data:'nama_pajak_potongan', title:'Pajak'},
            {data:'nilai_tbp_pajak_potongan', title:'Nilai'},
            {data:'ntpn', title:'NTPN'},
            {data:'status4', title:'Status'}, // 🔥 INI
            {data:'aksi', title:'Aksi', orderable:false, searchable:false}
        ]
    });

    });

    $(document).on('click','.btn-input',function(){
        $('#id_pajak').val($(this).data('id'));
        $('#modalInput').modal('show');
    });

    //Form input
    $('#formInput').submit(function(e){
        e.preventDefault();

        let form = new FormData(this);

        // 🔒 DISABLE BUTTON
        let btn = $('#btnSimpan');
        btn.prop('disabled', true)
        .html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');

        $.ajax({
            url: "{{ url('/opd/input-pajak/simpan') }}",
            type: "POST",
            data: form,
            processData: false,
            contentType: false,

            success: function(res){
                Swal.fire('Berhasil', res.message, 'success');

                $('#formInput')[0].reset();
                $('#id_pajak').val('');
                $('#akun_pajak').val(null).trigger('change');

                tbBelum.ajax.reload(null, false);
                tbSudah.ajax.reload(null, false);

                $('#modalInput').modal('hide');
            },

            // 🔥🔥🔥 DI SINI LETAKNYA 🔥🔥🔥
            error: function(xhr){
                let msg = 'Terjadi kesalahan';

                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors)[0][0];
                    }
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Validasi Gagal',
                    text: msg
                });
            },

            complete: function(){
                // 🔓 ENABLE BUTTON
                btn.prop('disabled', false)
                .html('<i class="fas fa-save"></i> Simpan');
            }
        });
    });

    //tarik data tbp ke form input pajak
    $(document).on('click','.btn-input',function () {

        let id = $(this).data('id');
        $('#id_pajak').val(id);

        $.get("{{ url('/opd/input-pajak/detail') }}/" + id, function (res) {

            $('#nomor_tbp').val(res.nomor_tbp);
            $('#no_spm').val(res.no_spm);
            $('#jenis_pajak').val(res.nama_pajak_potongan);
            $('#nilai_pajak').val(
                new Intl.NumberFormat('id-ID').format(res.nilai_tbp_pajak_potongan)
            );

            // 🔥 INI YANG SEBELUMNYA TIDAK ADA
            $('#akun_pajak').val(res.akun_pajak).trigger('change');
            $('#rek_belanja').val(res.rek_belanja);
            $('#nama_npwp').val(res.nama_npwp);
            $('#no_npwp').val(res.no_npwp);
            $('#ntpn').val(res.ntpn);

             // 🔁 PREVIEW FILE
            if(res.bukti_setoran){
                $('#previewBukti').html(`
                    <a href="/storage/${res.bukti_setoran}"
                    target="_blank"
                    class="btn btn-sm btn-outline-info">
                        <i class="fas fa-eye"></i> Lihat Bukti Setoran
                    </a>
                `);
            } else {
                // INPUT BARU → WAJIB UPLOAD
                $('#bukti_setoran').prop('required', true);
                $('#previewBukti').html('<small class="text-muted">Belum ada file</small>');
            }

            $('#modalInput').modal('show');
        });
    });

    // Batal Pajak
    $(document).on('click','.btn-batal',function(){
        let id = $(this).data('id');

        Swal.fire({
            title:'Batalkan Input Pajak?',
            icon:'warning',
            showCancelButton:true,
            confirmButtonText:'Ya'
        }).then((res)=>{
            if(res.isConfirmed){
                $.post("{{ url('/opd/input-pajak/batal') }}",
                {id:id},
                function(r){
                    Swal.fire('Berhasil',r.message,'success');
                    tbBelum.ajax.reload();
                    tbSudah.ajax.reload();
                });
            }
        });
    });

    //VALIDASI REALTIME (ON BLUR)
    $('#ntpn, #ebilling').on('blur', function(){
        let ntpn     = $('#ntpn').val();
        let ebilling = $('#ebilling').val();
        let id       = $('#id_pajak').val();

        if(ntpn === '' && ebilling === '') return;

        $.post("{{ url('/opd/input-pajak/cek-ntpn-ebilling') }}", {
            ntpn: ntpn,
            ebilling: ebilling,
            id: id,
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function(res){

            if(res.ntpn_exists){
                Swal.fire('Duplikat!', 'NTPN sudah pernah digunakan', 'warning');
                $('#ntpn').val('').focus();
                return;
            }

            if(res.ebilling_exists){
                Swal.fire('Duplikat!', 'E-Billing sudah pernah digunakan', 'warning');
                $('#ebilling').val('').focus();
                return;
            }

        });
    });

    $('#id_billing').on('blur', function () {
        let id_billing = $(this).val();
        let id = $('#id_pajak').val();

        if (id_billing === '') return;

        $.post("{{ url('/opd/input-pajak/cek-billing') }}", {
            id_billing: id_billing,
            id: id,
            _token: $('meta[name="csrf-token"]').attr('content')
        }, function (res) {

            if (res.exists) {
                Swal.fire('Duplikat!', 'E-Billing sudah pernah digunakan', 'warning');
                $('#id_billing').val('').focus();
            }

        });
    });

});

</script>
