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
        <a href="{{ route('storefront.track') }}">Track Order</a>
    </nav>
    <div class="nav-actions">
        @auth
            <div class="nav-account">
                <span class="nav-account-label">Hi, {{ explode(' ', auth()->user()->name)[0] }}</span>
                <form method="POST" action="{{ route('logout') }}" class="nav-logout-form">
                    @csrf
                    <button type="submit" class="nav-logout-btn">Log Out</button>
                </form>
            </div>
        @else
            <a class="nav-login-link" href="{{ route('login') }}">Log In</a>
        @endauth

        <a class="cart-link" href="{{ route('storefront.cart') }}">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3 5h2l2.4 12.2a2 2 0 0 0 2 1.6h8.4a2 2 0 0 0 2-1.6L22 8H6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="10" cy="21" r="1.4" fill="currentColor"/><circle cx="18" cy="21" r="1.4" fill="currentColor"/></svg>
            <span>Cart</span>
            <span id="cartCount" class="cart-count">0</span>
        </a>
    </div>
</header>
