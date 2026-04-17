<!DOCTYPE html>
<html lang="en" class="material-style layout-fixed">

<head>
    <title>POS Inventory</title>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="description"
        content="Empire Bootstrap admin template made using Bootstrap 4, it has tons of ready made feature, UI components, pages which completely fulfills any dashboard needs." />
    <meta name="keywords"
        content="Empire, bootstrap admin template, bootstrap admin panel, bootstrap 4 admin template, admin template">
    <meta name="author" content="Srthemesvilla" />

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/logo-inv.png') }}">

    <!-- Google fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">

    <!-- Icon fonts -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/ionicons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/linearicons.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/open-iconic.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/pe-icon-7-stroke.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fonts/feather.css') }}">
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- Core stylesheets -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-material.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/shreerang-material.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/uikit.css') }}">

    <!-- Libs -->
    <link rel="stylesheet" href="{{ asset('assets/libs/perfect-scrollbar/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/flot/flot.css') }}">
    <!-- Page -->
    <link rel="stylesheet" href="{{ asset('assets/css/pages/authentication.css') }}">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    @yield('style')

    <style>
        #layout-sidenav,
        #layout-navbar {
            z-index: 0;
        }

        .table-modern {
            border: 1px solid #e4e6ef;
            border-radius: 6px;
            overflow: hidden;
        }

        .table-modern thead th {
            background: #f8f9fa;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            border: 1px solid #e4e6ef;
        }

        .table-modern tbody td {
            border: 1px solid #e9ecef;
        }

        .table-modern tbody tr:nth-child(odd) {
            background: #fafafa;
        }

        .table-modern tbody tr:nth-child(even) {
            background: #f3f4f6;
        }

        .table-modern tbody tr:hover {
            background: #e9ecef;
            transition: 0.2s;
        }

        .checkbox-col {
            width: 50px;
            text-align: center;
        }

        .checkbox-col input {
            transform: scale(1.2);
        }

        .sort-icon {
            font-size: 11px;
            margin-left: 5px;
            color: #9aa0a6;
        }

        .action-btn {
            padding: 5px 10px;
            font-size: 12px;
        }
    </style>

</head>

<body>


    <!-- [ Preloader ] Start -->
    <div class="page-loader">
        <div class="bg-primary"></div>
    </div>
    <!-- [ Preloader ] End -->

    <!-- [ Layout wrapper ] Start -->
    <div class="layout-wrapper layout-2">
        <div class="layout-inner">
            <!-- [ Layout sidenav ] Start -->
            @include('components.sidebar')
            <!-- [ Layout sidenav ] End -->
            <!-- [ Layout container ] Start -->
            <div class="layout-container">
                <!-- [ Layout navbar ( Header ) ] Start -->
                @include('components.header')
                <!-- [ Layout navbar ( Header ) ] End -->

                <!-- [ Layout content ] Start -->

                @yield('content')

                <!-- [ Layout content ] Start -->
            </div>
            <!-- [ Layout container ] End -->
        </div>
        <!-- Overlay -->
        <div class="layout-overlay layout-sidenav-toggle"></div>
    </div>
    <!-- [ Layout wrapper] End -->

    <!-- Core scripts -->
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script> --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="{{ asset('assets/js/pace.js') }}"></script>
    {{-- <script src="{{ asset('assets/js/jquery-3.3.1.min.js') }}"></script> --}}
    <script src="{{ asset('assets/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/js/sidenav.js') }}"></script>
    <script src="{{ asset('assets/js/layout-helpers.js') }}"></script>
    <script src="{{ asset('assets/js/material-ripple.js') }}"></script>

    <!-- Libs -->
    <script src="{{ asset('assets/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/libs/eve/eve.js') }}"></script>
    <script src="{{ asset('assets/libs/flot/flot.js') }}"></script>
    <script src="{{ asset('assets/libs/flot/curvedLines.js') }}"></script>
    <script src="{{ asset('assets/libs/chart-am4/core.js') }}"></script>
    <script src="{{ asset('assets/libs/chart-am4/charts.js') }}"></script>
    <script src="{{ asset('assets/libs/chart-am4/animated.js') }}"></script>

    <!-- Demo -->
    <script src="{{ asset('assets/js/demo.js') }}"></script>
    <script src="{{ asset('assets/js/analytics.js') }}"></script>
    <script src="{{ asset('assets/js/pages/dashboards_index.js') }}"></script>

    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        function formatRupiah(angka) {

            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(angka);

        }

        window.addEventListener("load", () => {

            const el = document.querySelector(
                'a[href="https://codedthemes.com/item/empire-bootstrap-admin-template/"]');

            console.log(el);
            // el.style.display = 'none'

        });
        feather.replace();
    </script>
    @yield('scripts')

</body>

</html>
