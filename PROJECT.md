# Thrift Store Marketplace — MVP Project Specification

**Document:** `PROJECT.md`  
**Status:** MVP Specification / Single Source of Truth  
**Primary Goal:** Build a simple, polished marketplace for selling thrift items and free samples received from clients, with checkout and payment handling designed to reduce customer inquiries.

---

## 1. Project Overview

### 1.1 Vision

Build a lightweight online storefront where customers can:

1. Browse available products.
2. View complete product details and photos.
3. Add products to a cart.
4. Check out without needing to ask through chat.
5. Pay for their order.
6. Receive an order confirmation and track the order status.

The business can:

1. Add and manage inventory.
2. Assign every item a unique SKU.
3. Control stock quantities.
4. Prevent overselling.
5. Manage orders and payments.
6. Mark orders as paid, packed, shipped, completed, cancelled, or expired.
7. See which products are currently available.

The system should be simple enough to build and deploy as an MVP in approximately one focused development day, while keeping the architecture clean enough for future expansion.

---

## 2. Product Principles

### 2.1 Marketplace First

The storefront is the primary customer-facing product.

The customer should not need to contact the seller for basic information such as:

- Product name
- Price
- Condition
- Photos
- Available quantity
- Product specifications
- Shipping information
- Payment instructions
- Order status

### 2.2 Reduce Manual Inquiries

The system should answer common customer questions through the storefront and checkout flow.

The goal is:

> Browse → Add to Cart → Checkout → Pay → Order Confirmation

rather than:

> Browse → Message Seller → Ask Availability → Ask Price → Ask Payment Details → Wait → Confirm → Pay

### 2.3 Flexible Inventory

Do not assume every product has a quantity of one.

Some products are one-of-a-kind thrift pieces:

```text
Stock = 1
```

Other products may have multiple identical units:

```text
Stock = 5
```

The inventory system must support both.

### 2.4 SKU as the Primary Internal Identifier

Every product should have a SKU.

Example:

```text
TS-000001
TS-000002
TS-000003
```

For unique products, one SKU can represent one physical item.

For products with multiple identical units, the SKU represents the product/stock group.

No physical barcode or sticker is required for MVP.

The SKU can simply be printed or written on internal inventory documentation if needed.

### 2.5 No Overselling

The system must never allow available inventory to become negative.

Inventory availability must account for quantities currently reserved by active orders.

---

# 3. Technology Stack

## Backend

- Laravel
- PHP
- MySQL
- Laravel Sanctum if authentication is required for API/customer accounts
- Laravel Queue for future asynchronous jobs
- Laravel Scheduler for reservation expiration

## Admin

- Laravel Backpack

Backpack will provide the internal administration interface for:

- Products
- Categories
- Brands
- Orders
- Customers
- Payments
- Inventory
- Settings

## Storefront

- Vue 3
- Vuetify 3
- Vite
- Vue Router
- Axios

The storefront may communicate with Laravel through API endpoints.

## Hosting

For MVP, use one hosting environment and one subdomain.

Example:

```text
store.logicbox.com
```

Storefront:

```text
https://store.logicbox.com
```

Admin:

```text
https://store.logicbox.com/admin
```

API:

```text
https://store.logicbox.com/api
```

The architecture should allow the storefront and API to be separated later if required.

---

# 4. System Architecture

```text
                    CUSTOMER
                       |
                       v
              Vue + Vuetify Storefront
                       |
                       | HTTPS / REST API
                       v
                 Laravel Backend
                       |
          +------------+-------------+
          |            |             |
          v            v             v
       MySQL       Payment       Laravel
      Database     Gateway       Services
                       |
                       v
                  Order/Payment
                    Processing

                       ^
                       |
                Laravel Backpack
                   Admin Panel
                       |
                  STORE STAFF
```

Laravel is the central source of truth.

Backpack and the Vue storefront must use the same database and business rules.

---

# 5. User Roles

## 5.1 Guest Customer

Can:

- Browse products
- Search products
- Filter products
- View product details
- Add products to cart
- Checkout
- Provide customer information
- Select shipping method
- Select payment method
- Receive order confirmation

An account should NOT be required for MVP unless needed by the chosen payment/order tracking implementation.

## 5.2 Customer Account

Optional for MVP.

If enabled, customers can:

- View order history
- View order details
- Track orders
- Manage profile information

