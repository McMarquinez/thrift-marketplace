<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ThriftMarket Cart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="storefront-body">
<div id="cartPage" class="storefront-shell">
    @include('storefront.partials.nav')

    <div class="cart-heading">
        <h1 class="section-title">Your Cart</h1>
        <p class="section-subtitle">Review your picks, then check out in a couple of steps.</p>
    </div>

    <section class="cart-layout">
        <div class="cart-card">
            <div class="cart-card-head">
                <h2 class="cart-card-title">Items</h2>
                <span id="cartItemCountLabel" class="cart-item-count-label"></span>
            </div>
            <div id="cartEmptyState" class="state-card state-card-notice hidden">
                <span>Your cart is empty. Your next favorite find is waiting in the shop.</span>
                <a href="{{ route('storefront.home') }}#catalog" class="ghost-btn">Browse Products</a>
            </div>
            <div id="cartItems" class="cart-items"></div>
        </div>

        <aside class="summary-card summary-card-sticky">
            <h2>Order Summary</h2>
            <div class="summary-line"><span>Items</span><strong id="summaryItems">0</strong></div>
            <div class="summary-line"><span>Subtotal</span><strong id="summarySubtotal">PHP 0.00</strong></div>
            <div class="summary-line summary-line-total"><span>Total</span><strong id="summaryTotal">PHP 0.00</strong></div>
            <p class="summary-note">Checkout uses manual GCash payment. Admin verifies payment before shipping — no card details needed.</p>

            <details class="payment-instructions-details" open>
                <summary>
                    <strong>How to pay via GCash</strong>
                </summary>
                <div class="state-card payment-instructions">
                    <p>Send to Number: <strong>{{ $gcashNumber }}</strong></p>
                    <p>Account Name: <strong>{{ $gcashAccountName }}</strong></p>
                    @if(!empty($gcashQrUrl))
                        <img src="{{ $gcashQrUrl }}" alt="GCash QR" class="payment-qr">
                    @else
                        <p class="payment-qr-note">QR not uploaded yet. Please use the number above.</p>
                    @endif
                </div>
            </details>

            <form id="checkoutForm" class="checkout-form">
                <p class="form-section-label">Your details</p>

                <label class="form-field" for="checkoutName">
                    <span>Full Name</span>
                    <input id="checkoutName" name="customer_name" type="text" maxlength="255" autocomplete="name" required>
                </label>

                <label class="form-field" for="checkoutEmail">
                    <span>Email</span>
                    <input id="checkoutEmail" name="customer_email" type="email" maxlength="255" autocomplete="email" required>
                </label>

                <label class="form-field" for="checkoutPhone">
                    <span>Phone (Optional)</span>
                    <input id="checkoutPhone" name="customer_phone" type="text" maxlength="50" autocomplete="tel">
                </label>

                <label class="form-field" for="checkoutAddress">
                    <span>Shipping Address</span>
                    <textarea id="checkoutAddress" name="shipping_address" rows="3" maxlength="2000" autocomplete="street-address" required></textarea>
                </label>

                <p class="form-section-label">Payment</p>

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
                <p class="checkout-reassurance">You'll get an order confirmation and tracking link right after this step.</p>
            </form>

            <button id="clearCartButton" type="button" class="ghost-btn wide">Clear Cart</button>
        </aside>
    </section>

    @include('storefront.partials.footer')
</div>
</body>
</html>
