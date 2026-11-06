<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code') — {{ config('school.name') }}</title>
    <link href="{{ \App\Support\Asset::url('/plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ \App\Support\Asset::url('/css/app.css') }}" rel="stylesheet">
</head>
<body class="app">
    <div class="auth-shell">
        <div class="auth-card text-center">
            <div class="auth-mark mx-auto">@yield('code')</div>
            <h1 class="auth-title">@yield('title')</h1>
            <p class="auth-sub">@yield('message')</p>
            <a href="{{ url()->current() ? route('years.index') : '/' }}" class="btn btn-primary w-100">بازگشت به سال‌های مالی</a>
        </div>
    </div>
</body>
</html>