## 5.3 Admin

Can:

- Manage products
- Manage categories
- Manage brands
- Manage inventory
- Manage orders
- Confirm/review payments
- Manage customers
- Update order status
- Configure store settings

---

# 6. Core Business Rules

## 6.1 Product Stock

Every product has:

```text
stock_quantity
```

Example:

```text
Vintage Shirt
SKU: TS-000001
Stock: 1
```

or:

```text
Plain Shirt
SKU: TS-000002
Stock: 10
```

## 6.2 Available Quantity

Conceptually:

```text
available_quantity =
stock_quantity - reserved_quantity
```

For MVP, reserved quantities may be calculated from active orders rather than stored separately.

## 6.3 Product Availability

A product is:

### Available

When:

```text
available_quantity > 0
```

### Out of Stock

When:

```text
available_quantity <= 0
```

### Draft

When the product is not yet published.

### Archived

When the product should no longer be shown as an active listing.

---

# 7. Inventory Model

The system must distinguish between:

### Product

The catalog listing.

Example:

```text
Vintage Levi's Jacket
SKU: TS-000100
Price: ₱1,500
Stock: 1
```

### Physical Item

For MVP, a separate physical-item table is NOT required.

The product's `stock_quantity` is enough.

This keeps the first version simple.

A future version may introduce individual item records if the business needs:

- Serialized inventory
- Barcode scanning
- QR codes
- Warehouse locations
- Purchase batches
- Item-level cost tracking

---

# 8. SKU Strategy

Format:

```text
TS-000001
```

Recommended generation:

```text
TS-{6 digit sequence}
```

Examples:

```text
TS-000001
TS-000002
TS-000003
```

The SKU must be unique.

The SKU should be searchable in the admin panel.

Do not make the SKU depend on the product name because product names may change.

---

# 9. Product Model

Suggested fields:

```text
id
sku
name
slug
description
short_description
price
compare_at_price
cost_price
stock_quantity
condition
status
category_id
brand_id
created_at
updated_at
deleted_at
```

### Field Definitions

#### sku

Unique internal identifier.

#### name

Customer-facing product name.

#### slug

URL-friendly identifier.

Example:

```text
vintage-denim-jacket
```

#### description

Full product description.

#### short_description

Short summary for product cards.

#### price

Selling price.

#### compare_at_price

Optional original/reference price.

#### cost_price

Optional internal cost.

Do not expose this to customers.

#### stock_quantity

Current inventory quantity.

#### condition

Examples:

```text
new
like_new
very_good
good
fair
```

The exact choices may be changed depending on the store.

#### status

Recommended values:

```text
draft
published
archived
```

Stock availability should be derived from stock quantity rather than stored as a separate manual status.

---

# 10. Category Model

Fields:

```text
id
name
slug
description
is_active
created_at
updated_at
```

Examples:

```text
Clothing
Bags
Shoes
Accessories
Home
Miscellaneous
```

A product belongs to one category for MVP.

Future versions may support multiple categories.

---

# 11. Brand Model

Fields:

```text
id
name
slug
description
is_active
created_at
updated_at
```

Brand is optional.

A thrift item may have:

```text
brand_id = null
```

---

# 12. Product Image Model

Fields:

```text
id
product_id
path
alt_text
sort_order
is_primary
created_at
updated_at
```

A product can have multiple images.

Recommended behavior:

- First image is primary by default.
- Admin can reorder images.
- Admin can select the primary image.

Images should be optimized for web display.

---

# 13. Customer Model

Fields:

```text
id
name
email
phone
address_line_1
address_line_2
city
province
postal_code
country
created_at
updated_at
```

For MVP, customer account authentication is optional.

Guest checkout should still create a customer record.

---

# 14. Cart Model

For MVP, cart can be implemented either:

1. Client-side using local storage, or
2. Server-side for authenticated users.

Recommended MVP:

### Guest Cart

Use browser local storage.

The cart stores:

```text
product_id
quantity
```

The server must still revalidate:

- Product existence
- Price
- Stock
- Product status

during checkout.

Never trust price or availability values coming from the browser.

---

# 15. Order Model

Fields:

```text
id
order_number
customer_id
status
subtotal
shipping_fee
discount_amount
total
payment_status
shipping_status
customer_name
customer_email
customer_phone
shipping_address
notes
expires_at
paid_at
cancelled_at
created_at
updated_at
```

