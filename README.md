# Thrift Marketplace

A modern, full-stack marketplace for selling curated thrift, secondhand, sample, surplus, and one-of-a-kind products online.

> **Status:** 🚧 MVP — Active Development

## Overview

Thrift Marketplace is an e-commerce platform designed around the unique challenges of selling thrift and limited-quantity products.

Unlike traditional e-commerce stores where products can usually be restocked, thrift marketplaces often deal with:

* One-of-a-kind items
* Limited quantities
* Sample products
* Secondhand products
* Irregular inventory
* Frequently changing product catalogs

The platform is designed to make the buying process as simple as possible:

**Browse → Add to Cart → Checkout → Pay → Track Order**

while giving store administrators a simple way to manage:

**Products → Inventory → Orders → Payments → Fulfillment**

---

## ✨ Features

### Storefront

* 🛍️ Product browsing
* 🔎 Product search
* 🏷️ Category and brand filtering
* 📦 Stock availability
* 🖼️ Product image galleries
* 🛒 Shopping cart
* 💳 Checkout
* 📋 Order confirmation
* 🚚 Order tracking
* 📱 Responsive, mobile-first UI

### Inventory

* Unique SKU for every product
* Flexible stock quantities
* Support for one-of-a-kind products
* Support for products with multiple units
* Stock availability calculation
* Temporary inventory reservations
* Automatic reservation expiration
* Overselling protection

### Orders & Payments

* Customer checkout
* Order management
* Payment status tracking
* Payment confirmation
* Order status workflow
* Shipping information
* Order tracking

### Admin

Powered by Laravel Backpack.

* Dashboard
* Product management
* Category management
* Brand management
* Inventory management
* Order management
* Payment management
* Customer management
* Shipping management
* Store settings

---

## 🏗️ Technology Stack

### Backend

