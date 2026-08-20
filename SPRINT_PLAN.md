# ThriftMarket Sprint Plan (Pre-Implementation)

## Status
- Sprint: `Sprint 1 - MVP Foundation + Happy Path`
- State: `Draft for Review`
- Coding Start: `Blocked until this document is approved`
- Source of Truth: `PROJECT.md`

---

## 1. Sprint Goal
Deliver one complete, reliable MVP purchase flow end-to-end:

Admin creates and publishes product -> product appears in storefront -> customer browses -> adds to cart -> checks out -> order is created -> stock is reserved -> payment is confirmed -> stock is finalized -> admin updates fulfillment -> customer tracks status.

This sprint follows the MVP rules in `PROJECT.md`, with strict focus on:
- no overselling
- server-side price and stock validation
- reservation expiration handling
- clear separation of order, payment, and shipping states

---

## 2. Sprint Scope

### In Scope (MVP Must-Have)
- Laravel project setup + MySQL config
- Backpack admin setup
- Vue 3 + Vuetify storefront setup
- Core database schema + relationships
- Product catalog APIs (products, categories, brands)
- Cart to checkout flow
- Order creation with transaction protection
- Inventory reservation and expiration
- Payment status workflow (manual + webhook-ready design)
- Admin order/payment/inventory management basics
- Order tracking by order number + email

### Out of Scope (Do Not Implement in This Sprint)
- Wishlist, reviews, loyalty, recommendations
- Multi-warehouse, barcode/QR, advanced batches
- Complex tax engine
- Native mobile app
- Multi-vendor features

---

## 3. Definition of Done (Sprint-Level)
The sprint is complete only when all are true:
- [ ] Happy-path purchase flow works end-to-end
- [ ] Overselling prevention is proven with concurrency test
- [ ] Expired pending-payment orders release reservation
- [ ] Frontend cannot control final price/stock decisions
- [ ] Admin can manage product -> order -> payment -> shipping lifecycle
- [ ] Order tracking endpoint works using order number + email
- [ ] Core tests for inventory/checkout/payment pass

---

## 4. Sprint Work Breakdown

## Phase A - Setup and Foundations
Goal: runnable stack with shared backend + storefront integration.

Tasks:
- [ ] Initialize Laravel project and `.env`
- [ ] Configure MySQL connection
- [ ] Install/configure Backpack admin
- [ ] Configure Vue 3 + Vuetify + Vite in Laravel
- [ ] Configure API route structure and storage

Acceptance Criteria:
- [ ] Laravel app runs
- [ ] Backpack admin loads at `/admin`
- [ ] Vue storefront loads
- [ ] Database connection succeeds

---

## Phase B - Data Model and Schema
Goal: stable schema aligned with PROJECT.md.

Tasks:
- [ ] Create migrations: categories, brands, products, product_images, customers, orders, order_items, payments, shipments, settings
- [ ] Add unique SKU constraint
- [ ] Add product status and condition enums/constraints (as appropriate)
- [ ] Add order/payment/shipping status fields
- [ ] Add indexes for frequent lookups (sku, slug, order_number, status)
- [ ] Create Eloquent models + relationships + casts + scopes

Acceptance Criteria:
- [ ] All migrations run successfully
- [ ] Relationships match spec
- [ ] SKU uniqueness enforced
- [ ] Order tables support snapshot pricing

---

## Phase C - Admin MVP (Backpack)
Goal: staff can manage catalog and operations without direct DB edits.

Tasks:
- [ ] Category CRUD
- [ ] Brand CRUD
- [ ] Product CRUD (SKU, stock, status, condition, category, brand)
- [ ] Product image management (multiple images, primary image)
- [ ] Customer list and detail basics
- [ ] Order management screens (status transitions)
- [ ] Payment management screens (approve/reject manual)
- [ ] Settings screen (payment window, shipping fee, store contact)
- [ ] Inventory view: stock, reserved, available

Acceptance Criteria:
- [ ] Admin can create and publish complete product
- [ ] Admin can find products by SKU and status
- [ ] Admin can review and update order/payment states safely

---

## Phase D - Storefront MVP
Goal: customer can browse and place order without chat/manual intervention.

