<footer class="site-footer">
    <div class="footer-grid">
        <div class="footer-brand">
            <span class="brand-mark">TM</span>
            <div>
                <p class="brand-title">THRIFTMARKET</p>
                <p class="brand-sub">One-off thrift finds, honestly described.</p>
            </div>
        </div>

        <div class="footer-col">
            <p class="footer-heading">Shop</p>
            <a href="{{ route('storefront.home') }}#catalog">All Products</a>
            <a href="{{ route('storefront.home') }}#categories">Categories</a>
            <a href="{{ route('storefront.home') }}#newDrops">New Drops</a>
        </div>

        <div class="footer-col">
            <p class="footer-heading">Orders</p>
            <a href="{{ route('storefront.cart') }}">Cart</a>
            <a href="{{ route('storefront.track') }}">Track Order</a>
        </div>

        <div class="footer-col">
            <p class="footer-heading">Good to know</p>
            <p class="footer-text">Every piece is checked for condition before listing. Payments are verified by hand, so orders are usually confirmed within a few hours.</p>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} ThriftMarket. Every item is one-of-a-kind — once it's gone, it's gone.</p>
        <div class="footer-badges">
            <span class="footer-badge">Secure Checkout</span>
            <span class="footer-badge">Manually Verified Payments</span>
        </div>
    </div>
</footer>
