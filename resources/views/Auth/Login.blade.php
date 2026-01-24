<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neumorphism Login Form</title>
    <link rel="stylesheet" href="{{ asset('auth/style.css') }}">
</head>

<style>
    /* === Neumorphism Select (GLOBAL) === */
.neu-select select {
    width: 100%;
    appearance: none;
    background: transparent;
    border: none;
    outline: none;
    font-size: 16px;
    color: #3d4468;
    padding: 20px 24px;
    padding-left: 55px;
    cursor: pointer;
}

.neu-select label {
    position: absolute;
    left: 55px;
    top: 50%;
    transform: translateY(-50%);
    color: #9499b7;
    font-size: 16px;
    pointer-events: none;
    transition: all 0.3s ease;
}

.neu-select select:focus + label,
.neu-select select.has-value + label {
    top: 8px;
    font-size: 12px;
    color: #6c7293;
}

.neu-select .input-icon {
    pointer-events: none;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;

    background: linear-gradient(
        135deg,
        #4f5ec2 0%,
        #f2f7fb 40%,
        #40bd6e 100%
    );
}

.login-card {
    background: #eef3f8;
}

.login-header h2 {
    color: #1f4fa3;
}

.neu-button {
    color: #1f4fa3;
    font-weight: 700;
}
</style>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="neu-icon">
                    <div class="icon-inner">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                </div>
                <!-- <h2>Welcome back</h2> -->
                <div class="app-logo">
                    <img src="/app/assets/images/112a.png" style="width: 100%; height: 100%"  alt="Logo" srcset="">
                    <span>Silahkan Login Bang</span>
                </div>
            </div>
            
            {{-- FORM LOGIN --}}
        <form method="POST" class="my-login-validation" action="/cek_login">
            @csrf

            {{-- SELECT TAHUN --}}
            <div class="form-group">
                <div class="input-group neu-input neu-select">
                    <select name="tahun" required>
                        <option value="" disabled selected hidden></option>
                        <option value="2025">2025</option>
                        <option value="2026">2026</option>
                    </select>
                    <label>Tahun Anggaran</label>

                    <div class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </div>
                </div>
            </div>

            {{-- EMAIL --}}
            <div class="form-group">
                <div class="input-group neu-input">
                    <input type="email" id="email" name="email" required autocomplete="email" placeholder=" ">
                    <label for="email">Email address</label>
                    <div class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </div>
                </div>
                <span class="error-message" id="emailError"></span>
            </div>

            {{-- PASSWORD --}}
            <div class="form-group">
                <div class="input-group neu-input password-group">
                    <input type="password" id="password" name="password" required autocomplete="current-password" placeholder=" ">
                    <label for="password">Password</label>
                    <div class="input-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                    </div>
                    <button type="button" class="password-toggle neu-toggle" id="passwordToggle" aria-label="Toggle password visibility">
                        <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
                <span class="error-message" id="passwordError"></span>
            </div>

            {{-- BUTTON --}}
            <button type="submit" class="neu-button login-btn">
                Login
            </button>

        </form>

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