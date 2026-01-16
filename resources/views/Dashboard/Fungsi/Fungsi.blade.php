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


    document.addEventListener("DOMContentLoaded", function() {

        const dataChart = @json($chartBulan);

        console.log("Chart data:", dataChart);

        const ctx = document.getElementById('chartPajak');
        if (!ctx) {
            console.error("Canvas tidak ditemukan!");
            return;
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'],
                datasets: [{
                    label: 'Jumlah Pajak (Rp)',
                    data: dataChart,
                    backgroundColor: 'rgba(75, 85, 255, 0.4)',
                    borderColor: 'rgba(75, 85, 255, 1)',
                    borderWidth: 2,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    });

});

</script>