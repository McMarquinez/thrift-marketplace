<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Confirmation - ThriftMarket</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="storefront-body">
<div id="confirmationPage" class="storefront-shell">
    @include('storefront.partials.nav')

    <section class="single-panel-wrap">
        <article class="single-panel">
            <div class="confirmation-check" aria-hidden="true">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <p class="section-eyebrow">ORDER RECEIVED</p>
            <h1 class="hero-title">Thanks. Your order is in.</h1>
            <p id="confirmationMeta" class="section-subtitle">Your order is pending manual GCash verification.</p>

            <div class="state-card payment-instructions">
                <p><strong>Complete payment via GCash:</strong></p>
                <p>Number: <strong>{{ $gcashNumber }}</strong></p>
                <p>Account Name: <strong>{{ $gcashAccountName }}</strong></p>
                @if(!empty($gcashQrUrl))
                    <img src="{{ $gcashQrUrl }}" alt="GCash QR" class="payment-qr">
                @else
                    <p class="payment-qr-note">QR not uploaded yet. Please use the number above.</p>
                @endif
                <p class="summary-note">After payment, admin will verify and update your order status before shipping.</p>
            </div>

            <div id="confirmationSummary" class="state-card"></div>
            <div class="single-panel-actions">
                <a id="trackOrderButton" href="{{ route('storefront.track') }}" class="primary-link-btn">Track This Order</a>
                <a href="{{ route('storefront.home') }}#catalog" class="ghost-link-btn">Continue Shopping</a>
            </div>
        </article>
    </section>

    @include('storefront.partials.footer')
</div>
</body>
</html>
