import './bootstrap';

const formatMoney = (value) => {
    const amount = Number(value || 0);
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(amount);
};

const getCartStorage = () => {
    try {
        const value = localStorage.getItem('tm_cart');
        return value ? JSON.parse(value) : [];
    } catch {
        return [];
    }
};

const saveCartStorage = (cart) => {
    localStorage.setItem('tm_cart', JSON.stringify(cart));
};

const getImagePath = (product) => {
    const rawPath = product?.primary_image?.path || product?.images?.[0]?.path || '';
    if (!rawPath) return '';
    if (rawPath.startsWith('http://') || rawPath.startsWith('https://') || rawPath.startsWith('/')) {
        return rawPath;
    }
    return `/storage/${rawPath}`;
};

const storefrontApp = document.getElementById('storefrontApp');
const cartPage = document.getElementById('cartPage');
const confirmationPage = document.getElementById('confirmationPage');
const trackOrderPage = document.getElementById('trackOrderPage');

if (storefrontApp) {
    const state = {
        products: [],
        categories: [],
        cart: getCartStorage(),
        loading: true,
        error: false,
        query: {
            search: '',
            categoryId: '',
            sort: 'latest',
            availableOnly: false,
            page: 1,
            perPage: 24,
        },
        pagination: {
            currentPage: 1,
            lastPage: 1,
            from: 0,
            to: 0,
            total: 0,
        },
        featured: [],
    };

    const refs = {
        cartCount: document.getElementById('cartCount'),
        loadingState: document.getElementById('loadingState'),
        errorState: document.getElementById('errorState'),
        emptyState: document.getElementById('emptyState'),
        productGrid: document.getElementById('productGrid'),
        categoryGrid: document.getElementById('categoryGrid'),
        categorySelect: document.getElementById('categorySelect'),
        searchInput: document.getElementById('searchInput'),
        sortSelect: document.getElementById('sortSelect'),
        availabilityToggle: document.getElementById('availabilityToggle'),
        resultsMeta: document.getElementById('resultsMeta'),
        loadMoreButton: document.getElementById('loadMoreButton'),
        editorialLarge: document.getElementById('editorialLarge'),
        editorialSmallA: document.getElementById('editorialSmallA'),
        editorialSmallB: document.getElementById('editorialSmallB'),
    };

    let searchDebounce;

    const setLoading = (isLoading) => {
        state.loading = isLoading;
        refs.loadingState.classList.toggle('hidden', !isLoading);
        refs.loadMoreButton.disabled = isLoading;
    };

    const setError = (isError) => {
        state.error = isError;
        refs.errorState.classList.toggle('hidden', !isError);
    };

    const updateCartCount = () => {
        const totalItems = state.cart.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
        refs.cartCount.textContent = String(totalItems);
    };

    const buildQueryString = () => {
        const params = new URLSearchParams();
        params.set('sort', state.query.sort);
        params.set('page', String(state.query.page));
        params.set('per_page', String(state.query.perPage));

        if (state.query.search) params.set('search', state.query.search);
        if (state.query.categoryId) params.set('category_id', state.query.categoryId);
        if (state.query.availableOnly) params.set('available_only', '1');

        return params.toString();
    };

    const productCard = (product) => {
        const availableQty = Number(product.available_quantity || 0);
        const stockClass = availableQty > 0 ? 'stock-ok' : 'stock-low';
        const stockText = availableQty > 0 ? `${availableQty} left` : 'Out of stock';
        const imagePath = getImagePath(product);

        return `
            <article class="discovery-card">
                <div class="discovery-image" style="${imagePath ? `background-image:url('${imagePath}');background-size:cover;background-position:center;` : ''}">
                    <span class="sku-tag">${product.sku || 'SKU'}</span>
                </div>
                <div class="discovery-content">
                    <span class="discovery-price">${formatMoney(product.price)}</span>
                    <h3 class="discovery-name">${product.name}</h3>
                    <p class="discovery-meta">${product.category?.name || 'Uncategorized'} · ${product.brand?.name || 'No brand'}</p>
                    <div class="discovery-actions">
                        <span class="stock-note ${stockClass}">${stockText}</span>
                        <button class="add-cart-btn" type="button" data-add-to-cart="${product.id}" ${availableQty > 0 ? '' : 'disabled'}>
                            Add
                        </button>
                    </div>
                </div>
            </article>
        `;
    };

    const editorialCardMarkup = (product, label) => {
        const stock = Number(product.available_quantity || 0);
        return `
            <div class="editorial-info">
                <span class="editorial-label">${label}</span>
                <h3 class="editorial-name">${product.name}</h3>
                <span class="editorial-meta">
                    <span>${formatMoney(product.price)}</span>
                    <span>${stock > 0 ? `${stock} left` : 'Out of stock'}</span>
                </span>
            </div>
        `;
    };

    const renderEditorialPicks = () => {
        const slots = [refs.editorialLarge, refs.editorialSmallA, refs.editorialSmallB];
        const picks = state.featured.slice(0, 3);

        slots.forEach((slot, index) => {
            if (!slot) return;
            const pick = picks[index];

            if (!pick) {
                slot.style.backgroundImage = '';
                slot.innerHTML = '<div class="editorial-empty">New picks landing soon.</div>';
                return;
            }

            const imagePath = getImagePath(pick);
            if (imagePath) {
                slot.style.backgroundImage = `url('${imagePath}')`;
                slot.style.backgroundSize = 'cover';
                slot.style.backgroundPosition = 'center';
            } else {
                slot.style.backgroundImage = '';
            }

            slot.innerHTML = editorialCardMarkup(pick, index === 0 ? 'Editorial Pick' : 'Just Added');
        });
    };

    const renderProducts = () => {
        const hasItems = state.products.length > 0;
        refs.emptyState.classList.toggle('hidden', hasItems || state.loading || state.error);
        refs.productGrid.innerHTML = hasItems ? state.products.map(productCard).join('') : '';

        const p = state.pagination;
        refs.resultsMeta.textContent = `Showing ${p.from}-${p.to} of ${p.total} products`;
        refs.loadMoreButton.classList.toggle('hidden', p.currentPage >= p.lastPage || p.total === 0);
    };

    const renderCategories = () => {
        refs.categorySelect.innerHTML = [
            '<option value="">All categories</option>',
            ...state.categories.map((category) => `<option value="${category.id}">${category.name}</option>`),
        ].join('');

        refs.categoryGrid.innerHTML = state.categories
            .map((category) => {
                const activeClass = String(state.query.categoryId) === String(category.id) ? 'active' : '';
                return `<button type="button" class="category-chip ${activeClass}" data-category-id="${category.id}">${category.name}</button>`;
            })
            .join('');
    };

    const addToCart = (productId) => {
        const product = state.products.find((item) => Number(item.id) === Number(productId));
        if (!product) return;

        const available = Number(product.available_quantity || 0);
        if (available <= 0) return;

        const existing = state.cart.find((item) => Number(item.product_id) === Number(product.id));
        if (existing) {
            if (existing.quantity < available) {
                existing.quantity += 1;
            }
        } else {
            state.cart.push({
                product_id: Number(product.id),
                name: product.name,
                price: Number(product.price || 0),
                quantity: 1,
                available_quantity: available,
            });
        }

        saveCartStorage(state.cart);
        updateCartCount();
    };

    const loadProducts = async ({ append = false } = {}) => {
        setLoading(true);
        setError(false);

        try {
            const response = await fetch(`/api/products?${buildQueryString()}`);
            if (!response.ok) throw new Error('Products request failed');

            const json = await response.json();
            const items = Array.isArray(json.data) ? json.data : [];
            const meta = json.meta || {};

            state.products = append ? [...state.products, ...items] : items;
            state.pagination = {
                currentPage: Number(meta.current_page || 1),
                lastPage: Number(meta.last_page || 1),
                from: Number(meta.from || 0),
                to: Number(meta.to || 0),
                total: Number(meta.total || 0),
            };
        } catch {
            if (!append) state.products = [];
            setError(true);
        } finally {
            setLoading(false);
            renderProducts();
        }
    };

    const loadFeatured = async () => {
        try {
            const response = await fetch('/api/products?sort=latest&per_page=3');
            if (!response.ok) throw new Error('Featured request failed');

            const json = await response.json();
            state.featured = Array.isArray(json.data) ? json.data : [];
            renderEditorialPicks();
        } catch {
            state.featured = [];
            renderEditorialPicks();
        }
    };

    const loadCategories = async () => {
        const response = await fetch('/api/categories');
        if (!response.ok) throw new Error('Categories request failed');
        const json = await response.json();
        state.categories = json.data || [];
        renderCategories();
    };

    const resetAndLoadProducts = () => {
        state.query.page = 1;
        loadProducts({ append: false });
    };

    refs.searchInput.addEventListener('input', (event) => {
        state.query.search = event.target.value.trim();
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(resetAndLoadProducts, 300);
    });

    refs.categorySelect.addEventListener('change', (event) => {
        state.query.categoryId = event.target.value;
        renderCategories();
        resetAndLoadProducts();
    });

    refs.sortSelect.addEventListener('change', (event) => {
        state.query.sort = event.target.value;
        resetAndLoadProducts();
    });

    refs.availabilityToggle.addEventListener('change', (event) => {
        state.query.availableOnly = event.target.checked;
        resetAndLoadProducts();
    });

    refs.categoryGrid.addEventListener('click', (event) => {
        const button = event.target.closest('[data-category-id]');
        if (!button) return;

        state.query.categoryId = button.getAttribute('data-category-id') || '';
        refs.categorySelect.value = state.query.categoryId;
        renderCategories();
        resetAndLoadProducts();
    });

    refs.loadMoreButton.addEventListener('click', () => {
        if (state.pagination.currentPage >= state.pagination.lastPage) return;
        state.query.page = state.pagination.currentPage + 1;
        loadProducts({ append: true });
    });

    refs.productGrid.addEventListener('click', (event) => {
        const button = event.target.closest('[data-add-to-cart]');
        if (!button) return;
        addToCart(Number(button.getAttribute('data-add-to-cart')));
    });

    (async () => {
        updateCartCount();

        try {
            await Promise.all([loadCategories(), loadFeatured()]);
            await loadProducts();
        } catch {
            setError(true);
        }
    })();
}

