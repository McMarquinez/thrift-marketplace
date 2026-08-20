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
            <a href="{{ route('storefront.track') }}">Track Order</a>
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
            <p class="summary-note">Checkout is manual GCash payment. Admin confirms payment before shipping.</p>

            <div class="state-card payment-instructions">
                <p><strong>Send payment via GCash:</strong></p>
                <p>Number: <strong>{{ $gcashNumber }}</strong></p>
                <p>Account Name: <strong>{{ $gcashAccountName }}</strong></p>
                @if(!empty($gcashQrUrl))
                    <img src="{{ $gcashQrUrl }}" alt="GCash QR" class="payment-qr">
                @else
                    <p class="payment-qr-note">QR not uploaded yet. Please use the number above.</p>
                @endif
            </div>

            <form id="checkoutForm" class="checkout-form">
                <label class="form-field" for="checkoutName">
                    <span>Full Name</span>
                    <input id="checkoutName" name="customer_name" type="text" maxlength="255" required>
                </label>

                <label class="form-field" for="checkoutEmail">
                    <span>Email</span>
                    <input id="checkoutEmail" name="customer_email" type="email" maxlength="255" required>
                </label>

                <label class="form-field" for="checkoutPhone">
                    <span>Phone (Optional)</span>
                    <input id="checkoutPhone" name="customer_phone" type="text" maxlength="50">
                </label>

                <label class="form-field" for="checkoutAddress">
                    <span>Shipping Address</span>
                    <textarea id="checkoutAddress" name="shipping_address" rows="3" maxlength="2000" required></textarea>
                </label>

                <label class="form-field" for="checkoutPaymentMethod">
                    <span>Payment Method</span>
                    <input id="checkoutPaymentMethodLabel" type="text" value="GCash (Manual Verification)" disabled>
                    <input id="checkoutPaymentMethod" name="payment_method" type="hidden" value="gcash">
                </label>

                <label class="form-field" for="checkoutReference">
                    <span>GCash Reference Number (Optional)</span>
                    <input id="checkoutReference" name="gcash_reference" type="text" maxlength="255" placeholder="Example: 1234567890">
                </label>

                <label class="form-field" for="checkoutNotes">
                    <span>Notes (Optional)</span>
                    <textarea id="checkoutNotes" name="notes" rows="2" maxlength="2000"></textarea>
                </label>

                <div id="checkoutStatus" class="form-status hidden" role="status" aria-live="polite"></div>

                <button id="checkoutButton" type="submit" class="primary-btn wide" disabled>Place Order</button>
            </form>

            <button id="clearCartButton" type="button" class="ghost-btn wide">Clear Cart</button>
        </aside>
    </section>
</div>
</body>
</html>