Recommended order statuses:

```text
pending_payment
paid
processing
packed
shipped
completed
cancelled
expired
```

Recommended payment statuses:

```text
pending
paid
failed
expired
refunded
```

Recommended shipping statuses:

```text
pending
processing
shipped
delivered
```

---

# 16. Order Item Model

Fields:

```text
id
order_id
product_id
product_name
sku
unit_price
quantity
subtotal
created_at
updated_at
```

Important:

Store a snapshot of:

- Product name
- SKU
- Unit price

at the time of purchase.

This prevents historical orders from changing when a product is later edited.

Example:

If a product originally cost:

```text
₱1,000
```

and is later changed to:

```text
₱1,200
```

the previous order must still show:

```text
₱1,000
```

---

# 17. Payment Model

Fields:

```text
id
order_id
reference_number
provider
method
amount
status
paid_at
metadata
created_at
updated_at
```

Possible methods:

```text
gcash
maya
bank_transfer
cash_on_delivery
other
```

Actual payment methods depend on the payment provider selected.

Do not hard-code the system around one provider.

---

# 18. Shipping Model

Fields:

```text
id
order_id
courier
tracking_number
shipping_fee
status
shipped_at
delivered_at
created_at
updated_at
```

MVP can initially support manual shipping information.

Automatic courier integrations can be added later.

---

# 19. Product Workflow

## Admin creates a product

```text
Admin
  |
  v
Create Product
  |
  +--> Enter Name
  +--> Generate/Enter SKU
  +--> Select Category
  +--> Select Brand
  +--> Set Price
  +--> Set Stock Quantity
  +--> Set Condition
  +--> Upload Photos
  |
  v
Save as Draft
  |
  v
Publish
```

Once published, the product becomes visible in the storefront.

---

# 20. Customer Shopping Workflow

```text
Customer
   |
   v
Homepage
   |
   v
Browse Shop
   |
   v
Search / Filter
   |
   v
Product Details
   |
   v
Add to Cart
   |
   v
Cart
   |
   v
Checkout
   |
   v
Enter Customer Details
   |
   v
Select Shipping
   |
   v
Select Payment
   |
   v
Place Order
   |
   v
Payment
   |
   v
Order Confirmation
```

---

# 21. Reservation / Stock Workflow

This is one of the most important business rules.

When a customer creates an order but has not paid yet, the item should be temporarily reserved.

Example:

```text
Product Stock = 1
Active Reservation = 1

Available = 0
```

Other customers cannot purchase the same available quantity during the reservation window.

### Order Creation

```text
Available
   |
   v
Customer places order
   |
   v
Pending Payment
   |
   v
Inventory reserved
```

### Payment Success

```text
Pending Payment
   |
   v
Payment Confirmed
   |
   v
Paid
   |
   v
Inventory permanently deducted
```

### Payment Expired

```text
Pending Payment
   |
   v
Payment window expires
   |
   v
Expired
   |
   v
Inventory reservation released
   |
   v
Available Again
```

---

# 22. Reservation Expiration

The MVP should have a configurable payment window.

Example:

```text
Payment Window = 30 minutes
```

When an order is created:

```text
expires_at = now + payment_window
```

A Laravel scheduled job should periodically find:

```text
status = pending_payment
expires_at < now
```

and change the order to:

```text
expired
```

The inventory becomes available again.

The payment window should be configurable through admin settings.

---

# 23. Important Inventory Rule

Never simply decrement stock when the customer clicks "Add to Cart."

Adding to cart does NOT reserve inventory.

Correct flow:

```text
Add to Cart
    ↓
No stock change

Place Order
    ↓
Reserve stock

Payment Confirmed
    ↓
Finalize stock deduction
```

If the implementation uses a reservation mechanism backed by the order quantity, the effective available quantity must exclude active pending-payment orders.

---

# 24. Checkout Validation

At checkout, Laravel must re-check:

### Product

- Exists
- Published
- Not archived

### Price

Use the current server-side price.

Never trust the price sent by Vue.

### Stock

Confirm:

```text
requested_quantity <= available_quantity
```

### Customer

Validate:

- Name
- Email
- Phone

### Shipping

Validate the required address fields.

If any validation fails, do not create the order.

---

# 25. Checkout Workflow

