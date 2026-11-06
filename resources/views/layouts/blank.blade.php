<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود — {{ config('school.name') }}</title>
    <link href="{{ \App\Support\Asset::url('/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ \App\Support\Asset::url('/css/style.css') }}" rel="stylesheet">
    <link href="{{ \App\Support\Asset::url('/css/icons.css') }}" rel="stylesheet">
    <link href="{{ \App\Support\Asset::url('/css/app.css') }}" rel="stylesheet">
</head>
<body class="app light-mode">
    @yield('content')
    <script src="{{ \App\Support\Asset::url('/js/jquery.min.js') }}"></script>
    <script src="{{ \App\Support\Asset::url('/plugins/bootstrap/js/boostrap.min.js') }}"></script>
</body>
</html>
