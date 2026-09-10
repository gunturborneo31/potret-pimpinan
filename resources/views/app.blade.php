<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>Layanan Komunikasi dan Dokumentasi  Pimpinan</title>
        <meta name="author" content="rezapadillah">
        <meta name="description" content="Layanan Komunikasi dan Dokumentasi  Pimpinan Pemerintah Kabupaten Mahakam Ulu">
        <meta name="keywords" content="Layanan Komunikasi dan Dokumentasi  Pimpinan, Mahulu, Mahakam Ulu, dokumentasi pimpinan">
        <meta property="og:title" content="Layanan Komunikasi dan Dokumentasi  Pimpinan">
        <meta property="og:site_name" content="Layanan Komunikasi dan Dokumentasi  Pimpinan">
        <meta property="og:url" content="https://potretpimpinan.mahuluprov.go.id/">
        <meta property="og:description" content="Layanan Komunikasi dan Dokumentasi  Pimpinan Pemerintah Kabupaten Mahakam Ulu">
        <meta property="og:type" content="website">
        <meta property="og:image" content="{{ asset('img/og-image.jpg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />


        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-K226CDES96"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-K226CDES96');
        </script>
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
