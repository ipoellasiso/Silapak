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
    var table = $('.tabelrealisasi').DataTable({
        processing: true,
        serverSide: true,
        ajax: "/tampildatarealisasibelanja",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex'},
            {data: 'nama_skpd', name: 'nama_skpd'},
            {data: 'nomor_spm', name: 'nomor_spm'},
            {data: 'tanggal_sp2d', name: 'tanggal_sp2d'},
            {data: 'nomor_sp2d', name: 'nomor_sp2d'},
            {data: 'keterangan_sp2d', name: 'keterangan_sp2d'},
            {data: 'nilai_sp2d', name: 'nilai_sp2d'},
            {data: 'kode_rekening', name: 'kode_rekening'},
            {data: 'uraian', name: 'uraian'},
            {data: 'nilai', name: 'nilai'},
            {data: 'jenis', name: 'jenis'},
            // {data: 'action', name: 'action', orderable: false, searchable: false},
        ]
    });

    $('#createimportbku').click(function (){
        $('#saveBtn').val("create-import");
        $('#id').val('');
        $('#userForm1').trigger("reset");
        $('#tambahimportbku').modal('show');
        $('#modal-preview').attr('src', 'https://via/placeholder.com/150');

    });

    $(document).ready(function() {
        $('.amount').on('keyup', function(e) {
            $(this).val(formatRupiah($(this).val(), ' '));
        });
    });

    $('#id_opd').select2({
	    placeholder: "Pilih Opd",
    	allowClear: true,
        dropdownParent: $('#tambahbku'),
	    ajax: { 
            url: "/bku/opd",
            type: "Get",
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    searchOpd: params.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results: response
                };
            },
                cache: true
            }
    });

});

function readURL(input, id) {
    id = id || '#modal-preview';
    if (input.files && input.files[0]){
        var reader = new FileReader();

        reader.onload = function (e) {
            $(id).attr('src', e.target.result);
        };

        reader.readAsDataURL(input.files[0]);
        $('#modal-preview').removeClass('hidden');
        $('#start').hide();
    }
}

function formatRupiah(angka, prefix) {
    var number_string = angka.replace(/[^,\d]/g, '').toString(),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    if (ribuan) {
        separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix === undefined ? rupiah : (rupiah ? ' ' + rupiah : '');
}

</script>