```text
Cart
 |
 v
Checkout
 |
 +--> Validate customer
 |
 +--> Validate shipping
 |
 +--> Validate products
 |
 +--> Validate prices
 |
 +--> Validate stock
 |
 v
Calculate totals
 |
 v
Create Order
 |
 v
Create Order Items
 |
 v
Reserve inventory
 |
 v
Create Payment
 |
 v
Redirect to Payment
```

The total must always be calculated server-side.

Formula:

```text
subtotal =
SUM(order_item.unit_price * order_item.quantity)

total =
subtotal
+ shipping_fee
- discount_amount
```

---

# 26. Payment Workflow

Generic payment workflow:

```text
Order Created
     |
     v
Pending Payment
     |
     v
Customer Pays
     |
     v
Payment Provider
     |
     v
Webhook / Callback
     |
     v
Laravel verifies payment
     |
     v
Payment = Paid
     |
     v
Order = Paid
     |
     v
Inventory finalized
```

Never mark an order as paid solely because the frontend says payment succeeded.

Payment confirmation must come from a trusted provider callback/webhook or verified admin action for manual payment methods.

---

# 27. Manual Payment Workflow

If the MVP supports manual bank transfer or similar payment:

```text
Customer places order
      |
      v
Pending Payment
      |
      v
Customer sends payment
      |
      v
Customer provides reference
      |
      v
Admin reviews payment
      |
      +---- Rejected --> Payment Failed
      |
      +---- Approved --> Paid
```

When approved:

```text
Order = paid
Payment = paid
Inventory = finalized
```

---

# 28. Order Lifecycle

```text
pending_payment
      |
      +---- payment success ----> paid
      |
      +---- timeout ------------> expired
      |
      +---- cancellation -------> cancelled

paid
 |
 v
processing
 |
 v
packed
 |
 v
shipped
 |
 v
completed
```

A paid order should not automatically become completed.

The admin controls fulfillment status.

---

# 29. Cancellation

An order can be cancelled when business rules allow it.

If cancelled before payment:

```text
Reservation released
```

If cancelled after payment:

```text
Order = cancelled
Payment = refunded/pending refund
```

Refund behavior should be implemented according to the selected payment provider.

---

# 30. Storefront Modules

## 30.1 Homepage

Purpose:

Present the store and current products.

Sections:

- Hero
- Featured products
- New arrivals
- Categories
- Store information
- Call to action

Keep the page visually clean.

---

## 30.2 Shop

Features:

- Product grid
- Search
- Category filter
- Brand filter
- Price filter
- Sort
- Availability filtering

Product cards should show:

- Image
- Name
- Price
- Condition
- Availability

Avoid showing unnecessary information.

---

## 30.3 Product Details

Show:

- Image gallery
- Product name
- Price
- Condition
- Brand
- Category
- Description
- SKU if appropriate
- Available quantity
- Add to Cart button

If unavailable:

```text
Out of Stock
```

instead of:

```text
Add to Cart
```

---

## 30.4 Cart

Show:

- Product
- Image
- Price
- Quantity
- Subtotal
- Remove action
- Total

The cart must revalidate stock before checkout.

---

## 30.5 Checkout

Sections:

### Customer Information

- Name
- Email
- Phone

### Shipping Information

- Address
- City
- Province
- Postal code

### Payment

Available payment methods.

### Order Summary

Show:

- Items
- Subtotal
- Shipping
- Discount
- Total

Primary action:

```text
Place Order
```

---

## 30.6 Order Confirmation

Show:

```text
Order #TS-20260820-0001
```

Include:

- Order summary
- Payment status
- Shipping information
- Next steps
- Customer contact information

---

## 30.7 Order Tracking

MVP can support tracking using:

```text
Order Number
Email
```

Customer enters:

```text
Order Number
Email
```

The backend verifies the combination and returns order status.

Example:

```text
Order #TS-20260820-0001

Paid
Processing
Packed
Shipped
Completed
```

---

# 31. Admin Modules

## 31.1 Dashboard

Show:

- Total products
- Available products
- Out-of-stock products
- Pending payments
- Paid orders
- Orders to process
- Recent orders
- Recent products

---

# 32. Admin Product Management

Backpack CRUD:

- List
- Create
- Edit
- View
- Delete/archive

Filters:

- SKU
- Name
- Category
- Brand
- Status
- Stock

Actions:

