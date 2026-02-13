<script>
$(function () {

    /* =========================
    * CSRF
    * ========================= */
    $.ajaxSetup({
        headers:{
            'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')
        }
    });

    /* =========================
    * KOLOM DATATABLE
    * ========================= */
    const columnsGU = [
        {
            data:'pilih',
            title:'<input type="checkbox" id="check-all">',
            orderable:false,
            searchable:false
        },
        { data:'DT_RowIndex', title:'No', orderable:false, searchable:false },
        { data:'nama_skpd', title:'OPD' },
        { data:'sp2d', title:'SPM / TBP' },
        { data:'sp2d_info', title:'SP2D' },
        { data:'nama_pajak_potongan', title:'Jenis Pajak' },
        { data:'akun_pajak', title:'Akun' },
        { data:'nilai_tbp_pajak_potongan', title:'Nilai', className:'text-end' },
        { data:'pajak', title:'Ebilling / NTPN' },
        { data:'status', title:'Status' }
    ];

    const columnsLS = [
        {
            data:'pilih',
            title:'<input type="checkbox" id="check-all">',
            orderable:false,
            searchable:false,
            className:'text-center'
        },
        { data:'DT_RowIndex', title:'No', orderable:false, searchable:false },
        { data:'nama_skpd', title:'OPD' },
        { data:'sp2d', title:'SP2D' },
        { data:'nama_pajak_potongan', title:'Jenis Pajak' },
        { data:'akun_pajak', title:'Akun' },
        { data:'nilai_sp2d_pajak_potongan', title:'Nilai', className:'text-end' },
        { data:'pajak', title:'Ebilling / NTPN' },
        { data:'status', title:'Status' }
    ];

    let table;
    let postingMode = false;

    /* =========================
    * INIT TABLE
    * ========================= */
    function initTable(jenis){

        if(table){
            table.destroy();
            $('#table-rekon').empty();
        }

        table = $('#table-rekon').DataTable({
            processing:true,
            serverSide:true,
            order:[],
            ajax:{
                url:"{{ route('kpp.rekon.data') }}",
                data:function(d){
                    d.opd   = $('#filter-opd').val();
                    d.bulan = $('#filter-bulan').val();
                    d.tahun = $('#filter-tahun').val();
                    d.jenis = jenis;
                }
            },
            columns: jenis === 'GU' ? columnsGU : columnsLS,
            drawCallback:function(){
                updateActionState();
            }
        });
    }

    /* =========================
    * STATE TOMBOL (GU / LS)
    * ========================= */
    function updateActionState(){
        function updateActionState(){
            // JAGA UI SAJA, JANGAN SENTUH DISABLED
            if (!postingMode) {
                $('#posting-area').addClass('d-none');
                $('#btn-toggle-posting')
                    .removeClass('btn-secondary')
                    .addClass('btn-outline-secondary')
                    .text('Mode Posting');
            }
        }
    }

    /* =========================
    * LOAD AWAL
    * ========================= */
    initTable('GU');
    $('#jenis').val('GU');

    // GU BOLEH POSTING
    $('#btn-toggle-posting').prop('disabled', false);

    // GU TIDAK BOLEH PELAPORAN KPPN
    $('#btn-pelaporan-pajak').prop('disabled', true);

    /* =========================
    * TAB SWITCH
    * ========================= */
    $('#rekonTab a').on('click', function (e) {
        e.preventDefault();

        $('#rekonTab a').removeClass('active');
        $(this).addClass('active');

        let jenis = $(this).data('jenis');
        $('#jenis').val(jenis);

        // 🔥 MODE POSTING AKTIF UNTUK GU & LS
        $('#btn-toggle-posting').prop('disabled', false);

        // Pelaporan pajak KPPN hanya LS
        if (jenis === 'LS') {
            $('#btn-pelaporan-pajak').prop('disabled', false);
        } else {
            $('#btn-pelaporan-pajak').prop('disabled', true);

            postingMode = false;
            $('#posting-area').addClass('d-none');
            $('#btn-toggle-posting')
                .removeClass('btn-secondary')
                .addClass('btn-outline-secondary')
                .text('Mode Posting');
        }

        initTable(jenis);
    });

    /* =========================
    * FILTER
    * ========================= */
    $('#btn-filter').on('click',function(){
        table.ajax.reload();
    });

    /* =========================
    * CHECK ALL
    * ========================= */
    $(document).on('change','#check-all',function(){
        $('.chk-posting, .chk-unposting').prop('checked', this.checked);
    });

    /* =========================
    * MODE POSTING
    * ========================= */
    $('#btn-toggle-posting').on('click',function(){

        postingMode = !postingMode;

        if(postingMode){
            $('#posting-area').removeClass('d-none');
            $(this)
                .removeClass('btn-outline-secondary')
                .addClass('btn-secondary')
                .text('Tutup Mode Posting');
        }else{
            $('#posting-area').addClass('d-none');
            $(this)
                .removeClass('btn-secondary')
                .addClass('btn-outline-secondary')
                .text('Mode Posting');
        }
    });

    /* =========================
    * POSTING SELECT
    * ========================= */
    $('#btn-posting-select').on('click',function(){

        let ids = $('.chk-posting:checked').map(function(){
            return $(this).val();
        }).get();

        if(ids.length === 0){
            Swal.fire('Info','Pilih data BELUM FINAL','info');
            return;
        }

        Swal.fire({
            title:'Posting FINAL?',
            icon:'warning',
            showCancelButton:true
        }).then(r=>{
            if(!r.isConfirmed) return;

            $.post("{{ route('kpp.rekon.posting.select') }}",{ ids },res=>{
                Swal.fire('Berhasil',res.message,'success');
                table.ajax.reload();
            }).fail(xhr=>{
                Swal.fire('Gagal',xhr.responseJSON?.message,'error');
            });
        });
    });

    /* =========================
    * POSTING MASSAL
    * ========================= */
    $('#btn-posting-massal').on('click',function(){

        Swal.fire({
            title:'Posting Massal?',
            icon:'warning',
            showCancelButton:true
        }).then(r=>{
            if(!r.isConfirmed) return;

            $.post("{{ route('kpp.rekon.posting.massal') }}",{
                tahun:$('#filter-tahun').val(),
                bulan:$('#filter-bulan').val(),
                opd:$('#filter-opd').val()
            },res=>{
                Swal.fire('Berhasil',res.message,'success');
                table.ajax.reload();
            }).fail(xhr=>{
                Swal.fire('Gagal',xhr.responseJSON?.message,'error');
            });
        });
    });

    /* =========================
    * UNPOSTING SELECT
    * ========================= */
    $('#btn-unposting-select').on('click',function(){

        let ids = $('.chk-unposting:checked').map(function(){
            return $(this).val();
        }).get();

        if(ids.length === 0){
            Swal.fire('Info','Pilih data FINAL','info');
            return;
        }

        Swal.fire({
            title:'UnPosting?',
            icon:'warning',
            showCancelButton:true
        }).then(r=>{
            if(!r.isConfirmed) return;

            $.post("{{ route('kpp.rekon.unposting.select') }}",{ ids },res=>{
                Swal.fire('Berhasil',res.message,'success');
                table.ajax.reload();
            }).fail(xhr=>{
                Swal.fire('Gagal',xhr.responseJSON?.message,'error');
            });
        });
    });

    /* =========================
    * UNPOSTING MASSAL
    * ========================= */
    $('#btn-unposting-massal').on('click',function(){

        Swal.fire({
            title:'UnPosting Massal?',
            icon:'warning',
            showCancelButton:true
        }).then(r=>{
            if(!r.isConfirmed) return;

            $.post("{{ route('kpp.rekon.unposting.massal') }}",{
                tahun:$('#filter-tahun').val(),
                bulan:$('#filter-bulan').val(),
                opd:$('#filter-opd').val()
            },res=>{
                Swal.fire('Berhasil',res.message,'success');
                table.ajax.reload();
            }).fail(xhr=>{
                Swal.fire('Gagal',xhr.responseJSON?.message,'error');
            });
        });
    });

    /* =========================
    * PELAPORAN PAJAK (FILTER)
    * ========================= */
    $('#btn-pelaporan-pajak').on('click', function () {

        let ids = $('.chk-posting:checked').map(function () {
            return $(this).val();
        }).get();

        if (ids.length === 0) {
            Swal.fire('Info','Pilih data pajak','info');
            return;
        }

        let now = new Date();

        $.post("{{ route('kpp.rekon.pelaporan') }}", {
            mode: 'selected',
            ids: ids,
            bulan_lapor: now.getMonth() + 1,
            tahun_lapor: now.getFullYear()
        }, res => {
            Swal.fire('Berhasil', res.message, 'success');
            table.ajax.reload();
        }).fail(xhr=>{
            Swal.fire('Gagal',xhr.responseJSON?.message,'error');
        });
    });

    // FILTER PELAPORAN
    $('#btn-pelaporan-filter').on('click', function () {

        let now = new Date();

        Swal.fire({
            title: 'Laporkan pajak sesuai FILTER?',
            html: `
                <b>OPD:</b> ${$('#filter-opd').val() || 'Semua'}<br>
                <b>Bulan Pajak:</b> ${$('#filter-bulan').val()}<br>
                <b>Tahun Pajak:</b> ${$('#filter-tahun').val()}
            `,
            icon:'warning',
            showCancelButton:true
        }).then(r=>{
            if(!r.isConfirmed) return;

            $.post("{{ route('kpp.rekon.pelaporan') }}",{
                mode: 'filter',
                opd: $('#filter-opd').val(),
                bulan: $('#filter-bulan').val(),
                tahun: $('#filter-tahun').val(),
                bulan_lapor: now.getMonth() + 1,
                tahun_lapor: now.getFullYear()
            },res=>{
                Swal.fire('Berhasil',res.message,'success');
                table.ajax.reload();
            }).fail(xhr=>{
                Swal.fire('Gagal',xhr.responseJSON?.message,'error');
            });
        });
    });

    //SELECT GU
    $('#btn-pelaporan-gu').on('click', function () {

        let ids = $('.chk-posting:checked').map(function () {
            return $(this).val();
        }).get();

        if (ids.length === 0) {
            Swal.fire('Info','Pilih data GU','info');
            return;
        }

        let now = new Date();

        $.post("{{ route('kpp.rekon.pelaporan.gu') }}", {
            mode: 'selected',
            ids: ids,
            bulan_lapor: now.getMonth() + 1,
            tahun_lapor: now.getFullYear()
        }, res => {
            Swal.fire('Berhasil', res.message, 'success');
            table.ajax.reload();
        }).fail(xhr => {
            Swal.fire('Gagal', xhr.responseJSON?.message, 'error');
        });
    });

    // FILTER GU
    $('#btn-pelaporan-gu-filter').on('click', function () {

        let now = new Date();

        Swal.fire({
            title: 'Laporkan Pajak GU sesuai filter?',
            html: `
                <b>OPD:</b> ${$('#filter-opd').val() || 'Semua'}<br>
                <b>Bulan:</b> ${$('#filter-bulan').val()}<br>
                <b>Tahun:</b> ${$('#filter-tahun').val()}
            `,
            icon: 'warning',
            showCancelButton: true
        }).then(r => {
            if (!r.isConfirmed) return;

            $.post("{{ route('kpp.rekon.pelaporan.gu') }}", {
                mode: 'filter',
                opd: $('#filter-opd').val(),
                bulan: $('#filter-bulan').val(),
                tahun: $('#filter-tahun').val(),
                bulan_lapor: now.getMonth() + 1,
                tahun_lapor: now.getFullYear()
            }, res => {
                Swal.fire('Berhasil', res.message, 'success');
                table.ajax.reload();
            }).fail(xhr => {
                Swal.fire('Gagal', xhr.responseJSON?.message, 'error');
            });
        });
    });

    //POSTING SELECTED GU
    $('#btn-posting-gu-select').on('click', function () {

        let ids = $('.chk-posting:checked').map(function () {
            return $(this).val();
        }).get();

        if (ids.length === 0) {
            Swal.fire('Info','Pilih data GU','info');
            return;
        }

        Swal.fire({
            title: 'Posting GU?',
            icon: 'warning',
            showCancelButton: true
        }).then(r => {
            if (!r.isConfirmed) return;

            $.post("{{ route('kpp.rekon.posting.gu.select') }}", { ids }, res => {
                Swal.fire('Berhasil', res.message, 'success');
                table.ajax.reload();
            }).fail(xhr => {
                Swal.fire('Gagal', xhr.responseJSON?.message, 'error');
            });
        });
    });

    //POSTING MASSAL GU
    $('#btn-posting-gu-massal').on('click', function () {
        Swal.fire({
            title: 'Posting GU Massal?',
            icon: 'warning',
            showCancelButton: true
        }).then(r => {
            if (!r.isConfirmed) return;

            $.post("{{ route('kpp.rekon.posting.gu.massal') }}", {
                tahun: $('#filter-tahun').val(),
                bulan: $('#filter-bulan').val(),
                opd: $('#filter-opd').val()
            }, res => {
                Swal.fire('Berhasil', res.message, 'success');
                table.ajax.reload();
            }).fail(xhr => {
                Swal.fire('Gagal', xhr.responseJSON?.message, 'error');
            });
        });
    });

});
</script>
