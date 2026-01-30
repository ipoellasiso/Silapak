<script type="text/javascript">
$(function () {

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    var tableLS = $('#table-ls').DataTable({
        processing: true,
        serverSide: true,
        ajax: "/sp2d/ls",
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'nomor_sp2d', name: 'nomor_sp2d'},
            {data: 'nama_skpd', name: 'nama_skpd'},
            {data: 'nama_pihak_ketiga', name: 'nama_pihak_ketiga'},
            {data: 'nilai_sp2d', name: 'nilai_sp2d'},
            {data: 'tanggal_sp2d', name: 'tanggal_sp2d'},
            {data:'aksi', name:'aksi', orderable:false, searchable:false, className:'text-center'},
        ]
    });

    var tableGU = $('#table-gu').DataTable({
        processing: true,
        serverSide: true,
        ajax: "/sp2d/gu",
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nomor_sp2d' },
            { data: 'nama_skpd' },
            { data: 'nama_pihak_ketiga' },
            { data: 'nilai_sp2d', className: 'text-end' },
            { data: 'tanggal_sp2d' },
            { data: 'aksi', orderable: false, searchable: false }
        ]
    });

    var tableKKPD = $('#table-kkpd').DataTable({
        processing: true,
        serverSide: true,
        ajax: "/sp2d/kkpd",
    });

    var tableHapus = $('#table-hapus').DataTable({
        processing: true,
        serverSide: true,
        ajax: "/sp2d/hapus",
    });

    // Adjust datatables saat tab aktif
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
        $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
    });

    // 🔹 SUBMIT FORM TARIK SP2D
    $("#formTarikSp2d").on("submit", function(e){
        e.preventDefault();

        let jsonText = $('#json_data').val().trim();

        if(jsonText === ""){
            return Swal.fire("Perhatian!", "Data JSON belum diisi!", "warning");
        }

        try { JSON.parse(jsonText) }
        catch(e){
            return Swal.fire("Format Salah!", "JSON tidak valid!", "error");
        }

        Swal.fire({
            title: 'Yakin Simpan?',
            text: "Data akan disimpan ke database",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if(result.isConfirmed){
                simpanData(jsonText);
            }
        });
    });

    // 🔹 FUNGSI SIMPAN AJAX
    function simpanData(jsonText){
        $('#btnProses').prop('disabled', true).text('Proses...');

        $.ajax({
            url: "/sp2d/tarik",
            type: "POST",
            data: { json_data: jsonText },
            success: function(res){
                Swal.fire("Success!", "Data berhasil disimpan!", "success");
                $('#btnProses').prop('disabled', false).text('Simpan');
                $('#json_data').val("");
                tableLS.ajax.reload();
                tableGU.ajax.reload();
                tableKKPD.ajax.reload();
            },
            error: function(xhr){
                $('#btnProses').prop('disabled', false).text('Simpan');
                Swal.fire("Error!", xhr.responseJSON?.message || "Terjadi kesalahan!", "error");
            }
        });
    }

    // HAPUS DATA
    $('body').on('click', '.btn-hapus', function(){
        var id = $(this).data('id');
        $.post('/sp2d/hapus', {id:id}, function(res){
            Swal.fire('Success','Data berhasil dihapus!','success');
            tableLS.ajax.reload();
            tableGU.ajax.reload();
            tableKKPD.ajax.reload();
            
        });
    });

    // RESTORE DATA
    $('body').on('click', '.btn-restore', function(){
        var id = $(this).data('id');
        $.post('/sp2d/restore', {id:id}, function(res){
            Swal.fire('Success','Data berhasil direstore!','success');
            tableHapus.ajax.reload();
        });
    });

    //Detail
    $('body').on('click', '.btn-detail', function(){
        let id = $(this).data('id');

        $.get('/sp2d/detail/' + id, function(res){

            let belanja = res.belanjaLs ?? [];
            let pajak   = res.pajakPotonganLs ?? [];

            let html = `
                <h5>SP2D LS: ${res.nomor_sp2d}</h5>
                <p><b>SKPD:</b> ${res.nama_skpd}</p>
                <p><b>Pihak Ketiga:</b> ${res.nama_pihak_ketiga}</p>
                <p><b>Nilai:</b> ${Intl.NumberFormat().format(res.nilai_sp2d)}</p>
                <hr>

                <h6>Belanja</h6>
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Kode Rekening</th>
                            <th>Uraian</th>
                            <th class="text-end">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            let totalBelanja = 0;

            belanja.forEach(b => {
                totalBelanja += parseFloat(b.jumlah);
                html += `
                    <tr>
                        <td>${b.kode_rekening}</td>
                        <td>${b.uraian}</td>
                        <td class="text-end">${Intl.NumberFormat().format(b.jumlah)}</td>
                    </tr>
                `;
            });

            html += `
                <tr class="fw-bold table-secondary">
                    <td colspan="2" class="text-end">Total Belanja</td>
                    <td class="text-end">${Intl.NumberFormat().format(totalBelanja)}</td>
                </tr>
                </tbody></table>
            `;

            // ================= PAJAK =================
            if (pajak.length > 0) {
                html += `
                    <hr>
                    <h6>Pajak</h6>
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Jenis Pajak</th>
                                <th>ID Billing</th>
                                <th class="text-end">Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                let totalPajak = 0;

                pajak.forEach(p => {
                    totalPajak += parseFloat(p.nilai_sp2d_pajak_potongan);
                    html += `
                        <tr>
                            <td>${p.nama_pajak_potongan}</td>
                            <td>${p.id_billing ?? '-'}</td>
                            <td class="text-end">${Intl.NumberFormat().format(p.nilai_sp2d_pajak_potongan)}</td>
                        </tr>
                    `;
                });

                html += `
                    <tr class="fw-bold table-secondary">
                        <td colspan="2" class="text-end">Total Pajak</td>
                        <td class="text-end">${Intl.NumberFormat().format(totalPajak)}</td>
                    </tr>
                    </tbody></table>
                `;
            }

            $('#detailContent').html(html);
            $('#modalDetail').modal('show');
        });
    });

    $('body').on('click', '.btn-detail-gu', function () {
        let id = $(this).data('id');

        $.get('/sp2d/detail/' + id, function (res) {

            let belanja = res.belanjaLs ?? [];

            let html = `
                <h5>SP2D GU: ${res.nomor_sp2d}</h5>
                <p><b>SKPD:</b> ${res.nama_skpd}</p>
                <p><b>Nilai:</b> ${Intl.NumberFormat().format(res.nilai_sp2d)}</p>
                <hr>

                <div class="accordion" id="accordionGU">
            `;

            // ================= GROUP BY KEGIATAN =================
            let group = {};
            belanja.forEach(b => {
                let key = b.kode_kegiatan ?? 'TANPA_KEGIATAN';

                if (!group[key]) {
                    group[key] = {
                        kode: b.kode_kegiatan,
                        nama: b.nama_kegiatan ?? 'Tanpa Kegiatan',
                        rows: [],
                        subtotal: 0
                    };
                }

                group[key].rows.push(b);
                group[key].subtotal += parseFloat(b.jumlah);
            });

            let i = 0;

            // ================= RENDER ACCORDION =================
            Object.values(group).forEach(g => {
                i++;

                html += `
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading${i}">
                        <button class="accordion-button ${i > 1 ? 'collapsed' : ''}"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapse${i}">
                            ${g.nama}
                            <span class="ms-auto fw-bold">
                                ${Intl.NumberFormat().format(g.subtotal)}
                            </span>
                        </button>
                    </h2>

                    <div id="collapse${i}"
                        class="accordion-collapse collapse ${i === 1 ? 'show' : ''}"
                        data-bs-parent="#accordionGU">

                        <div class="accordion-body p-2">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Sub Kegiatan</th>
                                        <th>Kode Rekening</th>
                                        <th>Uraian</th>
                                        <th class="text-end">Jumlah</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;

                g.rows.forEach(r => {
                    html += `
                        <tr>
                            <td>${r.nama_sub_kegiatan ?? '-'}</td>
                            <td>${r.kode_rekening}</td>
                            <td>${r.uraian}</td>
                            <td class="text-end">${Intl.NumberFormat().format(r.jumlah)}</td>
                        </tr>
                    `;
                });

                html += `
                                <tr class="fw-bold table-secondary">
                                    <td colspan="3" class="text-end">Subtotal</td>
                                    <td class="text-end">
                                        ${Intl.NumberFormat().format(g.subtotal)}
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                `;
            });

            html += `</div>`;

            $('#detailContent').html(html);
            $('#modalDetail').modal('show');
        });
    });

    function loadTotalSp2dBulanan() {
        $.get('/sp2d/total-bulanan', function (res) {

            const bulan = {
                1:'Januari',2:'Februari',3:'Maret',4:'April',
                5:'Mei',6:'Juni',7:'Juli',8:'Agustus',
                9:'September',10:'Oktober',11:'November',12:'Desember'
            };

            let map = {};
            res.forEach(r => {
                map[r.bulan] = r;
            });

            let html = '';

            for (let i = 1; i <= 12; i++) {

                let jml   = map[i]?.jumlah ?? 0;
                let total = map[i]?.total ?? 0;

                html += `
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="fw-semibold text-muted">
                                    Total SP2D ${bulan[i]} <span class="text-primary">${jml}</span>
                                </div>
                                <div class="mt-2 fw-bold text-dark">
                                    Rp. ${Intl.NumberFormat('id-ID').format(total)}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }

            $('#cardTotalSp2d').html(html);
        });
    }

    loadTotalSp2dBulanan();

});
</script>
