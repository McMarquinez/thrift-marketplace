<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ThriftMarket Cart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="storefront-body">
<div id="cartPage" class="storefront-shell">
    <header class="site-navbar">
        <a href="{{ route('storefront.home') }}" class="brand-block">
            <span class="brand-mark">TM</span>
            <div>
                <p class="brand-title">THRIFTMARKET</p>
                <p class="brand-sub">Curated thrift discovery</p>
            </div>
        </a>
        <nav class="navbar-links" aria-label="Main navigation">
            <a href="{{ route('storefront.home') }}#catalog">Shop</a>
            <a href="{{ route('storefront.home') }}#newDrops">New Drops</a>
            <a href="{{ route('storefront.home') }}#about">About</a>
        </nav>
        <a class="cart-link" href="{{ route('storefront.cart') }}">Cart <span id="cartCount" class="cart-count">0</span></a>
    </header>

    <section class="cart-layout">
        <div class="cart-card">
            <h1 class="section-title">Your Cart</h1>
            <p class="section-subtitle">Review your picks before checkout.</p>
            <div id="cartEmptyState" class="state-card hidden">Your cart is empty. Discover products in the shop.</div>
            <div id="cartItems" class="cart-items"></div>
        </div>

        <aside class="summary-card">
            <h2>Order Summary</h2>
            <div class="summary-line"><span>Items</span><strong id="summaryItems">0</strong></div>
            <div class="summary-line"><span>Subtotal</span><strong id="summarySubtotal">PHP 0.00</strong></div>
            <p class="summary-note">Shipping and final total are confirmed at checkout.</p>
            <button id="checkoutButton" type="button" class="primary-btn" disabled>Checkout (Next Phase)</button>
            <button id="clearCartButton" type="button" class="ghost-btn wide">Clear Cart</button>
        </aside>
    </section>
</div>
</body>
</html>