- Publish
- Unpublish
- Archive
- Duplicate
- Adjust stock

---

# 33. Admin Inventory Management

Inventory view should allow staff to quickly find products.

Search by:

```text
SKU
Product Name
Brand
Category
```

Display:

```text
SKU | Product | Stock | Reserved | Available | Status
```

Example:

```text
TS-000001 | Denim Jacket | 1 | 0 | 1 | Available
TS-000002 | Black Shirt  | 5 | 2 | 3 | Available
```

---

# 34. Admin Order Management

List:

- Order number
- Customer
- Total
- Payment status
- Order status
- Created date

Filters:

- Pending payment
- Paid
- Processing
- Packed
- Shipped
- Completed
- Cancelled
- Expired

Order detail should show:

- Customer
- Items
- Prices
- Payment
- Shipping
- Timeline
- Internal notes

---

# 35. Admin Payment Management

Admin can:

- View payment
- View reference number
- View payment amount
- View payment status
- Confirm manual payment
- Reject manual payment

Payment confirmation must update the order safely and atomically.

---

# 36. Admin Customer Management

Show:

- Name
- Email
- Phone
- Number of orders
- Total spent
- Latest order

Customer details can show order history.

---

# 37. Admin Settings

MVP settings:

```text
Store Name
Store Description
Store Logo
Contact Email
Contact Phone
Payment Window
Default Shipping Fee
Currency
Store Status
```

Future settings:

- Multiple shipping methods
- Multiple payment gateways
- Coupons
- Tax rules
- Delivery zones

---

# 38. API Design

Base URL:

```text
/api
```

## Public Product Endpoints

```http
GET /api/products
GET /api/products/{slug}
GET /api/categories
GET /api/brands
```

## Checkout

```http
POST /api/orders
GET /api/orders/{orderNumber}
POST /api/orders/{orderNumber}/payment
```

## Order Tracking

```http
POST /api/orders/track
```

## Payment Webhook

```http
POST /api/payments/webhook
```

The exact routes can be adjusted during implementation.

---

# 39. API Rules

The API must:

- Validate all incoming data.
- Never trust frontend prices.
- Never trust frontend stock values.
- Use database transactions for order creation.
- Prevent duplicate payment processing.
- Validate payment callbacks.
- Return consistent HTTP status codes.
- Return useful validation errors.

---

# 40. Database Relationships

Recommended relationships:

```text
Category
   |
   +---- hasMany Products

Brand
   |
   +---- hasMany Products

Product
   |
   +---- belongsTo Category
   +---- belongsTo Brand
   +---- hasMany ProductImages
   +---- hasMany OrderItems

Customer
   |
   +---- hasMany Orders

Order
   |
   +---- belongsTo Customer
   +---- hasMany OrderItems
   +---- hasMany Payments
   +---- hasOne Shipment

OrderItem
   |
   +---- belongsTo Order
   +---- belongsTo Product
```

---

# 41. Database Transaction Requirements

Order creation must use a database transaction.

Conceptually:

```php
DB::transaction(function () {
    validateProducts();
    validateStock();
    createOrder();
    createOrderItems();
    reserveInventory();
    createPayment();
});
```

If any operation fails:

```text
ROLLBACK
```

No partial order should remain.

---

# 42. Concurrency / Overselling Protection

Two customers may attempt to purchase the same product at almost exactly the same time.

The backend must protect against this.

Use database transactions and appropriate row locking when checking and reserving inventory.

Conceptually:

```text
Customer A -> Check Stock
Customer B -> Check Stock

Only one transaction should successfully reserve
the final available quantity.
```

Never rely solely on frontend availability.

---

# 43. UI / UX Direction

The storefront should feel like a modern boutique/thrift marketplace.

Principles:

- Mobile-first
- Clean
- Premium but approachable
- Image-focused
- Fast
- Minimal friction
- Strong typography
- Clear price hierarchy
- Obvious availability
- Simple checkout

Avoid:

- Excessive animations
- Cluttered product cards
- Too many filters
- Unnecessary popups
- Forced account creation
- Long checkout forms

---

# 44. Product Card UX

Each card should communicate:

```text
[Product Image]

Product Name
Brand / Category
Condition

₱1,200

Available
```

Primary action:

```text
View Product
```

or:

```text
Add to Cart
```

depending on the design.

---

# 45. Mobile UX

