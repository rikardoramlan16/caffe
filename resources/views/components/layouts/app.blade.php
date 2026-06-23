<!doctype html>
<html lang="id" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'CafeFlow - Modern Cafe Management System' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ !empty($appLogo) ? asset($appLogo) : asset('favicon.ico') }}">
    <meta name="description" content="Cafe management system untuk QR ordering, kasir, barista, pelayan, multi cabang, dan laporan realtime.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    {{ $slot }}
</body>
</html>
