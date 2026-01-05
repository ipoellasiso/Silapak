<!DOCTYPE html>
<html lang="en">

<head>
    @include('Template.Head')
</head>

<body>
    <script src="/assets/static/js/initTheme.js"></script>
    <div id="app">

        {{-- sidebar --}}
        @include('Template.Sidebar')

        <div id="main" class='layout-navbar navbar-fixed'>
            @include('Template.Navbar')

            <div id="main-content">              
                <div class="page-heading">
                    <div class="page-title">
                        <div class="row">
                            <div class="col-12 col-md-6 order-md-1 order-last">
                                {{-- <h3>{{ $title}}</h3> --}}
                                {{-- <p class="text-subtitle text-muted">Home</p> --}}
                            </div>
                            <div class="col-12 col-md-6 order-md-2 order-first">
                                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">{{ $breadcumd }}</a></li>
                                        <li class="breadcrumb-item"><a href="index.html">{{ $breadcumd1 }}</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">{{ $breadcumd2 }}</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>

                    <section class="section">
                        @yield('content')
                    </section>
                </div>
            </div>

            <footer>
                @include('Template.Footer')
            </footer>

        </div>
    </div>

    {{-- Script --}}
    @include('Template.Script')
    
</body>

</html>