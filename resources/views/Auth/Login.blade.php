<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Silapak - Halaman Login</title>
    <link rel="stylesheet" href="{{ asset('auth/style.css') }}">
</head>

<style>
/* ================= GLOBAL ================= */
* {
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', system-ui, sans-serif;
    min-height: 100vh;
    margin: 0;
    background: linear-gradient(135deg,#4f5ec2 0%,#eef3f8 45%,#40bd6e 100%);
}

/* ================= LAYOUT ================= */
.login-page {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    gap: 80px;
    padding: 40px;
}

/* ================= LOGIN CARD ================= */
.login-card {
    width: 380px;
    background: rgba(255,255,255,0.75);
    backdrop-filter: blur(14px);
    border-radius: 22px;
    padding: 40px 32px;
    box-shadow:
        0 20px 45px rgba(0,0,0,.18),
        inset 0 0 0 1px rgba(255,255,255,.4);
    z-index: 2;
}

/* ================= HEADER ================= */
.login-header {
    text-align: center;
    margin-bottom: 25px;
}

.login-header img {
    width: 150px;
    margin-bottom: 12px;
}

.login-header span {
    display: block;
    font-size: 15px;
    color: #5b638b;
}

/* ================= INPUT ================= */
.form-group {
    margin-bottom: 18px;
}

.neu-input {
    position: relative;
}

.neu-input input,
.neu-input select {
    width: 100%;
    border: none;
    outline: none;
    background: #eef3f8;
    border-radius: 14px;
    padding: 18px 20px;
    font-size: 15px;
    box-shadow:
        inset 4px 4px 8px #d9dee5,
        inset -4px -4px 8px #ffffff;
}

.neu-input label {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 14px;
    color: #8891b2;
    pointer-events: none;
    transition: .3s;
}

.neu-input input:focus + label,
.neu-input input:not(:placeholder-shown) + label {
    top: 6px;
    font-size: 11px;
}

/* ================= BUTTON ================= */
.login-btn {
    width: 100%;
    margin-top: 10px;
    border: none;
    border-radius: 16px;
    padding: 15px;
    font-size: 16px;
    font-weight: 700;
    color: #1f4fa3;
    background: #eef3f8;
    cursor: pointer;
    box-shadow:
        6px 6px 14px #d0d6e1,
        -6px -6px 14px #ffffff;
    transition: .3s;
}

.login-btn:hover {
    transform: translateY(-2px);
}

/* ================= SIDE LOGO ================= */
.side-logo {
    width: 260px;
    display: flex;
    justify-content: center;
    opacity: 0;
}

.side-logo img {
    width: 200px;
    filter: drop-shadow(0 20px 30px rgba(0,0,0,.25));
    animation: float 4s ease-in-out infinite;
}

/* ================= ANIMATION ================= */
.side-logo.left {
    animation: slideLeft 1.2s ease forwards;
}

.side-logo.right {
    animation: slideRight 1.2s ease forwards;
    animation-delay: .3s;
}

@keyframes slideLeft {
    from { opacity: 0; transform: translateX(-100px); }
    to   { opacity: 1; transform: translateX(0); }
}

@keyframes slideRight {
    from { opacity: 0; transform: translateX(100px); }
    to   { opacity: 1; transform: translateX(0); }
}

@keyframes float {
    0%,100% { transform: translateY(0); }
    50%     { transform: translateY(-14px); }
}

/* ================= RESPONSIVE ================= */
@media (max-width: 992px) {
    .side-logo {
        display: none;
    }

    .login-page {
        gap: 0;
    }
}
</style>
</head>

<body>

<div class="login-page">

    {{-- LOGO KIRI --}}
    <div class="side-logo left">
        <img src="/app/assets/images/logo/13.png" style="width: 30%; height: 30%" alt="Logo Kota Palu">
    </div>

    {{-- LOGIN CARD --}}
    <div class="login-card">

        <div class="login-header">
            <img src="/app/assets/images/logo-silapak.png" alt="Sistem Informasi Pelaporan Pajak Daerah Kota Palu">
            <span>Silahkan Login</span>
        </div>

        <form method="POST" action="/cek_login">
            @csrf

            <div class="form-group">
                <div class="neu-input">
                    <select name="tahun" required>
                        <option value="" disabled selected hidden></option>
                        <option>2025</option>
                        <option>2026</option>
                    </select>
                    <label>Tahun Anggaran</label>
                </div>
            </div>

            <div class="form-group">
                <div class="neu-input">
                    <input type="email" name="email" required placeholder=" ">
                    <label>Email</label>
                </div>
            </div>

            <div class="form-group">
                <div class="neu-input">
                    <input type="password" name="password" required placeholder=" ">
                    <label>Password</label>
                </div>
            </div>

            <button class="login-btn">Login</button>

        </form>
    </div>

    {{-- LOGO KANAN --}}
    <div class="side-logo right">
        <img src="/app/assets/images/112a.png" style="width: 100%; height: 100%" alt="SiLAPAK">
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

    <script src="{{ asset('auth/script.js') }}"></script>

</body>
</html>