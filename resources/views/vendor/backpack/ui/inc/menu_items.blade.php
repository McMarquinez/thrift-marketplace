{{-- This file is used for menu items by any Backpack v6 theme --}}
<x-backpack::menu-item title="Dashboard" icon="la la-home" :link="backpack_url('dashboard')" />

<li class="nav-title">Catalog</li>
<x-backpack::menu-item title="Products" icon="la la-cube" :link="backpack_url('product')" />
<x-backpack::menu-item title="Product Images" icon="la la-image" :link="backpack_url('product-image')" />
<x-backpack::menu-item title="Categories" icon="la la-tags" :link="backpack_url('category')" />
<x-backpack::menu-item title="Brands" icon="la la-certificate" :link="backpack_url('brand')" />

<li class="nav-title">Sales</li>
<x-backpack::menu-item title="Orders" icon="la la-shopping-cart" :link="backpack_url('order')" />
<x-backpack::menu-item title="Payments" icon="la la-credit-card" :link="backpack_url('payment')" />
<x-backpack::menu-item title="Customers" icon="la la-users" :link="backpack_url('customer')" />

<li class="nav-title">Fulfillment</li>
<x-backpack::menu-item title="Shipments" icon="la la-truck" :link="backpack_url('shipment')" />

<li class="nav-title">Configuration</li>
<x-backpack::menu-item title="Settings" icon="la la-cog" :link="backpack_url('setting')" />