if (cartPage) {
    const state = {
        cart: getCartStorage(),
        submitting: false,
    };

    const refs = {
        cartCount: document.getElementById('cartCount'),
        cartItems: document.getElementById('cartItems'),
        cartEmptyState: document.getElementById('cartEmptyState'),
        summaryItems: document.getElementById('summaryItems'),
        summarySubtotal: document.getElementById('summarySubtotal'),
        checkoutForm: document.getElementById('checkoutForm'),
        checkoutStatus: document.getElementById('checkoutStatus'),
        checkoutName: document.getElementById('checkoutName'),
        checkoutEmail: document.getElementById('checkoutEmail'),
        checkoutPhone: document.getElementById('checkoutPhone'),
        checkoutAddress: document.getElementById('checkoutAddress'),
        checkoutPaymentMethod: document.getElementById('checkoutPaymentMethod'),
        checkoutReference: document.getElementById('checkoutReference'),
        checkoutNotes: document.getElementById('checkoutNotes'),
        checkoutButton: document.getElementById('checkoutButton'),
        clearCartButton: document.getElementById('clearCartButton'),
    };

    const setCheckoutStatus = (message, type) => {
        refs.checkoutStatus.textContent = message;
        refs.checkoutStatus.classList.remove('hidden', 'success', 'error');
        refs.checkoutStatus.classList.add(type);
    };

    const clearCheckoutStatus = () => {
        refs.checkoutStatus.textContent = '';
        refs.checkoutStatus.classList.add('hidden');
        refs.checkoutStatus.classList.remove('success', 'error');
    };

    const setSubmitting = (isSubmitting) => {
        state.submitting = isSubmitting;
        refs.checkoutButton.disabled = isSubmitting || state.cart.length === 0;
        refs.checkoutButton.textContent = isSubmitting ? 'Placing Order...' : 'Place Order';
    };

    const updateHeaderCount = () => {
        const count = state.cart.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
        refs.cartCount.textContent = String(count);
    };

    const renderCart = () => {
        const totalItems = state.cart.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
        const subtotal = state.cart.reduce((sum, item) => sum + Number(item.quantity || 0) * Number(item.price || 0), 0);

        refs.summaryItems.textContent = String(totalItems);
        refs.summarySubtotal.textContent = formatMoney(subtotal);
        refs.checkoutButton.disabled = totalItems === 0 || state.submitting;

        refs.cartEmptyState.classList.toggle('hidden', totalItems > 0);

        if (totalItems === 0) {
            refs.cartItems.innerHTML = '';
            updateHeaderCount();
            return;
        }

        refs.cartItems.innerHTML = state.cart
            .map((item) => `
                <article class="cart-item">
                    <div>
                        <h3 class="cart-item-name">${item.name}</h3>
                        <p class="cart-item-meta">${formatMoney(item.price)} each</p>
                        <div class="cart-item-controls">
                            <button class="qty-btn" type="button" data-qty="decrease" data-id="${item.product_id}">-</button>
                            <span class="qty-label">${item.quantity}</span>
                            <button class="qty-btn" type="button" data-qty="increase" data-id="${item.product_id}">+</button>
                            <button class="remove-btn" type="button" data-remove-id="${item.product_id}">Remove</button>
                        </div>
                    </div>
                    <strong>${formatMoney(Number(item.price) * Number(item.quantity))}</strong>
                </article>
            `)
            .join('');

        updateHeaderCount();
    };

    const persist = () => {
        saveCartStorage(state.cart);
        renderCart();
    };

    const changeQuantity = (productId, action) => {
        const item = state.cart.find((row) => Number(row.product_id) === Number(productId));
        if (!item) return;

        if (action === 'increase') {
            const maxAvailable = Number(item.available_quantity || 999999);
            item.quantity = Math.min(maxAvailable, Number(item.quantity) + 1);
        }

        if (action === 'decrease') {
            item.quantity = Number(item.quantity) - 1;
            if (item.quantity <= 0) {
                state.cart = state.cart.filter((row) => Number(row.product_id) !== Number(productId));
            }
        }

        persist();
    };

    refs.cartItems.addEventListener('click', (event) => {
        const qtyButton = event.target.closest('[data-qty]');
        if (qtyButton) {
            changeQuantity(Number(qtyButton.getAttribute('data-id')), qtyButton.getAttribute('data-qty'));
            return;
        }

        const removeButton = event.target.closest('[data-remove-id]');
        if (removeButton) {
            const id = Number(removeButton.getAttribute('data-remove-id'));
            state.cart = state.cart.filter((row) => Number(row.product_id) !== id);
            persist();
        }
    });

    refs.clearCartButton.addEventListener('click', () => {
        state.cart = [];
        clearCheckoutStatus();
        persist();
    });

    refs.checkoutForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (state.cart.length === 0 || state.submitting) {
            return;
        }

        const mergedNotes = [
            refs.checkoutNotes.value.trim(),
            refs.checkoutReference.value.trim() ? `GCash Ref: ${refs.checkoutReference.value.trim()}` : '',
        ].filter(Boolean).join(' | ');

        const payload = {
            customer_name: refs.checkoutName.value.trim(),
            customer_email: refs.checkoutEmail.value.trim(),
            customer_phone: refs.checkoutPhone.value.trim() || null,
            shipping_address: refs.checkoutAddress.value.trim(),
            payment_method: refs.checkoutPaymentMethod.value,
            notes: mergedNotes || null,
            items: state.cart.map((item) => ({
                product_id: Number(item.product_id),
                quantity: Number(item.quantity),
            })),
        };

        clearCheckoutStatus();
        setSubmitting(true);

        try {
            const response = await fetch('/api/orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const json = await response.json();

            if (!response.ok) {
                const validationErrors = json.errors
                    ? Object.values(json.errors).flat().join(' ')
                    : '';
                const fallbackError = json.error || json.message || 'Unable to place order right now.';
                setCheckoutStatus(validationErrors || fallbackError, 'error');
                return;
            }

            const orderNumber = json?.data?.order_number || 'N/A';
            const total = json?.data?.pricing?.total;
            const successMessage = total !== undefined
                ? `Order ${orderNumber} placed successfully. Total: ${formatMoney(total)}.`
                : `Order ${orderNumber} placed successfully.`;

            setCheckoutStatus(successMessage, 'success');

            const emailParam = encodeURIComponent(payload.customer_email);
            const orderParam = encodeURIComponent(orderNumber);

            state.cart = [];
            persist();
            refs.checkoutForm.reset();

            window.location.href = `/order-confirmation?order_number=${orderParam}&email=${emailParam}`;
        } catch {
            setCheckoutStatus('Network error while placing order. Please try again.', 'error');
        } finally {
            setSubmitting(false);
        }
    });

    renderCart();
}