The majority of storefront interactions should work comfortably on mobile.

Important:

- Large tap targets
- Sticky cart access where appropriate
- Simple checkout
- Optimized images
- Minimal typing
- Clear validation messages
- Easy back navigation

---

# 46. Admin UX

Backpack is an internal tool.

Prioritize:

- Speed
- Search
- Filters
- Bulk-friendly workflows
- Clear status indicators
- Minimal clicks

Admin users should be able to go from:

```text
New Product
```

to:

```text
Published
```

quickly.

---

# 47. Security Requirements

Minimum:

- CSRF protection
- Authentication for admin
- Authorization
- Input validation
- SQL injection protection through Eloquent/query builder
- XSS protection
- Rate limiting for sensitive endpoints
- Secure payment webhook validation
- Server-side price calculation
- Server-side stock validation

Never expose:

```text
cost_price
admin credentials
payment secrets
API secrets
```

to the storefront.

---

# 48. SEO Requirements

Storefront should support:

- SEO-friendly URLs
- Product slugs
- Page titles
- Meta descriptions
- Open Graph metadata
- Product structured data where practical

Example:

```text
/store/vintage-denim-jacket
```

instead of:

```text
/product?id=123
```

---

# 49. Image Requirements

Product images should:

- Be optimized
- Have predictable storage paths
- Have alt text
- Support multiple images
- Support a primary image

Recommended image behavior:

```text
Upload
  ↓
Validate
  ↓
Resize/Optimize
  ↓
Store
  ↓
Generate URL
```

---

# 50. Notifications

MVP should support basic order notifications.

Potential channels:

- Email
- Admin notification

Customer notifications:

```text
Order received
Payment confirmed
Order shipped
Order completed
Order cancelled
```

If email integration increases development time significantly, implement the order system first and make notification delivery a second milestone.

---

# 51. Logging / Audit

Important events should be logged.

Examples:

```text
Product created
Product updated
Product published
Stock adjusted
Order created
Payment confirmed
Payment rejected
Order cancelled
Order shipped
```

This is useful when investigating inventory discrepancies.

---

# 52. MVP Scope

## MUST HAVE

### Storefront

- [ ] Homepage
- [ ] Shop/product listing
- [ ] Product search
- [ ] Basic filtering
- [ ] Product detail page
- [ ] Cart
- [ ] Checkout
- [ ] Customer information
- [ ] Shipping information
- [ ] Payment method
- [ ] Order confirmation
- [ ] Order tracking

### Admin

- [ ] Backpack installation
- [ ] Admin authentication
- [ ] Dashboard
- [ ] Product CRUD
- [ ] Category CRUD
- [ ] Brand CRUD
- [ ] Product image management
- [ ] Inventory management
- [ ] Order management
- [ ] Payment management
- [ ] Customer management
- [ ] Settings

### Backend

- [ ] Database migrations
- [ ] Models
- [ ] Relationships
- [ ] API endpoints
- [ ] Server-side validation
- [ ] Order creation
- [ ] Inventory reservation
- [ ] Payment state management
- [ ] Reservation expiration
- [ ] Order tracking
- [ ] Transaction protection

---

# 53. NOT REQUIRED FOR MVP

Do NOT let these delay the first release:

- [ ] Customer reviews
- [ ] Wishlist
- [ ] Coupons
- [ ] Loyalty program
- [ ] Product recommendations
- [ ] Multiple warehouses
- [ ] Barcode scanner
- [ ] QR code scanner
- [ ] Native mobile app
- [ ] Advanced analytics
- [ ] Automated courier integration
- [ ] Multi-vendor marketplace
- [ ] Seller accounts
- [ ] Complex tax engine
- [ ] Advanced inventory batches

---

# 54. One-Day Development Strategy

The goal is not to build every possible feature.

The goal is to build one complete happy path:

```text
Admin creates product
       ↓
Product appears in storefront
       ↓
Customer views product
       ↓
Customer adds to cart
       ↓
Customer checks out
       ↓
Order is created
       ↓
Inventory is reserved
       ↓
Payment is completed/confirmed
       ↓
Order becomes paid
       ↓
Inventory is finalized
       ↓
Admin processes order
       ↓
Customer sees order status
```

If this workflow works reliably, the MVP is successful.

---

# 55. Recommended Development Order

## Phase 1 — Project Setup

