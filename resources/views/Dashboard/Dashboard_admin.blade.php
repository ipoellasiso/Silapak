@extends('Template.Layout')
@section('content')

<style>
    .stat-card {
        display:flex;align-items:center;gap:12px;
        background:#fff;padding:14px 18px;border-radius:12px;
        box-shadow:0 4px 12px rgba(0,0,0,0.05);
    }
    .stat-icon {
        width:45px;height:45px;border-radius:10px;display:flex;
        justify-content:center;align-items:center;color:white;font-size:22px;
    }
    .chart-card {
        background:#fff;padding:18px;border-radius:12px;
        box-shadow:0 4px 12px rgba(0,0,0,0.05);
        min-height:380px;
    }
    #chartPajak {
        width: 100% !important;
        height: 360px !important; /* bebas 300–450px */
    }

    .chart-card {
        background:#fff;
        border-radius:14px;
        padding:18px;
        box-shadow:0 4px 12px rgba(0,0,0,0.05);
        height: 420px;
        display:flex;
        flex-direction:column;
    }
    #profileChart, #statusChart {
        flex:1;
    }
</style>

<div class="container-fluid py-4">

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#7367F0;">
                    <i class="bi bi-building"></i>
                </div>
                <div><small class="text-muted">Total OPD</small><div class="fw-bold fs-4">{{ $totalOpd }}</div></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#39C0ED;">
                    <i class="bi bi-people"></i>
                </div>
                <div><small class="text-muted">Total User</small><div class="fw-bold fs-4">{{ $totalUser }}</div></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#FFBB33;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div><small class="text-muted">Belum Verifikasi</small><div class="fw-bold fs-4">{{ $totalBelum }}</div></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#00C851;">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <div><small class="text-muted">Pajak Diterima</small><div class="fw-bold fs-4">{{ $totalTerima }}</div></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon" style="background:#ff4444;">
                    <i class="bi bi-x-circle"></i>
                </div>
                <div><small class="text-muted">Pajak Ditolak</small><div class="fw-bold fs-4">{{ $totalTolak }}</div></div>
            </div>
        </div>
    </div>

    {{-- <form method="GET" class="mb-3">
        <div style="width:200px;">
            <select name="tahun" class="form-select" onchange="this.form.submit()">
                @foreach($listTahun as $th)
                    <option value="{{ $th }}" {{ $tahun == $th ? 'selected' : '' }}>Tahun {{ $th }}</option>
                @endforeach
            </select>
        </div>
    </form> --}}

    <br><br>
    <div class="row g-3">
        <div class="col-xl-12 col-lg-12">
            <div class="card-body">
                <h5 class="fw-bold mb-3">Grafik PerBulan</h5>
                <div id="profileChart"></div>
            </div>
        </div>
        {{-- <div class="col-xl-4 col-lg-12">
            <div class="chart-card">
                <h6 class="fw-bold mb-3">Status Pajak</h6>
                <canvas id="chartStatus"></canvas>
            </div>
        </div> --}}
    </div>

</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (sessionStorage.getItem('login_success') === '1') {

        Swal.mixin({
            toast: true,
            position: 'top-end',
            iconColor: 'white',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            customClass: {
                popup: 'colored-toast success'
            }
        }).fire({
            icon: 'success',
            title: 'Login berhasil'
        });

        // 🔥 hapus flag supaya tidak muncul lagi
        sessionStorage.removeItem('login_success');
    }
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {

    // ============= LINE CHART =============
    const dataChart = @json($chartBulan);

    var options = {
        chart: {
            type: 'bar',
            height: 350,
            background: '#ffffff',
            toolbar: { show: true }
        },
        plotOptions: {
            bar: {
                borderRadius: 6,
                columnWidth: '45%',
            }
        },
        dataLabels: { enabled: false },
        stroke: {
            show: true,
            width: 2,
            colors: ['#435ebe']
        },
        series: [{
            name: "Jumlah Pajak (Rp)",
            data: dataChart
        }],
        xaxis: {
            categories: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']
        },
        yaxis: {
            title: { text: 'Jumlah' }
        },
        grid: {
            show: true,
            borderColor: '#e0e6ed',
            strokeDashArray: 0,
        },
        fill: {
            opacity: 1,
            colors: ['#435ebe'] // warna solid (bukan transparan)
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val.toLocaleString('id-ID');
                }
            }
        }
    };

    var chart = new ApexCharts(document.querySelector("#profileChart"), options);
    chart.render();

    // ============= PIE CHART =============
    const status = @json($statusPie);
    const ctx2 = document.getElementById('chartStatus').getContext('2d');

    new Chart(ctx2, {
        type: 'pie',
        data: {
            labels: ['Belum Verifikasi', 'Diterima', 'Ditolak'],
            datasets: [{
                data: [status.belum, status.terima, status.tolak],
                backgroundColor: ['#FFBB33','#00C851','#ff4444'],
                borderColor:'#fff',borderWidth:2
            }]
        },
        options:{
            responsive:true,
            plugins:{
                legend:{ position:'bottom' },
                tooltip:{
                    callbacks:{
                        label:(c)=>`${c.label}: ${c.raw.toLocaleString('id-ID')} data`
                    }
                }
            }
        }
    });

});
</script>

{{-- NOTICE PAJAK LS + WHATSAPP ADMIN --}}
@if($pajakLsBelumInput->count() > 0)
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ================== PESAN WHATSAPP ==================
    let pesanWa = `
📢 NOTICE PAJAK LS

Terdapat {{ $pajakLsBelumInput->count() }} SP2D
dengan {{ $totalPajakBelumInput }} Pajak LS
yang belum diinput lebih dari 2 hari.

Mohon segera ditindaklanjuti 🙏
Ayoo semangat Kaka 😄💪😂
    `;

    // ================== LINK WA ==================
    let waApp = "whatsapp://send?phone=6285342157722&text=" + encodeURIComponent(pesanWa);
    let waWeb = "https://wa.me/6285342157722?text=" + encodeURIComponent(pesanWa);

    Swal.fire({
        icon: 'warning',
        title: 'NOTICE PAJAK LS',
        html: `
            <div style="line-height:1.6">
                Terdapat <b>{{ $pajakLsBelumInput->count() }}</b> SP2D<br>
                dengan <b>{{ $totalPajakBelumInput }}</b> Pajak LS<br>
                yang belum diinput lebih dari 2 hari.<br><br>
                <small>
                    mohon diinput ya.. agar tidak menumpuk lebih banyak lagi..<br>
                    ayoo semangat Kaka 😄💪😂
                </small>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '📱 Kirim ke WA Group Silapak',
        cancelButtonText: '📥 Download Excel',
        confirmButtonColor: '#25D366',
        cancelButtonColor: '#198754',
        reverseButtons: true
    }).then((result) => {

        // 👉 KIRIM KE WA (SATU KLIK)
        if (result.isConfirmed) {

            // coba buka WA app
            let timer = setTimeout(() => {
                window.open(waWeb, '_blank');
            }, 700);

            window.location.href = waApp;

            // kalau app terbuka, cancel fallback
            window.addEventListener('blur', () => clearTimeout(timer));
        }

        // 👉 DOWNLOAD EXCEL
        if (result.dismiss === Swal.DismissReason.cancel) {
            window.location.href = "{{ route('pajakls.export-belum-input') }}";
        }

    });
});
</script>
@endif