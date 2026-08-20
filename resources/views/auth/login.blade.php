<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | ThriftMarket</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="storefront-body">
<div class="storefront-shell auth-shell">
    <div class="auth-layout">
        <aside class="auth-brand-panel" aria-label="Why create an account">
            <p class="auth-kicker">THRIFTMARKET ACCESS</p>
            <h2>Shop faster with your account.</h2>
            <p>Sign in to keep checkout smooth and secure every time you buy curated drops.</p>
            <ul class="auth-benefits">
                <li>Faster checkout details</li>
                <li>Easy access to order tracking</li>
                <li>Secure account session</li>
            </ul>
        </aside>

        <section class="summary-card auth-card">
            <a href="{{ route('storefront.home') }}" class="auth-home-link">Back to shop</a>

            <div class="auth-switch" role="tablist" aria-label="Authentication options">
                <a class="auth-tab active" href="{{ route('login', ['redirect' => $redirect ?? request('redirect', '')]) }}" role="tab" aria-selected="true">Log In</a>
                <a class="auth-tab" href="{{ route('register', ['redirect' => $redirect ?? request('redirect', '')]) }}" role="tab" aria-selected="false">Create Account</a>
            </div>

            <h1 class="section-title">Welcome Back</h1>
            <p class="section-subtitle auth-subtitle">Log in to add products to cart and complete checkout.</p>

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

            <form method="POST" action="{{ route('login.store') }}" class="checkout-form auth-form">
                @csrf
                <input type="hidden" name="redirect" value="{{ old('redirect', $redirect ?? '') }}">

                <label class="form-field" for="email">
                    <span>Email</span>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                    @error('email')
                        <small class="field-note">{{ $message }}</small>
                    @enderror
                </label>

                <label class="form-field" for="password">
                    <span class="form-field-label-row">
                        <span>Password</span>
                        <a href="{{ route('password.request') }}" class="auth-inline-link auth-forgot-link">Forgot password?</a>
                    </span>
                    <span class="password-field">
                        <input id="password" name="password" type="password" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" data-toggle-password="#password" data-label-show="Show" data-label-hide="Hide" aria-label="Toggle password visibility">Show</button>
                    </span>
                    @error('password')
                        <small class="field-note">{{ $message }}</small>
                    @enderror
                </label>

                <label class="stock-filter auth-remember">
                    <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    <span>Remember me on this device</span>
                </label>

                <button type="submit" class="primary-btn wide">Log In</button>
            </form>

            <p class="auth-footer-text">
                New to ThriftMarket?
                <a href="{{ route('register', ['redirect' => $redirect ?? request('redirect', '')]) }}" class="auth-inline-link">Create account</a>
            </p>
        </section>
    </div>
</div>
</body>
</html>
