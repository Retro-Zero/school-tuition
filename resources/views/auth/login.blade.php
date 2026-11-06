@extends('layouts.blank')

@section('content')
    <div class="auth-shell">
        <div class="auth-card">
            <div class="auth-mark">۵</div>
            <h1 class="auth-title">{{ config('school.name') }}</h1>
            <p class="auth-sub">{{ config('school.subtitle') }} — ورود کارکنان</p>

            @if ($errors->any())
                <div class="alert alert-danger py-2 px-3" style="font-size:12.5px;border-radius:10px">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="field">
                    <label for="username" class="form-label">نام کاربری</label>
                    <input id="username" name="username" type="text" value="{{ old('username') }}"
                        class="form-control @error('username') is-invalid @enderror" required autofocus
                        autocomplete="username" placeholder="admin">
                </div>

                <div class="field">
                    <label for="password" class="form-label">رمز عبور</label>
                    <input id="password" name="password" type="password"
                        class="form-control @error('password') is-invalid @enderror" required
                        autocomplete="current-password" placeholder="••••••••">
                </div>

                <div class="form-check mb-3">
                    <input id="remember" name="remember" type="checkbox" class="form-check-input" value="1">
                    <label for="remember" class="form-check-label" style="font-size:12.5px">مرا به خاطر بسپار</label>
                </div>

                <button type="submit" class="btn btn-primary w-100">ورود به سیستم</button>
            </form>

            <div class="auth-foot">دسترسی فقط برای کارکنان مدرسه</div>
        </div>
    </div>
@endsection
