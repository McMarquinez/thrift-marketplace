<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password | ThriftMarket</title>
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
            <h2>Choose a new password.</h2>
            <p>Pick something you haven't used before on ThriftMarket. You'll be signed in with it right away next time.</p>
            <ul class="auth-benefits">
                <li>At least 8 characters</li>
                <li>Kept private, never emailed</li>
                <li>Takes effect immediately</li>
            </ul>
        </aside>

        <section class="summary-card auth-card">
            <a href="{{ route('login') }}" class="auth-home-link">Back to log in</a>

            <h1 class="section-title">Reset Your Password</h1>
            <p class="section-subtitle auth-subtitle">Enter a new password for {{ $email }}.</p>

            @if ($errors->any())
                <div class="form-status error auth-alert" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="checkout-form auth-form">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <label class="form-field" for="email">
                    <span>Email</span>
                    <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required autofocus>
                    @error('email')
                        <small class="field-note">{{ $message }}</small>
                    @enderror
                </label>

                <label class="form-field" for="password">
                    <span>New Password</span>
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
                    <span>Confirm New Password</span>
                    <span class="password-field">
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                        <button type="button" class="password-toggle" data-toggle-password="#password_confirmation" data-label-show="Show" data-label-hide="Hide" aria-label="Toggle confirm password visibility">Show</button>
                    </span>
                </label>

                <button type="submit" class="primary-btn wide">Reset Password</button>
            </form>
        </section>
    </div>
</div>
</body>
</html>
