<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Account | ThriftMarket</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="storefront-body">
<div class="storefront-shell auth-shell">
    <div class="auth-layout">
        <aside class="auth-brand-panel" aria-label="Account perks">
            <p class="auth-kicker">GET STARTED</p>
            <h2>Create your account in less than a minute.</h2>
            <p>Join ThriftMarket to shop drop releases and complete checkout without extra steps.</p>
            <ul class="auth-benefits">
                <li>Quick checkout with saved profile</li>
                <li>Order tracking with your account email</li>
                <li>Simple account access on mobile and desktop</li>
            </ul>
        </aside>

        <section class="summary-card auth-card">
            <a href="{{ route('storefront.home') }}" class="auth-home-link">Back to shop</a>

            <div class="auth-switch" role="tablist" aria-label="Authentication options">
                <a class="auth-tab" href="{{ route('login', ['redirect' => $redirect ?? request('redirect', '')]) }}" role="tab" aria-selected="false">Log In</a>
                <a class="auth-tab active" href="{{ route('register', ['redirect' => $redirect ?? request('redirect', '')]) }}" role="tab" aria-selected="true">Create Account</a>
            </div>

            <h1 class="section-title">Create Your Account</h1>
            <p class="section-subtitle auth-subtitle">Save time at checkout and manage orders with one login.</p>

            @if ($errors->any())
                <div class="form-status error auth-alert" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register.store') }}" class="checkout-form auth-form">
                @csrf
                <input type="hidden" name="redirect" value="{{ old('redirect', $redirect ?? '') }}">

                <label class="form-field" for="name">
                    <span>Full Name</span>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="255" required autofocus>
                    @error('name')
                        <small class="field-note">{{ $message }}</small>
                    @enderror
                </label>

                <label class="form-field" for="email">
                    <span>Email</span>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required>
                    @error('email')
                        <small class="field-note">{{ $message }}</small>
                    @enderror
                </label>

                <label class="form-field" for="password">
                    <span>Password</span>
                    <span class="password-field">
                        <input id="password" name="password" type="password" autocomplete="new-password" required>
                        <button type="button" class="password-toggle" data-toggle-password="#password" data-label-show="Show" data-label-hide="Hide" aria-label="Toggle password visibility">Show</button>
                    </span>
                    <small class="field-note">Use at least 8 characters.</small>
                    @error('password')
                        <small class="field-note">{{ $message }}</small>
                    @enderror
                </label>

                <label class="form-field" for="password_confirmation">
                    <span>Confirm Password</span>
                    <span class="password-field">
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                        <button type="button" class="password-toggle" data-toggle-password="#password_confirmation" data-label-show="Show" data-label-hide="Hide" aria-label="Toggle confirm password visibility">Show</button>
                    </span>
                    @error('password_confirmation')
                        <small class="field-note">{{ $message }}</small>
                    @enderror
                </label>

                <button type="submit" class="primary-btn wide">Create Account</button>
            </form>

            <p class="auth-footer-text">
                Already have an account?
                <a href="{{ route('login', ['redirect' => $redirect ?? request('redirect', '')]) }}" class="auth-inline-link">Log in</a>
            </p>
        </section>
    </div>
</div>
</body>
</html>
