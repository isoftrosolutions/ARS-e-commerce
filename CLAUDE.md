# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Easy Shopping A.R.S** is a single-vendor e-commerce platform for the Nepal market. Built with vanilla PHP (no framework), MySQL/PDO, and plain HTML/CSS/JS. Runs on Apache 2.4.

## Setup & Running

**Requirements:** Apache 2.4 with PHP, MySQL server, writable `uploads/` directory.

**Initialize the database:**
```bash
php setup_db.php
# or visit http://localhost/ARS/setup_db.php in browser
```
This creates the `ars_ecommerce` MySQL database with all tables and seed data.

**Default admin credentials:** mobile `9800000000` / password `admin123`

**No build step** — PHP files serve directly from Apache.

**Database config** is hardcoded in `config/db.php` (localhost, root, no password by default).

## Architecture

### Request Flow

All pages include `config/db.php` first (establishes PDO connection, starts session, defines constants), then typically `includes/header.php` and `includes/footer.php` for the HTML shell.

### Directory Layout

- `config/db.php` — PDO connection, session start, constants (`SITE_NAME`, `CURRENCY`, `UPLOAD_DIR`)
- `includes/functions.php` — Shared utilities: `is_logged_in()`, `is_admin()`, `redirect()`, `display_message()`, `formatPrice()`, `get_cart_count()`
- `includes/header.php` / `footer.php` — Page template wrappers
- `auth/` — Login, signup, logout (role-based: `admin` or `customer`)
- `admin/` — Admin panel pages; each page calls `is_admin()` or redirects
- `assets/main.css` — All styles
- `uploads/` — Product images and payment proofs (auto-created)

### Key Pages

| File | Purpose |
|------|---------|
| `index.php` | Homepage, latest 8 products |
| `shop.php` | Catalog with `?category=ID` and `?q=search` filtering |
| `product.php` | Product detail by `?slug=...` |
| `cart-action.php` | Cart mutations (add/remove/update), redirects back |
| `cart.php` | Cart view |
| `checkout.php` | Checkout form (COD / eSewa / BankQR) |
| `checkout-process.php` | POST handler — creates order, updates stock, clears cart |
| `order-success.php` | Confirmation page |
| `admin/dashboard.php` | Stats overview |
| `admin/products.php` | Product list/delete |
| `admin/product-add.php` | Add product with image upload |
| `admin/orders.php` | Order list with status updates |

### Cart

Cart lives in `$_SESSION['cart']` as `[product_id => ['name', 'price', 'image', 'qty']]`. No DB persistence for anonymous carts; cleared after order creation.

### Database Schema (database: `ars_ecommerce`)

- **users** — `id`, `full_name`, `email`, `mobile` (login key), `password` (bcrypt), `address`, `role` (admin|customer)
- **categories** — `id`, `name`, `slug`
- **products** — `id`, `name`, `slug`, `description`, `price`, `discount_price`, `category_id`, `stock`, `image` (filename), `sku`, `is_featured`
- **orders** — `id`, `user_id` (nullable), `total_amount`, `payment_method` (COD|eSewa|BankQR), `payment_status` (Pending|Paid|Failed), `delivery_status` (Pending|Confirmed|Shipped|Delivered|Cancelled), `transaction_id`, `payment_proof` (filename), `address`, `notes`
- **order_items** — `id`, `order_id`, `product_id`, `quantity`, 
### File Uploads

Images stored in `uploads/`, named `product_[timestamp]_[random].ext` or `proof_[timestamp]_[random].ext`. Allowed: jpg, jpeg, png, webp. Max 1:1 ratio recommended (1000×1000px) for product images.

