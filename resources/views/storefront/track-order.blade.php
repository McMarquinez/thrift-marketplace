<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Track Order - ThriftMarket</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="storefront-body">
<div id="trackOrderPage" class="storefront-shell">
    @include('storefront.partials.nav')

    <section class="single-panel-wrap">
        <article class="single-panel">
            <p class="section-eyebrow">ORDER TRACKING</p>
            <h1 class="hero-title">Track your order status</h1>
            <p class="section-subtitle">Enter your order number and email used during checkout.</p>

            <form id="trackOrderForm" class="checkout-form">
                <label class="form-field" for="trackOrderNumber">
                    <span>Order Number</span>
                    <input id="trackOrderNumber" name="order_number" type="text" maxlength="255" required>
                </label>

                <label class="form-field" for="trackEmail">
                    <span>Email</span>
                    <input id="trackEmail" name="email" type="email" maxlength="255" required>
                </label>

                <div id="trackStatus" class="form-status hidden" role="status" aria-live="polite"></div>

                <button id="trackSubmitButton" type="submit" class="primary-btn wide">Track Order</button>
            </form>

            <div id="trackResult" class="state-card hidden"></div>
        </article>
    </section>

    @include('storefront.partials.footer')
</div>
</body>
</html>
