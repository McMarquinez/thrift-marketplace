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
    <header class="site-navbar">
        <a href="{{ route('storefront.home') }}" class="brand-block">
            <span class="brand-mark">TM</span>
            <div>
                <p class="brand-title">THRIFTMARKET</p>
                <p class="brand-sub">Curated thrift discovery</p>
            </div>
        </a>
        <nav class="navbar-links" aria-label="Main navigation">
            <a href="#catalog">Shop</a>
            <a href="#newDrops">New Drops</a>
            <a href="#about">About</a>
        </nav>
        <a class="cart-link" href="{{ route('storefront.cart') }}">Cart <span id="cartCount" class="cart-count">0</span></a>
    </header>

    <section id="newDrops" class="new-week">
        <div class="hero-copy-wrap">
            <p class="section-eyebrow">NEW THIS WEEK</p>
            <h1 class="hero-title">Fresh finds. One-off pieces.<br>No restocks. Just good finds.</h1>
            <a class="shop-cta" href="#catalog">SHOP NEW DROPS</a>
        </div>
        <div class="editorial-frame">
            <div class="editorial-grid">
                <article id="editorialLarge" class="editorial-block large"></article>
                <article id="editorialSmallA" class="editorial-block small"></article>
                <article id="editorialSmallB" class="editorial-block small"></article>
            </div>
        </div>
    </section>

    <section id="catalog" class="catalog-wrap" aria-live="polite">
        <div class="toolbar-card">
            <div class="toolbar-head">
                <h2 class="section-title">JUST IN</h2>
                <p id="resultsMeta" class="results-meta">Loading products...</p>
            </div>

            <div class="toolbar-grid">
                <label class="field-block field-search">
                    <span>Search</span>
                    <input id="searchInput" type="text" placeholder="Search by name, style, or SKU">
                </label>

                <label class="field-block">
                    <span>Category</span>
                    <select id="categorySelect">
                        <option value="">All</option>
                    </select>
                </label>

                <label class="field-block">
                    <span>Sort</span>
                    <select id="sortSelect">
                        <option value="latest">Newest</option>
                        <option value="price_asc">Price low-high</option>
                        <option value="price_desc">Price high-low</option>
                    </select>
                </label>

                <label class="stock-filter">
                    <input id="availabilityToggle" type="checkbox">
                    <span>In stock only</span>
                </label>
            </div>
        </div>

        <div id="loadingState" class="state-card">Loading fresh pieces...</div>
        <div id="errorState" class="state-card hidden">Unable to load products right now. Please refresh.</div>
        <div id="emptyState" class="state-card hidden">No products are available yet.</div>
        <div id="productGrid" class="discovery-grid"></div>

        <div class="catalog-footer">
            <button id="loadMoreButton" type="button" class="ghost-btn">Load More</button>
        </div>
    </section>

    <section class="categories" id="categories">
        <h2 class="section-title">SHOP BY CATEGORY</h2>
        <div id="categoryGrid" class="category-grid"></div>
    </section>

    <section id="about" class="why-section">
        <h2 class="section-title">WHY THRIFTMARKET</h2>
        <p>Curated finds</p>
        <p class="dot">•</p>
        <p>Honest condition</p>
        <p class="dot">•</p>
        <p>Secure checkout</p>
    </section>
</div>
</body>
</html>
