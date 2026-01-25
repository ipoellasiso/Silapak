<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silapak - Halaman Login</title>
    <link rel="stylesheet" href="{{ asset('auth/style.css') }}">
    <link rel="stylesheet" href="{{ asset('auth/login.css') }}">
</head>

<body>
    {{-- <div class="wave-bg"></div> --}}
<div class="page">
    
    <!-- LOGO ATAS -->
    <div class="top-logos">
        <img src="/app/assets/images/logo/13.png" class="logo palu" alt="Kota Palu">
        <img src="/app/assets/images/112a.png" class="logo silapak" alt="SiLAPAK Palu">
    </div>

    <!-- LOGIN CARD -->
    <div class="login-card glass-card">

        <h2>Sistem Informasi Pelaporan Pajak Daerah Kota Palu</h2>
        <p><span>Silahkan Login</span></p>

        <form id="loginForm">
            @csrf

            <div class="form-group">
                <select name="tahun" required>
                    <option hidden>Pilih Tahun</option>
                    <option>2025</option>
                    <option selected>2026</option>
                </select>
            </div>

            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>

            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="button" id="btnLogin" onclick="submitLogin()">Login</button>
        </form>

    </div>
</div>

<div id="loadingOverlay">
    <div class="loading-box">
        <img src="/app/assets/images/112a.png"
             alt="Loading SILAPAK"
             class="spinner-logo">
        <p>Memverifikasi akun...</p>
    </div>
</div>

    {{-- ================= SWEETALERT2 ================= --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- SUCCESS --}}
    @if (session('success'))
    <script>
        Swal.mixin({
            toast: true,
            position: 'top-end',
            iconColor: 'white',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            customClass: { popup: 'colored-toast success' }
        }).fire({
            icon: 'success',
            title: "{{ session('success') }}"
        });
    </script>
    @endif

    {{-- ERROR --}}
    @if (session('error'))
    <script>
        Swal.mixin({
            toast: true,
            position: 'top-end',
            iconColor: 'white',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            customClass: { popup: 'colored-toast error' }
        }).fire({
            icon: 'error',
            title: "{{ session('error') }}"
        });
    </script>
    @endif

    {{-- QUESTION --}}
    @if (session('question'))
    <script>
        Swal.mixin({
            toast: true,
            position: 'top-end',
            iconColor: 'white',
            showConfirmButton: false,
            timer: 4500,
            timerProgressBar: true,
            customClass: { popup: 'colored-toast question' }
        }).fire({
            icon: 'question',
            title: "{{ session('question') }}"
        });
    </script>
    @endif

    {{-- <script src="{{ asset('auth/script.js') }}"></script> --}}

    <script>
        function submitLogin(){
            const overlay = document.getElementById('loadingOverlay');
            const btn = document.getElementById('btnLogin');
            const form = document.getElementById('loginForm');

            overlay.style.display = 'flex';
            btn.disabled = true;
            btn.innerHTML = 'Memverifikasi akun...';

            const formData = new FormData(form);

            fetch('/cek_login', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'success'){
                    // 🔥 simpan flag login sukses
                    sessionStorage.setItem('login_success', '1');

                    // 🔥 TUNGGU SPINNER BERPUTAR DULU
                    setTimeout(() => {
                        window.location.href = res.redirect;
                    }, 1500); // ⏱️ 1 DETIK
                } else {
                    throw res.message;
                }
            })
            .catch(err => {
                overlay.style.display = 'none';
                btn.disabled = false;
                btn.innerHTML = 'Login';

                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal',
                    text: err
                });
            });
        }
    </script>

</body>
</html>