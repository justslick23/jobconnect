<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@yield('title') | CBS Recruitment Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no" />

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('assets/img/CBS logo.png') }}" type="image/png" />

    {{-- Meta for SEO and branding --}}
    <meta name="description" content="CBS Recruitment Portal - Apply and manage job applications at CBS.">
    <meta name="author" content="CBS">
    <meta name="keywords" content="CBS, Recruitment, Jobs, Careers, Portal">

    {{-- CSRF Token --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Fonts and icons --}}
    <script src="{{ asset('assets/js/plugin/webfont/webfont.min.js') }}"></script>
    <script>
        WebFont.load({
            google: { families: ["Public Sans:300,400,500,600,700"] },
            custom: {
                families: [
                    "Font Awesome 5 Solid",
                    "Font Awesome 5 Regular",
                    "Font Awesome 5 Brands",
                    "simple-line-icons"
                ],
                urls: ["{{ asset('assets/css/fonts.min.css') }}"]
            },
            active: function () { sessionStorage.fonts = true; }
        });
    </script>

    {{-- CSS Files --}}
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/plugins.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/kaiadmin.min.css') }}" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" integrity="sha512-yZ+gsy3uVZ3A0j+pUdPzvshn0e6vZBZFeZ2Y3MfHdLNu0sBuUcmUGWyovCv2X6B9UyaHewq3bdavZCsmZ3" crossorigin="anonymous" referrerpolicy="no-referrer" />

    {{-- Optional demo CSS (remove in production) --}}
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
</head>

    
<body>
    <div class="wrapper">
        @include('layouts.sidebar')
        
        <div class="main-panel">
            @include('layouts.navbar')
            
                    @yield('content')
           
        
                    <script src="{{ asset('assets/js/core/jquery-3.7.1.min.js') }}"></script>
                    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
                    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
                
                    <!-- jQuery Scrollbar -->
                    <script src="{{ asset('assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
                
                    <!-- Chart JS -->
                    <script src="{{ asset('assets/js/plugin/chart.js/chart.min.js') }}"></script>
                
                    <!-- jQuery Sparkline -->
                    <script src="{{ asset('assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js') }}"></script>
                
                    <!-- Chart Circle -->
                    <script src="{{ asset('assets/js/plugin/chart-circle/circles.min.js') }}"></script>
                
                    <!-- Datatables -->
                    <script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>
                
                    <!-- Bootstrap Notify -->
                    <script src="{{ asset('assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js') }}"></script>
                
                    <!-- jQuery Vector Maps -->
                    <script src="{{ asset('assets/js/plugin/jsvectormap/jsvectormap.min.js') }}"></script>
                    <script src="{{ asset('assets/js/plugin/jsvectormap/world.js') }}"></script>
                
                    <!-- Sweet Alert -->
                    <script src="{{ asset('assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
                
                    <!-- Kaiadmin Main JS -->
                    <script src="{{ asset('assets/js/kaiadmin.min.js') }}"></script>
                
                 
                
                    @yield('scripts')
            @yield('scripts')  {{-- Corrected yield directive --}}
        </div>
    </div>
</body>
</html>
