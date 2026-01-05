    <script src="/assets/static/js/components/dark.js"></script>
    <script src="/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js"></script>

    <script src="/assets/compiled/js/app.js"></script>

    <script src="/assets/extensions/apexcharts/apexcharts.min.js"></script>
    <script src="/assets/static/js/pages/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

    {{-- <script src="https://cdn.jsdelivr.net/npm/numbro@2.3.1/dist/numbro.min.js"></script> --}}

    <script>
        // aktifkan semua tooltip bootstrap di seluruh halaman
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>

    <div class="container my-3">
        @if (@session('success'))
            <script>

                const Toast = swal.mixin({
                    toast:true,
                    position: 'top-right',
                    iconColor: 'white',
                    customClass: {
                        popup: 'colored-toast'
                    },
                    showConfirmButton: false,
                    timer:4500,
                    timeProgressBar: true
                })
                Toast.fire({
                    icon: 'success',
                    title: "{{ session('success') }}"
                })
            </script>
        @endif

        @if (@session('error'))
            <script>
                // swal.fire({
                //     icon: 'success',
                //     title: 'Wow ...',
                //     text: "{{ session('success') }}",
                //     timer: 2500
                // })

                const Toast = swal.mixin({
                    toast:true,
                    position: 'top-right',
                    iconColor: 'white',
                    customClass: {
                        popup: 'colored-toast'
                    },
                    showConfirmButton: false,
                    timer:4500,
                    timeProgressBar: true
                })
                Toast.fire({
                    icon: 'error',
                    title: "{{ session('error') }}"
                })
            </script>
        @endif

        @if (@session('question'))
            <script>
                // swal.fire({
                //     icon: 'success',
                //     title: 'Wow ...',
                //     text: "{{ session('success') }}",
                //     timer: 2500
                // })

                const Toast = swal.mixin({
                    toast:true,
                    position: 'top-right',
                    iconColor: 'white',
                    customClass: {
                        popup: 'colored-toast'
                    },
                    showConfirmButton: false,
                    timer:4500,
                    timeProgressBar: true
                })
                Toast.fire({
                    icon: 'question',
                    title: "{{ session('question') }}"
                })
            </script>
        @endif

        <script>
            function numberToBrackets(number) {
            if (number < 0) {
                return `(${Math.abs(number)})`;
            }
            return number;
            }
        </script>
        
    </div>
    