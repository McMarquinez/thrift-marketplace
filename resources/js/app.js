import './bootstrap';

const appRoot = document.getElementById('storefrontApp');

if (appRoot) {
	const state = {
		products: [],
		categories: [],
		brands: [],
		search: '',
		category: '',
		brand: '',
		sort: 'latest',
		inStockOnly: false,
		page: 1,
		total: 0,
		from: 0,
		to: 0,
		lastPage: 1,
		loading: true,
		error: false,
		cart: [],
	};

	const refs = {
		searchInput: document.getElementById('searchInput'),
		categorySelect: document.getElementById('categorySelect'),
		brandSelect: document.getElementById('brandSelect'),
		sortSelect: document.getElementById('sortSelect'),
		availabilityToggle: document.getElementById('availabilityToggle'),
		resetFiltersButton: document.getElementById('resetFiltersButton'),
		resultsMeta: document.getElementById('resultsMeta'),
		loadingState: document.getElementById('loadingState'),
		errorState: document.getElementById('errorState'),
		emptyState: document.getElementById('emptyState'),
		productGrid: document.getElementById('productGrid'),
		prevPageButton: document.getElementById('prevPageButton'),
		nextPageButton: document.getElementById('nextPageButton'),
		pageLabel: document.getElementById('pageLabel'),
		quickCategoryChips: document.getElementById('quickCategoryChips'),
		totalProductsMetric: document.getElementById('totalProductsMetric'),
		liveCountMetric: document.getElementById('liveCountMetric'),
		cartCount: document.getElementById('cartCount'),
		cartTotalItems: document.getElementById('cartTotalItems'),
		cartTotalAmount: document.getElementById('cartTotalAmount'),
		cartList: document.getElementById('cartList'),
		clearCartButton: document.getElementById('clearCartButton'),
		cartAnchor: document.getElementById('cartAnchor'),
	};

	let searchDebounce;

	const formatMoney = (value) => {
		const amount = Number(value || 0);
		return new Intl.NumberFormat('en-PH', {
			style: 'currency',
			currency: 'PHP',
			minimumFractionDigits: 2,
		}).format(amount);
	};

	const setLoading = (isLoading) => {
		state.loading = isLoading;
		refs.loadingState.classList.toggle('hidden', !isLoading);
	};

	const setError = (isError) => {
		state.error = isError;
		refs.errorState.classList.toggle('hidden', !isError);
	};

	const getCartStorage = () => {
		try {
			const value = localStorage.getItem('tm_cart');
			return value ? JSON.parse(value) : [];
		} catch {
			return [];
		}
	};

	const saveCartStorage = () => {
		localStorage.setItem('tm_cart', JSON.stringify(state.cart));
	};

	const renderCart = () => {
		const totalItems = state.cart.reduce((sum, item) => sum + Number(item.quantity || 0), 0);
		const totalAmount = state.cart.reduce((sum, item) => sum + Number(item.quantity || 0) * Number(item.price || 0), 0);

		refs.cartCount.textContent = String(totalItems);
		refs.cartTotalItems.textContent = `${totalItems} item${totalItems === 1 ? '' : 's'}`;
		refs.cartTotalAmount.textContent = formatMoney(totalAmount);

		if (state.cart.length === 0) {
			refs.cartList.innerHTML = '<p class="cart-empty">No items yet. Add from product cards.</p>';
			return;
		}

		refs.cartList.innerHTML = state.cart
			.map((item) => `
				<article class="cart-item">
					<div>
						<h4>${item.name}</h4>
						<p>${item.quantity} x ${formatMoney(item.price)}</p>
					</div>
					<strong>${formatMoney(Number(item.quantity) * Number(item.price))}</strong>
				</article>
			`)
			.join('');
	};

	const addToCart = (productId) => {
		const product = state.products.find((item) => Number(item.id) === Number(productId));
		if (!product) return;

		const available = Number(product.available_quantity || 0);
		if (available <= 0) return;

		const found = state.cart.find((item) => Number(item.product_id) === Number(product.id));
		if (found) {
			if (found.quantity < available) {
				found.quantity += 1;
			}
		} else {
			state.cart.push({
				product_id: Number(product.id),
				name: product.name,
				price: Number(product.price || 0),
				quantity: 1,
			});
		}

		saveCartStorage();
		renderCart();
	};

	const updateMeta = () => {
		refs.resultsMeta.textContent = `Showing ${state.from}-${state.to} of ${state.total} items`;
		refs.pageLabel.textContent = `Page ${state.page} of ${state.lastPage}`;
		refs.prevPageButton.disabled = state.page <= 1;
		refs.nextPageButton.disabled = state.page >= state.lastPage;
		refs.totalProductsMetric.textContent = String(state.total);
		refs.liveCountMetric.textContent = String(state.products.length);
	};

	const productCard = (product) => {
		const category = product.category?.name || 'Uncategorized';
		const brand = product.brand?.name || 'Unbranded';
		const availableQty = Number(product.available_quantity || 0);
		const stockClass = availableQty > 0 ? 'stock-ok' : 'stock-low';
		const stockText = availableQty > 0 ? `${availableQty} available` : 'Out of stock';
		const description = product.short_description || product.description || 'No description yet.';
		const canAddToCart = availableQty > 0;

		return `
			<article class="product-card">
				<div class="product-media">
					<span class="sku-pill">${product.sku || 'SKU'}</span>
				</div>
				<div class="meta-line">
					<span class="pill">${category}</span>
					<span class="pill">${brand}</span>
					<span class="pill">${product.condition || 'n/a'}</span>
				</div>
				<h3>${product.name}</h3>
				<p class="product-desc">${description}</p>
				<div class="price-line">
					<span class="price">${formatMoney(product.price)}</span>
					<span class="${stockClass}">${stockText}</span>
				</div>
				<div class="product-actions">
					<button class="add-cart-btn" type="button" data-add-to-cart="${product.id}" ${canAddToCart ? '' : 'disabled'}>
						${canAddToCart ? 'Add to cart' : 'Unavailable'}
					</button>
				</div>
			</article>
		`;
	};

	const renderProducts = () => {
		const hasItems = state.products.length > 0;
		refs.emptyState.classList.toggle('hidden', hasItems || state.loading || state.error);
		refs.productGrid.innerHTML = hasItems ? state.products.map(productCard).join('') : '';
		updateMeta();
	};

	const toQuery = () => {
		const params = new URLSearchParams();
		if (state.search) params.set('search', state.search);
		if (state.category) params.set('category', state.category);
		if (state.brand) params.set('brand', state.brand);
		if (state.inStockOnly) params.set('available_only', '1');
		if (state.sort) params.set('sort', state.sort);
		params.set('page', String(state.page));
		return params.toString();
	};

	const loadProducts = async () => {
		setLoading(true);
		setError(false);

		try {
			const response = await fetch(`/api/products?${toQuery()}`);
			if (!response.ok) throw new Error('Products request failed');

			const json = await response.json();
			const payload = json.data || {};

			state.products = payload.data || [];
			state.total = Number(payload.total || 0);
			state.page = Number(payload.current_page || 1);
			state.lastPage = Number(payload.last_page || 1);
			state.from = Number(payload.from || 0);
			state.to = Number(payload.to || 0);
		} catch (error) {
			state.products = [];
			state.total = 0;
			state.from = 0;
			state.to = 0;
			setError(true);
		} finally {
			setLoading(false);
			renderProducts();
		}
	};

	const buildOptions = (selectEl, records) => {
		const isCategory = selectEl === refs.categorySelect;
		const firstOption = isCategory
			? '<option value="">All categories</option>'
			: '<option value="">All brands</option>';
		const options = records.map((record) => `<option value="${record.slug}">${record.name}</option>`).join('');
		selectEl.innerHTML = firstOption + options;
	};

	const renderQuickCategoryChips = () => {
		const chips = state.categories.slice(0, 8).map((category) => {
			const activeClass = state.category === category.slug ? 'active' : '';
			return `<button type="button" class="chip-btn ${activeClass}" data-category-chip="${category.slug}">${category.name}</button>`;
		});

		refs.quickCategoryChips.innerHTML = [
			`<button type="button" class="chip-btn ${state.category === '' ? 'active' : ''}" data-category-chip="">All</button>`,
			...chips,
		].join('');
	};

	const loadFilters = async () => {
		const [categoriesResponse, brandsResponse] = await Promise.all([
			fetch('/api/categories'),
			fetch('/api/brands'),
		]);

		const categoriesJson = await categoriesResponse.json();
		const brandsJson = await brandsResponse.json();

		state.categories = categoriesJson.data || [];
		state.brands = brandsJson.data || [];

		buildOptions(refs.categorySelect, state.categories);
		buildOptions(refs.brandSelect, state.brands);
		renderQuickCategoryChips();
	};

	const resetFilters = () => {
		state.search = '';
		state.category = '';
		state.brand = '';
		state.sort = 'latest';
		state.inStockOnly = false;
		state.page = 1;

		refs.searchInput.value = '';
		refs.categorySelect.value = '';
		refs.brandSelect.value = '';
		refs.sortSelect.value = 'latest';
		refs.availabilityToggle.checked = false;

		renderQuickCategoryChips();
		loadProducts();
	};

	refs.searchInput.addEventListener('input', (event) => {
		state.search = event.target.value.trim();
		state.page = 1;
		clearTimeout(searchDebounce);
		searchDebounce = setTimeout(loadProducts, 300);
	});

	refs.categorySelect.addEventListener('change', (event) => {
		state.category = event.target.value;
		state.page = 1;
		renderQuickCategoryChips();
		loadProducts();
	});

	refs.brandSelect.addEventListener('change', (event) => {
		state.brand = event.target.value;
		state.page = 1;
		loadProducts();
	});

	refs.sortSelect.addEventListener('change', (event) => {
		state.sort = event.target.value;
		state.page = 1;
		loadProducts();
	});

	refs.availabilityToggle.addEventListener('change', (event) => {
		state.inStockOnly = event.target.checked;
		state.page = 1;
		loadProducts();
	});

	refs.prevPageButton.addEventListener('click', () => {
		if (state.page <= 1) return;
		state.page -= 1;
		loadProducts();
	});

	refs.nextPageButton.addEventListener('click', () => {
		if (state.page >= state.lastPage) return;
		state.page += 1;
		loadProducts();
	});

	refs.resetFiltersButton.addEventListener('click', resetFilters);

	refs.quickCategoryChips.addEventListener('click', (event) => {
		const button = event.target.closest('[data-category-chip]');
		if (!button) return;
		state.category = button.getAttribute('data-category-chip') || '';
		state.page = 1;
		refs.categorySelect.value = state.category;
		renderQuickCategoryChips();
		loadProducts();
	});

	refs.productGrid.addEventListener('click', (event) => {
		const button = event.target.closest('[data-add-to-cart]');
		if (!button) return;
		const productId = Number(button.getAttribute('data-add-to-cart'));
		addToCart(productId);
	});

	refs.clearCartButton.addEventListener('click', () => {
		state.cart = [];
		saveCartStorage();
		renderCart();
	});

	refs.cartAnchor.addEventListener('click', () => {
		document.getElementById('cartPanel')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
	});

	(async () => {
		state.cart = getCartStorage();
		renderCart();

		try {
			await loadFilters();
		} catch (error) {
			setError(true);
		}

		await loadProducts();
	})();
}
