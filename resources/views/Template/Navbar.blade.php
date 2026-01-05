<header>
    <nav class="navbar navbar-expand navbar-light navbar-top">
        <div class="container-fluid">
            <a href="#" class="burger-btn d-block">
                <i class="bi bi-justify fs-3"></i>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-lg-0">
                    <li class="nav-item dropdown me-1">
                    </li>
                    <li class="nav-item dropdown me-3">
                    </li>
                </ul>
                <div class="dropdown">
                    <a href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-menu d-flex">
                            <div class="user-name text-end me-3">
                                <h6 class="mb-0 text-gray-600">{{ $userx->fullname }}</h6>
                                <p class="mb-0 text-sm text-gray-600">{{ $userx->role }} {{ $userx->tahun }}</p>
                                {{-- <p class="mb-0 text-sm text-gray-600">{{ $userx->tahun }}</p> --}}
                            </div>
                            <div class="user-img d-flex align-items-center">
                                <div class="avatar avatar-md">
                                    @if(Auth::user()->role == 'Admin' || Auth::user()->role == 'Verifikasi' || Auth::user()->role == 'User')
                                        <img src="/app/assets/images/user/{{ $userx->gambar }}" alt="">
                                    @endif
                                    {{-- <img src="/assets/compiled/jpg/1.jpg"> --}}
                                </div>
                            </div>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton" style="min-width: 11rem;">
                        <li>
                            {{-- <h6 class="dropdown-header">Hello, John!</h6> --}}
                        </li>
                        <li><a class="dropdown-item" href="#"><i class="icon-mid bi bi-person me-2"></i> My
                                Profile</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            @if(Auth::user()->role == 'Admin' || Auth::user()->role == 'Verifikasi' || Auth::user()->role == 'User')
                                <a class="dropdown-item" href="/logout"><i
                                class="icon-mid bi bi-box-arrow-left me-2"></i> Logout</a>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</header>