- [ ] Create Laravel project
- [ ] Configure environment
- [ ] Configure MySQL
- [ ] Install Backpack
- [ ] Configure Vue 3
- [ ] Configure Vuetify
- [ ] Configure Vite
- [ ] Configure API routes
- [ ] Configure storage

Acceptance criteria:

```text
Laravel runs.
Backpack runs.
Vue storefront runs.
Database connection works.
```

---

## Phase 2 — Database

Create migrations for:

- [ ] categories
- [ ] brands
- [ ] products
- [ ] product_images
- [ ] customers
- [ ] orders
- [ ] order_items
- [ ] payments
- [ ] shipments
- [ ] settings

Acceptance criteria:

All migrations run successfully and relationships are defined.

---

## Phase 3 — Models

Create:

```text
Category
Brand
Product
ProductImage
Customer
Order
OrderItem
Payment
Shipment
Setting
```

Add:

- [ ] Fillable/casts
- [ ] Relationships
- [ ] Scopes
- [ ] Soft deletes where appropriate

---

# 56. Phase 4 — Backpack Admin

Build CRUDs in this order:

```text
Category
Brand
Product
Customer
Order
Payment
Shipment
Settings
```

Product admin must support:

- SKU
- Name
- Description
- Price
- Stock
- Category
- Brand
- Condition
- Images
- Status

Acceptance criteria:

An admin can create and publish a complete product without touching the database.

---

# 57. Phase 5 — Storefront

Build:

```text
Home
Shop
Product Details
Cart
Checkout
Order Confirmation
Order Tracking
```

Acceptance criteria:

A customer can complete a purchase without contacting the seller.

---

# 58. Phase 6 — Inventory

Implement:

- [ ] Stock validation
- [ ] Reservation
- [ ] Available quantity
- [ ] Expiration
- [ ] Payment confirmation
- [ ] Stock finalization
- [ ] Stock restoration on expiration/cancellation

Acceptance criteria:

Two customers cannot successfully purchase the last available unit.

---

# 59. Phase 7 — Payment

Implement the selected payment method.

The architecture must support:

```text
Payment Pending
Payment Paid
Payment Failed
Payment Expired
Payment Refunded
```

Acceptance criteria:

A payment confirmation updates the correct order exactly once.

---

# 60. Phase 8 — Order Fulfillment

Admin can:

```text
Paid
  ↓
Processing
  ↓
Packed
  ↓
Shipped
  ↓
Completed
```

Admin can enter:

```text
Courier
Tracking Number
```

Customer can see the current status.

---

# 61. Phase 9 — Testing

Minimum tests:

### Product

- [ ] Product can be created
- [ ] Product can be published
- [ ] Product appears in storefront
- [ ] Product can be archived

### Inventory

- [ ] Stock displays correctly
- [ ] Add to cart does not permanently reduce stock
- [ ] Order reserves stock
- [ ] Expired order releases stock
- [ ] Paid order finalizes stock
- [ ] Cancellation releases stock where appropriate
- [ ] Overselling is prevented

### Checkout

- [ ] Valid checkout succeeds
- [ ] Invalid customer data fails
- [ ] Invalid product fails
- [ ] Insufficient stock fails
- [ ] Server calculates price
- [ ] Order total is correct

### Payment

- [ ] Payment success works
- [ ] Payment failure works
- [ ] Duplicate webhook does not double-process
- [ ] Order status updates correctly

---

# 62. Definition of Done

The MVP is considered complete when:

```text
Admin can create a product.
        ↓
Product appears in storefront.
        ↓
Customer can browse it.
        ↓
Customer can add it to cart.
        ↓
Customer can checkout.
        ↓
Order is created.
        ↓
Inventory is reserved.
        ↓
Payment is confirmed.
        ↓
Inventory is finalized.
        ↓
Admin can process order.
        ↓
Customer can see order status.
```

Additionally:

- No overselling
- No negative inventory
- No frontend-controlled pricing
- Payment status is trustworthy
- Expired reservations release stock
- Admin can manage the complete order lifecycle

---

# 63. Future Roadmap

After MVP:

## Phase 2

- Customer accounts
- Email notifications
- Better order tracking
- Multiple shipping methods
- Automated payment integrations
- Discount codes
- Wishlist

## Phase 3

- Barcode/QR support
- Advanced inventory
- Inventory locations
- Analytics dashboard
- Sales reports
- Cost/profit tracking
- Purchase batches