* [Laravel](https://laravel.com/)
* PHP
* MySQL
* Laravel Sanctum
* Laravel Backpack

### Frontend

* Vue 3
* Vuetify 3
* Vite
* Vue Router
* Axios

### Architecture

The application uses Laravel as the central backend and source of truth.

```text
                     ┌──────────────────┐
                     │     Customer     │
                     └────────┬─────────┘
                              │
                              ▼
                    ┌──────────────────┐
                    │ Vue + Vuetify    │
                    │   Storefront     │
                    └────────┬─────────┘
                              │
                         REST API
                              │
                              ▼
                    ┌──────────────────┐
                    │     Laravel      │
                    │     Backend      │
                    └────────┬─────────┘
                             │
              ┌──────────────┼──────────────┐
              ▼              ▼              ▼
          ┌────────┐    ┌─────────┐    ┌─────────┐
          │ MySQL  │    │ Payment │    │ Storage │
          └────────┘    │ Gateway │    └─────────┘
                        └─────────┘
                              ▲
                              │
                    ┌─────────┴────────┐
                    │ Laravel Backpack │
                    │   Admin Panel     │
                    └───────────────────┘
```

---

## 📦 Inventory Model

The platform does **not** assume that every product has a quantity of one.

A product can have:

```text
Stock = 1
```

for a unique thrift item, or:

```text
Stock = 10
```

for a product with multiple available units.

Every product receives a unique SKU.

Example:

```text
TS-000001
TS-000002
TS-000003
```

Physical barcode or QR-code stickers are **not required**.

The SKU is primarily an internal inventory identifier.

---

## 🔒 Inventory Reservation

To prevent two customers from purchasing the same item simultaneously, inventory is temporarily reserved when an order is created.

```text
Product Available
       │
       ▼
 Customer Checkout
       │
       ▼
  Order Created
       │
       ▼
 Inventory Reserved
       │
       ├───────────────┐
       │               │
       ▼               ▼
 Payment Success   Payment Expires
       │               │
       ▼               ▼
  Stock Finalized   Reservation
                    Released
```

Adding an item to the cart does **not** permanently reserve inventory.

The server revalidates inventory during checkout.

---

## 🛒 Customer Flow

```text
Homepage
    ↓
Shop
    ↓
Product Details
    ↓
Add to Cart
    ↓
Cart
    ↓
Checkout
    ↓
Customer Information
    ↓
Shipping Information
    ↓
Payment
    ↓
Order Confirmation
    ↓
Order Tracking
```

The primary goal is to minimize manual inquiries.

Customers should be able to complete a purchase without needing to contact the seller for basic information.

---

## 📋 Order Lifecycle

Orders follow a defined lifecycle:

```text
Pending Payment
       │
       ├──── Payment Success ────► Paid
       │                              │
       │                              ▼
       │                         Processing
       │                              │
       │                              ▼
       │                            Packed
       │                              │
       │                              ▼
       │                           Shipped
       │                              │
       │                              ▼
       │                          Completed
       │
       ├──── Payment Timeout ────► Expired
       │
       └──── Cancellation ──────► Cancelled
```

Payment and shipping statuses are tracked separately from the main order status.

---

## 🗃️ Core Models

The MVP is built around the following models:

```text
Category
   └── Products

Brand
   └── Products

Product
   ├── Category
   ├── Brand
   ├── Product Images
   └── Order Items

Customer
   └── Orders

Order
   ├── Customer
   ├── Order Items
   ├── Payments
   └── Shipment

Order Item
   ├── Order
   └── Product
```

---

## 🚀 MVP Scope

### Storefront

* [x] Project architecture
* [ ] Homepage
* [ ] Product listing
* [ ] Product search
* [ ] Product filtering
* [ ] Product details
* [ ] Shopping cart
* [ ] Checkout
* [ ] Order confirmation
* [ ] Order tracking

### Admin

* [ ] Laravel Backpack setup
* [ ] Product CRUD
* [ ] Category CRUD
* [ ] Brand CRUD
* [ ] Product image management
* [ ] Inventory management
* [ ] Order management
* [ ] Payment management
* [ ] Customer management
* [ ] Shipping management
* [ ] Store settings

### Backend

* [ ] Database migrations
* [ ] Models and relationships
* [ ] Product API
* [ ] Cart validation
* [ ] Checkout API
* [ ] Order creation
* [ ] Inventory reservation
* [ ] Payment processing
* [ ] Reservation expiration
* [ ] Order tracking
* [ ] Overselling protection

---

## 🛠️ Local Development

### Requirements

Make sure the following are installed:

* PHP
* Composer
* Node.js
* npm
* MySQL
* Git

### Clone the repository

```bash
git clone https://github.com/YOUR_USERNAME/thrift-marketplace.git

cd thrift-marketplace
```

### Install PHP dependencies

```bash
composer install
```

### Install frontend dependencies

```bash
npm install
```

### Configure environment

```bash
cp .env.example .env
```

Configure the database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=thrift_marketplace
DB_USERNAME=root
DB_PASSWORD=
```

Generate the application key:

```bash
php artisan key:generate
```

### Run migrations

```bash
php artisan migrate
```

### Start the Laravel server

```bash
php artisan serve
```

### Start Vite

```bash
npm run dev
```

The application should then be available at:

```text
http://127.0.0.1:8000
```

---

## 🧪 Testing

Run the Laravel test suite:

```bash
php artisan test
```

Before considering the MVP complete, the following scenarios should be tested:

### Inventory

* Product with stock of 1
* Product with stock greater than 1
* Product becomes unavailable
* Inventory reservation
* Reservation expiration
* Inventory restoration
* Concurrent checkout attempts
* Overselling prevention

### Checkout

* Successful checkout
* Invalid customer information
* Invalid shipping information
* Insufficient stock
* Price changes before checkout
* Empty cart

### Payment

* Successful payment
* Failed payment
* Expired payment
* Duplicate payment callback
* Manual payment confirmation

### Orders

* Pending payment
* Paid
* Processing
* Packed
* Shipped
* Completed
* Cancelled
* Expired

---

## 🔐 Security Principles

The backend is always the source of truth.

The application must never trust the frontend for:

* Product price
* Inventory quantity
* Payment status
* Order totals
* Customer authorization

All critical operations must be validated server-side.

Financial and inventory operations should use database transactions where appropriate.

Payment webhooks must be verified before changing order/payment state.

---

## 📈 Roadmap

### Phase 1 — MVP

* Storefront
* Product catalog
* Inventory
* Cart
* Checkout
* Payments
* Orders
* Admin panel
* Order tracking

### Phase 2 — Store Improvements

* Customer accounts
* Email notifications
* Wishlist
* Discount codes
* Multiple payment methods
* Multiple shipping methods
* Better analytics

### Phase 3 — Advanced Inventory

* Barcode support
* QR codes
* Inventory locations
* Stock batches
* Cost tracking
* Profit reporting

### Phase 4 — Platform

* Native mobile application
* Push notifications
* Loyalty system
* Product recommendations
* Advanced analytics

---

## 🤝 Contributing

Contributions, suggestions, and improvements are welcome.

Before making significant changes, please open an issue to discuss the proposed feature or architectural change.

When contributing:

1. Keep changes focused.
2. Follow existing Laravel and Vue patterns.
3. Add tests for important business logic.
4. Avoid unnecessary dependencies.
5. Do not introduce features outside the current roadmap without discussion.

---

## 📄 Project Documentation

The complete MVP specification and development instructions are maintained in:

```text
PROJECT.md
```

`PROJECT.md` serves as the project's **Single Source of Truth (SSOT)**.

It contains:

* Product requirements
* Business rules
* Architecture
* Database models
* Inventory workflows
* Checkout workflows
* Payment workflows
* Admin modules
* Storefront modules
* API requirements
* Development milestones
* Acceptance criteria

---

## 📜 License

This project is currently under development.

The licensing model will be determined before the first public release.

---

## 💡 Project Goal

Thrift Marketplace is being built around a simple idea:

> **Make selling unique and limited-quantity products online as easy as selling regular e-commerce inventory.**

The customer experience should be:

**Discover → Choose → Checkout → Pay → Receive**

The business experience should be:

**List → Track → Sell → Fulfill**

Everything else should support those two experiences.