Tasks:
- [ ] Homepage
- [ ] Shop page with search + basic filters (category/brand/availability)
- [ ] Product details page
- [ ] Local-storage guest cart
- [ ] Cart page with totals
- [ ] Checkout page (customer + shipping + payment method)
- [ ] Order confirmation page
- [ ] Order tracking page (order number + email)

Acceptance Criteria:
- [ ] Customer can complete checkout flow end-to-end
- [ ] Out-of-stock products cannot be added at checkout
- [ ] Mobile layout is usable and responsive

---

## Phase E - Order, Inventory, Payment Core Logic
Goal: robust backend transaction flow and inventory safety.

Tasks:
- [ ] Build form requests for checkout/tracking/payment actions
- [ ] Build services/actions for order creation, reservation, confirmation, expiration
- [ ] Implement transaction-based order creation (`DB::transaction`)
- [ ] Revalidate product status/price/stock server-side at checkout
- [ ] Reserve stock on order creation (`pending_payment`)
- [ ] Finalize stock deduction on payment confirmation
- [ ] Release reservation on expiry/cancellation
- [ ] Add scheduler job for reservation expiration
- [ ] Add idempotency guard for duplicate payment callbacks

Acceptance Criteria:
- [ ] No negative inventory
- [ ] Duplicate payment callback does not double-process
- [ ] Reservation expiration updates order and availability correctly

---

## Phase F - API Layer
Goal: expose required MVP endpoints with consistent responses.

Tasks:
- [ ] `GET /api/products`
- [ ] `GET /api/products/{slug}`
- [ ] `GET /api/categories`
- [ ] `GET /api/brands`
- [ ] `POST /api/orders`
- [ ] `GET /api/orders/{orderNumber}`
- [ ] `POST /api/orders/{orderNumber}/payment`
- [ ] `POST /api/orders/track`
- [ ] `POST /api/payments/webhook`

Acceptance Criteria:
- [ ] Validation errors are clear and consistent
- [ ] API never trusts frontend price or stock values
- [ ] HTTP status codes are consistent and meaningful

---

## Phase G - Test and Verification
Goal: prove critical behavior before release.

Tasks:
- [ ] Product lifecycle tests (create/publish/list/archive)
- [ ] Inventory tests (reserve/finalize/release/overselling)
- [ ] Checkout validation tests (bad data, bad stock, price authority)
- [ ] Payment tests (success/fail/idempotent webhook)
- [ ] Concurrency scenario test for last-unit purchase
- [ ] Basic smoke test of complete flow

Acceptance Criteria:
- [ ] All critical tests pass
- [ ] Concurrency case demonstrates no oversell
- [ ] Happy-path smoke test passes

---

## 5. Execution Order (Recommended)
1. Phase A
2. Phase B
3. Phase C (catalog-related first)
4. Phase E (core order/inventory/payment)
5. Phase F
6. Phase D (wire to live APIs)
7. Phase G

Reason for order:
- Data model and backend rules are the risk center.
- Storefront wiring is fastest after API contracts stabilize.
- Admin catalog must exist early for realistic test data.

---

## 6. Progress Tracker
Use this section as the live sprint tracker.

- [x] A1 Setup complete
- [x] B1 Schema complete
- [x] C1 Admin catalog complete
- [x] C2 Admin operations complete
- [x] E1 Order transaction flow complete
- [x] E2 Inventory reservation/expiration complete
- [x] E3 Payment confirmation/idempotency complete
- [x] F1 Public product APIs complete
- [x] F2 Checkout/tracking/payment APIs complete
- [x] D1 Storefront browse flow complete
- [ ] D2 Storefront checkout flow complete
- [ ] G1 Tests complete
- [ ] Release smoke test complete

---

## 7. Risks and Mitigation
- Risk: overselling under concurrent checkout.
  - Mitigation: row-level locking + transaction boundaries + concurrency tests.

- Risk: duplicate webhook processing.
  - Mitigation: idempotency checks and atomic payment/order updates.

- Risk: scope creep from non-MVP features.
  - Mitigation: strict out-of-scope list and approval gate for new features.

- Risk: delays from payment integration complexity.
  - Mitigation: implement manual payment flow first, keep webhook-ready architecture.

---

## 8. Review and Approval Gate
Before coding starts, confirm:
- [ ] Sprint scope approved
- [ ] Execution order approved
- [ ] Acceptance criteria approved
- [ ] Out-of-scope list approved

When approved, we begin with Phase A and update this file after each completed checkpoint.