## Phase 4

- Native mobile app
- Push notifications
- Loyalty program
- Recommendations
- Advanced customer profiles

---

# 64. Codex Implementation Rules

Codex should treat this file as the project's Single Source of Truth.

Before implementing a feature:

1. Read the relevant section.
2. Check existing code before creating new architecture.
3. Prefer existing Laravel/Vue patterns.
4. Do not introduce unnecessary packages.
5. Keep business logic in backend services/actions rather than Vue components.
6. Keep controllers thin.
7. Use Form Requests for validation.
8. Use API Resources where appropriate.
9. Use database transactions for financial/inventory operations.
10. Never trust frontend pricing or inventory.
11. Keep the storefront responsive.
12. Keep Backpack configuration organized.
13. Do not implement future features unless explicitly requested.

---

# 65. Suggested Laravel Structure

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   └── Admin/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Services/
│   ├── InventoryService.php
│   ├── OrderService.php
│   ├── PaymentService.php
│   └── ShippingService.php
├── Actions/
│   ├── CreateOrder.php
│   ├── ReserveInventory.php
│   ├── ConfirmPayment.php
│   └── ExpireOrder.php
└── Console/
    └── Commands/

resources/
├── js/
│   ├── components/
│   ├── layouts/
│   ├── pages/
│   ├── router/
│   ├── services/
│   └── stores/
```

The exact structure may be adapted to the chosen Laravel version.

---

# 66. Recommended Service Responsibilities

## InventoryService

Responsible for:

- Available quantity
- Stock validation
- Reservation checks
- Inventory finalization
- Inventory release

## OrderService

Responsible for:

- Creating orders
- Calculating totals
- Creating order items
- Order state transitions

## PaymentService

Responsible for:

- Creating payment records
- Processing payment confirmation
- Verifying callbacks
- Preventing duplicate payment processing

## ShippingService

Responsible for:

- Shipping information
- Tracking numbers
- Shipping status

---

# 67. Important State Separation

Do not use one `status` field to represent everything.

Keep these concepts separate:

```text
Order Status
Payment Status
Shipping Status
Product Publication Status
Inventory Availability
```

Example:

```text
Product:
published

Inventory:
0 available

Order:
paid

Payment:
paid

Shipping:
processing
```

This is much easier to maintain than one giant status field.

---

# 68. Final Product Philosophy

The MVP should feel simple to the customer but structured internally.

Customer sees:

```text
Browse
→
Choose
→
Checkout
→
Pay
→
Receive
```

Staff sees:

```text
Inventory
→
Orders
→
Payments
→
Fulfillment
```

The system handles:

```text
SKU
Stock
Reservation
Payment
Order State
Inventory Safety
```

The customer should not need to understand any of those internal mechanisms.

---

# 69. Final Codex Checklist

- [ ] Read this entire PROJECT.md before implementation.
- [ ] Confirm Laravel environment.
- [ ] Install/configure Backpack.
- [ ] Configure Vue 3 + Vuetify 3.
- [ ] Configure database.
- [ ] Create migrations.
- [ ] Create models and relationships.
- [ ] Create seed data.
- [ ] Build Backpack admin.
- [ ] Build storefront.
- [ ] Build product browsing.
- [ ] Build product details.
- [ ] Build cart.
- [ ] Build checkout.
- [ ] Build order creation.
- [ ] Build inventory reservation.
- [ ] Build payment workflow.
- [ ] Build reservation expiration.
- [ ] Build order management.
- [ ] Build shipping status.
- [ ] Build order tracking.
- [ ] Add validation.
- [ ] Add transactions.
- [ ] Add overselling protection.
- [ ] Test complete purchase flow.
- [ ] Test expired payment flow.
- [ ] Test cancellation flow.
- [ ] Test concurrent inventory purchase.
- [ ] Polish mobile UX.
- [ ] Configure production environment.
- [ ] Deploy.
- [ ] Run final smoke test.

---

# 70. MVP Success Criteria

The project succeeds if a real customer can discover a product and complete the entire purchase journey without needing to send a message to the seller for routine information.

The core experience is:

> **See it → Want it → Add to Cart → Checkout → Pay → Get Confirmation**

while the business experience is:

> **List it → Track inventory → Receive payment → Fulfill order → Complete sale**

That is the MVP.
