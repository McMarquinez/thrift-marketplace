<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ThriftMarket Storefront</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="storefront-body">
<div id="storefrontApp" class="storefront-shell">
    <header class="store-header">
        <p class="store-title">THRIFTMARKET</p>
        <div class="divider"></div>
        <div class="store-nav-row">
            <nav class="store-nav" aria-label="Main links">
                <a href="#justIn">Shop</a>
                <a href="#newDrops">New Drops</a>
                <a href="#about">About</a>
            </nav>
            <a class="cart-link" href="#cartNotice">Cart <span id="cartCount" class="cart-count">0</span></a>
        </div>
    </header>

    <section id="newDrops" class="new-week">
        <p class="section-eyebrow">NEW THIS WEEK</p>
        <div class="editorial-frame">
            <div class="editorial-grid">
                <div class="editorial-block large"></div>
                <div class="editorial-block small"></div>
                <div class="editorial-block small"></div>
            </div>
        </div>
        <p class="editorial-copy">
            Fresh finds. One-off pieces.<br>
            No restocks. Just good finds.
        </p>
        <a class="shop-cta" href="#justIn">SHOP NEW DROPS</a>
    </section>

    <div class="divider"></div>

    <section id="justIn" class="just-in" aria-live="polite">
        <h2 class="section-heading">JUST IN</h2>
        <div id="loadingState" class="state-card">Loading fresh pieces...</div>
        <div id="errorState" class="state-card hidden">Unable to load products right now. Please refresh.</div>
        <div id="emptyState" class="state-card hidden">No products are available yet.</div>
        <div id="productGrid" class="discovery-grid"></div>
    </section>

    <div class="divider"></div>

    <section class="categories">
        <h2 class="section-heading">SHOP BY CATEGORY</h2>
        <div id="categoryGrid" class="category-grid"></div>
    </section>

    <div class="divider"></div>

    <section id="about" class="why-section">
        <h2 class="section-heading">WHY THRIFTMARKET</h2>
        <p>Curated finds</p>
        <p class="dot">•</p>
        <p>Honest condition</p>
        <p class="dot">•</p>
        <p>Secure checkout</p>
    </section>

    <p id="cartNotice" class="cart-notice">Cart preview is active. Full checkout flow comes next in Phase D2.</p>
</div>
</body>
</html>
