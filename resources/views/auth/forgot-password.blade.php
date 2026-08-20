<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password | ThriftMarket</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="storefront-body">
<div class="storefront-shell auth-shell">
    <div class="auth-layout">
        <aside class="auth-brand-panel" aria-label="Password reset help">
            <p class="auth-kicker">ACCOUNT RECOVERY</p>
            <h2>Let's get you back in.</h2>
            <p>Enter the email on your account and we'll send a secure link to set a new password.</p>
            <ul class="auth-benefits">
                <li>Link expires after 60 minutes</li>
                <li>No password is shared over email</li>
                <li>Your cart and orders stay untouched</li>
            </ul>
        </aside>

        <section class="summary-card auth-card">
            <a href="{{ route('login') }}" class="auth-home-link">Back to log in</a>

            <h1 class="section-title">Forgot Your Password?</h1>
            <p class="section-subtitle auth-subtitle">We'll email you a link to reset it.</p>

            @if (session('status'))
                <div class="form-status success auth-alert" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="form-status error auth-alert" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="checkout-form auth-form">
                @csrf

                <label class="form-field" for="email">
                    <span>Email</span>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                    @error('email')
                        <small class="field-note">{{ $message }}</small>
                    @enderror
                </label>

                <button type="submit" class="primary-btn wide">Send Reset Link</button>
            </form>

            <p class="auth-footer-text">
                Remembered it after all?
                <a href="{{ route('login') }}" class="auth-inline-link">Log in</a>
            </p>
        </section>
    </div>
</div>
</body>
</html>
