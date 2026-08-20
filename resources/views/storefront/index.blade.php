<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ThriftMarket Storefront</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="storefront-body">
<a href="#catalog" class="skip-link">Skip to product listing</a>
<div
    id="storefrontApp"
    class="storefront-shell"
    data-authenticated="{{ auth()->check() ? '1' : '0' }}"
    data-login-url="{{ route('login') }}"
>
    @include('storefront.partials.nav')

    <section id="newDrops" class="new-week">
        <div class="hero-copy-wrap">
            <p class="section-eyebrow">NEW THIS WEEK</p>
            <h1 class="hero-title">Fresh finds. One-off pieces.<br>No restocks. Just good finds.</h1>
            <p class="hero-support">Every item is checked for condition and photographed as-is — what you see is exactly what arrives.</p>
            <div class="hero-actions">
                <a class="shop-cta" href="#catalog">SHOP NEW DROPS</a>
                <a class="shop-cta-secondary" href="#categories">Browse categories</a>
            </div>
            <ul class="hero-trust-row">
                <li>Honest condition notes</li>
                <li>Secure checkout</li>
                <li>Payments verified by hand</li>
            </ul>
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
                    <span class="input-with-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        <input id="searchInput" type="text" placeholder="Search by name, style, or SKU">
                    </span>
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

        <div id="errorState" class="state-card state-card-notice hidden">
            <span>Unable to load products right now.</span>
            <button id="retryLoadButton" type="button" class="ghost-btn">Try Again</button>
        </div>
        <div id="emptyState" class="state-card state-card-notice hidden">
            <span>No products match your filters.</span>
            <button id="resetFiltersButton" type="button" class="ghost-btn">Reset Filters</button>
        </div>
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

    @include('storefront.partials.footer')
</div>
</body>
</html>