if (confirmationPage) {
    const cartCount = document.getElementById('cartCount');
    const confirmationMeta = document.getElementById('confirmationMeta');
    const confirmationSummary = document.getElementById('confirmationSummary');
    const trackOrderButton = document.getElementById('trackOrderButton');

    const cart = getCartStorage();
    const count = cart.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
    cartCount.textContent = String(count);

    const params = new URLSearchParams(window.location.search);
    const orderNumber = params.get('order_number') || '';
    const email = params.get('email') || '';

    if (!orderNumber || !email) {
        confirmationMeta.textContent = 'Order details are missing. You can still track your order anytime.';
        confirmationSummary.textContent = 'Use the Track Order page with your order number and checkout email.';
    } else {
        confirmationMeta.textContent = `Order ${orderNumber} is now pending payment confirmation.`;
        confirmationSummary.textContent = `Saved to order email: ${email}`;
        trackOrderButton.href = `/track-order?order_number=${encodeURIComponent(orderNumber)}&email=${encodeURIComponent(email)}`;
    }
}

if (trackOrderPage) {
    const cartCount = document.getElementById('cartCount');
    const trackOrderForm = document.getElementById('trackOrderForm');
    const trackOrderNumber = document.getElementById('trackOrderNumber');
    const trackEmail = document.getElementById('trackEmail');
    const trackStatus = document.getElementById('trackStatus');
    const trackResult = document.getElementById('trackResult');
    const trackSubmitButton = document.getElementById('trackSubmitButton');

    const cart = getCartStorage();
    const count = cart.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
    cartCount.textContent = String(count);

    const setTrackStatus = (message, type) => {
        trackStatus.textContent = message;
        trackStatus.classList.remove('hidden', 'success', 'error');
        trackStatus.classList.add(type);
    };

    const clearTrackStatus = () => {
        trackStatus.textContent = '';
        trackStatus.classList.add('hidden');
        trackStatus.classList.remove('success', 'error');
    };

    const renderTracking = (data) => {
        const createdAt = data.created_at ? new Date(data.created_at).toLocaleString() : 'N/A';
        const paidAt = data.paid_at ? new Date(data.paid_at).toLocaleString() : 'Not yet paid';

        trackResult.innerHTML = `
            <div class="track-result-grid">
                <p><strong>Order Number:</strong> ${data.order_number || 'N/A'}</p>
                <p><strong>Order Status:</strong> ${data.status || 'N/A'}</p>
                <p><strong>Payment Status:</strong> ${data.payment_status || 'N/A'}</p>
                <p><strong>Shipping Status:</strong> ${data.shipping_status || 'N/A'}</p>
                <p><strong>Total:</strong> ${formatMoney(data.pricing?.total)}</p>
                <p><strong>Created:</strong> ${createdAt}</p>
                <p><strong>Paid:</strong> ${paidAt}</p>
            </div>
        `;
        trackResult.classList.remove('hidden');
    };

    const params = new URLSearchParams(window.location.search);
    const presetOrder = params.get('order_number');
    const presetEmail = params.get('email');
    if (presetOrder) trackOrderNumber.value = presetOrder;
    if (presetEmail) trackEmail.value = presetEmail;

    trackOrderForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearTrackStatus();
        trackResult.classList.add('hidden');

        trackSubmitButton.disabled = true;
        trackSubmitButton.textContent = 'Checking...';

        try {
            const response = await fetch('/api/orders/track', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    order_number: trackOrderNumber.value.trim(),
                    email: trackEmail.value.trim(),
                }),
            });

            const json = await response.json();

            if (!response.ok) {
                const message = json.message || 'Order not found. Please verify details.';
                setTrackStatus(message, 'error');
                return;
            }

            setTrackStatus('Order found. Status updated below.', 'success');
            renderTracking(json.data || {});
        } catch {
            setTrackStatus('Network error while tracking order. Please try again.', 'error');
        } finally {
            trackSubmitButton.disabled = false;
            trackSubmitButton.textContent = 'Track Order';
        }
    });
}
