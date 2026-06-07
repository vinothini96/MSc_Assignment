# EleganceSarees — Complete Technical Documentation

> **Project:** EleganceSarees — Online Saree E-Commerce Platform  
> **Stack:** Core PHP (PDO), MySQL, Bootstrap 5, Vanilla JavaScript  
> **Server:** Apache via XAMPP | **Database:** `saree_shop_db`  
> **Base URL:** `http://localhost/EleganceSarees`  
> **Last Updated:** June 2026 — Session expiry & logout security fixes applied

---

## Table of Contents

1. [Project Architecture Overview](#1-project-architecture-overview)
2. [Database Schema](#2-database-schema)
3. [Configuration & Bootstrap System](#3-configuration--bootstrap-system)
4. [Session Management](#4-session-management)
5. [User Authentication Flow](#5-user-authentication-flow)
6. [Admin Authentication Flow](#6-admin-authentication-flow)
7. [User Registration Flow](#7-user-registration-flow)
8. [Logout Flow](#8-logout-flow)
9. [Product Browsing & Search Flow](#9-product-browsing--search-flow)
10. [Product Detail Page Flow](#10-product-detail-page-flow)
11. [Cart System Flow](#11-cart-system-flow)
12. [Checkout & Order Processing Flow](#12-checkout--order-processing-flow)
13. [Loyalty Points System](#13-loyalty-points-system)
14. [Wishlist Flow](#14-wishlist-flow)
15. [Reviews & Ratings Flow](#15-reviews--ratings-flow)
16. [User Profile Management](#16-user-profile-management)
17. [Admin — Product Management Flow](#17-admin--product-management-flow)
18. [Admin — Category Management Flow](#18-admin--category-management-flow)
19. [Admin — Order Management Flow](#19-admin--order-management-flow)
20. [Admin — User Management Flow](#20-admin--user-management-flow)
21. [Admin — Banner Management Flow](#21-admin--banner-management-flow)
22. [Admin — Coupon Management](#22-admin--coupon-management)
23. [Admin — Dashboard & Analytics](#23-admin--dashboard--analytics)
24. [Contact Form Flow](#24-contact-form-flow)
25. [Helper Functions Reference](#25-helper-functions-reference)
26. [JavaScript Modules](#26-javascript-modules)
27. [Security Model](#27-security-model)
28. [Known Issues & Notes](#28-known-issues--notes)

---

## 1. Project Architecture Overview

```
EleganceSarees/
├── index.php                  ← Home page
├── shop.php                   ← Product listing with filters (uses $pdo + snake_case)
├── product.php                ← Product detail by slug (uses $pdo + snake_case)
├── product-detail.php         ← Product detail by ID (uses init.php + camelCase)
├── products.php               ← Paginated listing (uses init.php + camelCase)
├── cart.php                   ← Shopping cart view
├── checkout.php               ← Checkout form
├── orders.php                 ← Order history
├── order-confirmation.php     ← Post-order confirmation
├── login.php / register.php   ← Auth pages
├── logout.php                 ← Session destroy
├── profile.php                ← User profile edit
├── wishlist.php               ← Wishlist page
├── about.php / contact.php    ← Static pages
│
├── actions/                   ← Form POST handlers (redirect-based)
│   ├── login-process.php
│   ├── register-process.php
│   ├── logout.php
│   ├── cart-action.php        ← Cart AJAX (FormData)
│   ├── checkout-process.php
│   └── contact-process.php
│
├── api/                       ← JSON API endpoints (fetch/AJAX)
│   ├── cart.php               ← Cart AJAX (JSON body)
│   ├── wishlist.php           ← Wishlist toggle
│   └── search.php             ← Search autocomplete
│
├── admin/                     ← Admin panel
│   ├── login.php / logout.php
│   ├── index.php              ← Dashboard
│   ├── products.php           ← Product list
│   ├── product-form.php       ← Add/Edit product
│   ├── categories.php
│   ├── orders.php
│   ├── users.php
│   ├── coupons.php
│   ├── banners.php
│   └── includes/
│       ├── admin-header.php   ← Loads config + db + functions
│       └── admin-footer.php
│
├── includes/                  ← Shared includes for frontend
│   ├── config.php             ← App config, DB constants, starts session
│   ├── db.php                 ← PDO connection ($pdo)
│   ├── functions.php          ← All helper functions
│   ├── header.php             ← HTML head + navbar (loads config+db+functions)
│   ├── footer.php             ← HTML footer + scripts
│   ├── init.php               ← Bootstrap for newer pages
│   ├── session.php            ← Secure session config
│   └── product-card.php       ← Reusable product card partial
│
├── config/
│   ├── constants.php          ← BASE_URL, UPLOAD_PATH, loyalty constants
│   └── database.php           ← Alternate DB config (not used by most pages)
│
├── assets/images/             ← Static bundled images (banners, sarees, users)
├── uploads/products/          ← Admin-uploaded product images
├── js/                        ← JavaScript files
├── css/ / assets/css/         ← Stylesheets
└── database/saree_shop_db.sql ← Database dump
```

### Two Bootstrap Paths

The project has two parallel bootstrap systems due to incremental development:

| Path | Used By | DB Variable | Functions Style |
|------|---------|-------------|-----------------|
| `includes/header.php` → `config.php` + `db.php` + `functions.php` | `shop.php`, `cart.php`, `checkout.php`, most frontend pages | `$pdo` | `snake_case` |
| `includes/init.php` → `session.php` + `constants.php` + `functions.php` | `products.php`, `product-detail.php`, `wishlist.php`, admin `banners.php`, `coupons.php` | `getDB()` | `camelCase` (undefined) |

---

## 2. Database Schema

### `users`
| Column | Type | Description |
|--------|------|-------------|
| `id` | INT PK AUTO_INCREMENT | User identifier |
| `full_name` | VARCHAR(150) | Display name |
| `email` | VARCHAR(150) UNIQUE | Login credential |
| `phone` | VARCHAR(20) | 10-digit mobile |
| `password` | VARCHAR(255) | bcrypt hash |
| `address` | TEXT | Street address |
| `city` | VARCHAR(100) | City |
| `district` | VARCHAR(100) | District |
| `pincode` | VARCHAR(10) | Postal code |
| `loyalty_points` | INT DEFAULT 0 | Accumulated reward points |
| `status` | ENUM('active','blocked') | Account status |
| `created_at` | TIMESTAMP | Registration time |

### `admins`
| Column | Type | Description |
|--------|------|-------------|
| `id` | INT PK | Admin identifier |
| `username` | VARCHAR(50) UNIQUE | Login username |
| `email` | VARCHAR(150) UNIQUE | Admin email |
| `password` | VARCHAR(255) | bcrypt hash |
| `full_name` | VARCHAR(150) | Display name |
| `created_at` | TIMESTAMP | Created time |

### `categories`
| Column | Type | Description |
|--------|------|-------------|
| `id` | INT PK | Category identifier |
| `name` | VARCHAR(100) | Category name |
| `slug` | VARCHAR(100) UNIQUE | URL-friendly name |
| `description` | TEXT | Description |
| `image` | VARCHAR(255) | Category image |
| `status` | ENUM('active','inactive') | Visibility |

### `products`
| Column | Type | Description |
|--------|------|-------------|
| `id` | INT PK | Product identifier |
| `category_id` | INT FK → categories | Category |
| `name` | VARCHAR(200) | Product name |
| `slug` | VARCHAR(220) UNIQUE | URL-friendly name |
| `description` | TEXT | Full description |
| `saree_type` | ENUM | Silk/Cotton/Designer/Bridal/Banarasi/Chiffon/Georgette |
| `price` | DECIMAL(10,2) | Regular price |
| `discount_price` | DECIMAL(10,2) NULL | Sale price |
| `stock` | INT | Available quantity |
| `image` | VARCHAR(255) | Image filename |
| `is_featured` | TINYINT(1) | Show on homepage featured section |
| `is_new_arrival` | TINYINT(1) | Show in new arrivals |
| `status` | ENUM('active','inactive') | Visibility |
| `created_at` | TIMESTAMP | Creation time |

### `cart`
| Column | Type | Description |
|--------|------|-------------|
| `id` | INT PK | Cart row identifier |
| `user_id` | INT FK → users CASCADE | Owner |
| `product_id` | INT FK → products CASCADE | Product |
| `quantity` | INT DEFAULT 1 | Quantity |
| `created_at` | TIMESTAMP | Added time |
| UNIQUE KEY | `(user_id, product_id)` | One row per product per user |

### `orders`
| Column | Type | Description |
|--------|------|-------------|
| `id` | INT PK | Order identifier |
| `user_id` | INT FK → users | Customer |
| `order_number` | VARCHAR(30) UNIQUE | Human-readable (e.g. ES20260529XXXX) |
| `total_amount` | DECIMAL(10,2) | Final amount after discount |
| `discount_amount` | DECIMAL(10,2) | Loyalty discount applied |
| `shipping_address` | TEXT | Delivery address |
| `city`, `district`, `pincode`, `phone` | VARCHAR | Shipping details |
| `payment_method` | ENUM('cod','online') | Payment type |
| `status` | ENUM('pending','confirmed','processing','shipped','delivered','cancelled') | Order lifecycle |
| `notes` | TEXT | Customer notes |
| `created_at` | TIMESTAMP | Order time |

### `order_items`
| Column | Type | Description |
|--------|------|-------------|
| `id` | INT PK | Row identifier |
| `order_id` | INT FK → orders CASCADE | Parent order |
| `product_id` | INT FK → products RESTRICT | Product reference |
| `product_name` | VARCHAR(200) | Snapshot of name at order time |
| `quantity` | INT | Qty ordered |
| `unit_price` | DECIMAL(10,2) | Price at order time |
| `subtotal` | DECIMAL(10,2) | unit_price × quantity |

### `loyalty_points`
| Column | Type | Description |
|--------|------|-------------|
| `id` | INT PK | Record identifier |
| `user_id` | INT FK → users CASCADE | User |
| `order_id` | INT FK → orders SET NULL | Linked order (nullable) |
| `points` | INT | Positive = earned, Negative = redeemed |
| `description` | VARCHAR(255) | Human-readable reason |
| `created_at` | TIMESTAMP | Transaction time |

### `banners`
| Column | Type | Description |
|--------|------|-------------|
| `id` | INT PK | Banner identifier |
| `title` | VARCHAR(200) | Headline text |
| `subtitle` | VARCHAR(255) | Sub-text |
| `image` | VARCHAR(255) | Image filename in `assets/images/banners/` |
| `link_url` | VARCHAR(255) | Click destination |
| `discount_text` | VARCHAR(100) | Badge text (e.g. "30% OFF") |
| `is_active` | TINYINT(1) | Visibility toggle |
| `sort_order` | INT | Display order |

### `coupons` (admin-managed, not yet wired to checkout)
| Column | Type | Description |
|--------|------|-------------|
| `id` | INT PK | Coupon identifier |
| `code` | VARCHAR | Unique coupon code |
| `discount_type` | ENUM('percent','fixed') | Discount calculation method |
| `discount_value` | DECIMAL | Discount amount |
| `min_order` | DECIMAL | Minimum cart total required |
| `max_uses` | INT NULL | Usage limit (NULL = unlimited) |
| `used_count` | INT | Times applied |
| `expires_at` | DATE NULL | Expiry date |
| `is_active` | TINYINT(1) | Active flag |

---

## 3. Configuration & Bootstrap System

### `includes/config.php`

The primary configuration file. Loaded by `includes/header.php` and `admin/includes/admin-header.php`.

**Defines:**
```php
APP_NAME     = 'Elegance Sarees'
APP_URL      = 'http://localhost/EleganceSarees'
BASE_PATH    = dirname(__DIR__)   // Absolute path to project root
DB_HOST      = 'localhost'
DB_USER      = 'root'
DB_PASS      = 'root'
DB_NAME      = 'saree_shop_db'
APP_DEBUG    = true
UPLOAD_PRODUCTS      = BASE_PATH . '/uploads/products/'   // Physical path
UPLOAD_PRODUCTS_URL  = 'uploads/products/'               // Web-relative path
ASSETS_IMAGES        = 'assets/images/'                  // Web-relative path
```

Also **starts the PHP session** if not already started.

### `config/constants.php`

Secondary constants file. Loaded by `includes/init.php` (used by newer pages).

**Defines:**
```php
APP_NAME              = 'EleganceSarees'
APP_TAGLINE           = 'Your Trusted Online Store'
BASE_URL              = '/EleganceSarees'
UPLOAD_PATH           = __DIR__ . '/../uploads/products/'
UPLOAD_URL            = BASE_URL . '/uploads/products/'
ITEMS_PER_PAGE        = 12
LOYALTY_POINTS_PER_DOLLAR = 1
LOYALTY_REDEEM_RATE   = 100
```

### `includes/db.php`

Creates a PDO connection and assigns it to `$pdo` global variable.

```php
$pdo = new PDO('mysql:host=localhost;dbname=saree_shop_db;charset=utf8mb4', DB_USER, DB_PASS, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Throws exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Returns associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                     // Uses real prepared statements
]);
```

On failure, it dies with a setup instruction message rather than exposing credentials.

### `includes/init.php`

Bootstrap for newer-style pages:

```php
require_once __DIR__ . '/session.php';         // Secure session init
require_once __DIR__ . '/../config/constants.php';  // Constants
require_once __DIR__ . '/functions.php';        // All helper functions
```

Note: This path does NOT include `db.php`, so pages using `init.php` must call `getDB()` — a function that is referenced but not defined in the current codebase.

---

## 4. Session Management

### File: `includes/session.php`

Called at the start of every request. Pages using `includes/header.php` reach it via `config.php`; pages using `includes/init.php` include it directly.

### Configurable Constants

Three constants control the session lifecycle. They can be overridden by defining them **before** `session.php` is included:

| Constant | Default | Description |
|----------|---------|-------------|
| `SESSION_IDLE_TIMEOUT` | `1800` (30 min) | Seconds of inactivity before the session is destroyed |
| `SESSION_ABSOLUTE_TTL` | `28800` (8 hours) | Maximum session age regardless of activity |
| `SESSION_REGEN_INTERVAL` | `300` (5 min) | How often the session ID is rotated |

### Cookie Settings

Applied via `session_set_cookie_params()` **before** `session_start()`, so they take effect on the very first cookie issued:

```php
session_set_cookie_params([
    'lifetime' => 0,         // Cookie expires when the browser closes (no persistent cookie)
    'path'     => '/',       // Available across the entire site
    'secure'   => false,     // Set to true when serving over HTTPS
    'httponly' => true,      // Cookie NOT accessible via JavaScript (XSS protection)
    'samesite' => 'Lax',     // Not sent on cross-site POST requests (CSRF protection)
]);
```

`lifetime => 0` means the browser-side cookie dies when the browser closes. Server-side expiry is enforced separately (see below).

### Three-Layer Expiry System

Every request runs three checks in sequence:

#### Layer 1 — Idle Timeout
```php
if (isset($_SESSION['last_activity'])
    && ($now - $_SESSION['last_activity']) > SESSION_IDLE_TIMEOUT) {
    // Full teardown: clear data, expire cookie, destroy file, start fresh
}
```
If `last_activity` is older than 30 minutes, the session is fully destroyed and a new empty one is started. The user will be asked to log in again on their next protected page visit.

#### Layer 2 — Absolute TTL
```php
if (isset($_SESSION['session_start_time'])
    && ($now - $_SESSION['session_start_time']) > SESSION_ABSOLUTE_TTL) {
    // Same full teardown
}
```
Even an active user who never goes idle is force-logged-out after 8 hours. This prevents sessions from living indefinitely through continuous use.

#### Layer 3 — Session ID Rotation (anti-fixation)
```php
if (($now - $_SESSION['_regen_time']) > SESSION_REGEN_INTERVAL) {
    session_regenerate_id(true); // deletes the old session file
    $_SESSION['_regen_time'] = $now;
}
```
Every 5 minutes a new session ID is issued and the old one is deleted server-side. This limits the window an attacker has to exploit a stolen or fixated session ID.

### Timestamp Lifecycle

```
Login / Register
      │
      └── Sets three timestamps in $_SESSION:
              session_start_time  ← never updated (absolute age anchor)
              last_activity       ← updated on EVERY request by session.php
              _regen_time         ← updated when session ID is rotated

Every subsequent request (via session.php):
      ├── Check: now - last_activity  > 1800 → destroy
      ├── Check: now - session_start_time > 28800 → destroy
      └── Update: last_activity = now
```

### Session Variables

| Variable | Set When | Used For |
|----------|----------|----------|
| `$_SESSION['user_id']` | Login / Register | Identifies logged-in customer |
| `$_SESSION['user_name']` | Login / Register | Display name in navbar |
| `$_SESSION['user_email']` | Login / Register | Available reference |
| `$_SESSION['admin_id']` | Admin login | Identifies logged-in admin |
| `$_SESSION['admin_name']` | Admin login | Admin display name |
| `$_SESSION['guest_cart']` | Add to cart (guest) | Stores cart items as array |
| `$_SESSION['flash']` | Any page | One-time notification messages |
| `$_SESSION['session_start_time']` | Login / Register / Admin login | Absolute TTL anchor |
| `$_SESSION['last_activity']` | Every request | Idle timeout tracking |
| `$_SESSION['_regen_time']` | Login / ID rotation | Session ID rotation timer |

---

## 5. User Authentication Flow

### Overview
Standard email + password login. No token-based system (JWT, etc.) — authentication state is maintained entirely through PHP sessions. There is no "Remember Me" or persistent token.

### Step-by-Step Flow

```
User visits login.php
        │
        ▼
login.php renders form
  - Checks is_logged_in() → if true, redirect to index.php
  - Captures ?redirect= query param (for post-login destination)
  - Loads js/validation.js for client-side validation
        │
        ▼ (form submitted via POST)
actions/login-process.php
        │
        ├── 1. Method check: Only POST allowed → else redirect to login.php
        │
        ├── 2. Input sanitization:
        │       $email    = trim($_POST['email'])
        │       $password = $_POST['password']
        │
        ├── 3. Basic validation:
        │       - filter_var($email, FILTER_VALIDATE_EMAIL)
        │       - $password !== ''
        │       → If fails: flash('danger', ...) → redirect to login.php
        │
        ├── 4. Database lookup:
        │       SELECT * FROM users WHERE email = ? AND status = 'active'
        │       (PDO prepared statement — prevents SQL injection)
        │       → If no user found: flash('danger', 'Invalid email or password')
        │         (same message to prevent email enumeration)
        │
        ├── 5. Password verification:
        │       password_verify($password, $user['password'])
        │       → Uses PHP's bcrypt verification (PASSWORD_BCRYPT via PASSWORD_DEFAULT)
        │       → If fails: same 'Invalid email or password' message
        │
        ├── 6. Session creation (on success):
        │       $_SESSION['user_id']    = (int) $user['id']
        │       $_SESSION['user_name']  = $user['full_name']
        │       $_SESSION['user_email'] = $user['email']
        │       // Session timestamps stamped for idle + absolute TTL tracking:
        │       $_SESSION['session_start_time'] = time()
        │       $_SESSION['last_activity']      = time()
        │       $_SESSION['_regen_time']        = time()
        │
        ├── 7. Guest cart merge:
        │       sync_guest_cart_to_db($pdo, $user['id'])
        │       → Moves any items added before login from session to DB cart table
        │
        └── 8. Redirect:
                flash('success', 'Welcome back, ...')
                redirect($redirect)   → defaults to /EleganceSarees/index.php
```

### Password Storage
- Registration: `password_hash($password, PASSWORD_DEFAULT)` → uses bcrypt with cost factor 10
- Verification: `password_verify($plaintext, $hash)` → constant-time comparison (timing-safe)
- The hash is a 60-character bcrypt string: `$2y$10$...`

### No Tokens
This application does not use:
- JWT tokens
- CSRF tokens (no hidden token fields in forms)
- API keys
- "Remember Me" persistent cookies

Authentication is purely session-based. The session ID is transmitted as a cookie (`PHPSESSID`) with `httponly=true`.

### Redirect Safety
```php
// Redirect destination validated to start with /
if (strpos($redirect, '/') !== 0) {
    $redirect = '/EleganceSarees/index.php';
}
```
This prevents open redirect attacks where `redirect` could point to an external URL.

---

## 6. Admin Authentication Flow

### Separate Session Namespace
Admin auth uses `$_SESSION['admin_id']` and `$_SESSION['admin_name']`, completely separate from the user `$_SESSION['user_id']`. This means a logged-in customer is not treated as an admin, and vice versa.

### Step-by-Step Flow

```
Admin visits admin/login.php
        │
        ▼
admin/login.php
  - Loads admin-header.php (which loads config + db + functions)
  - is_admin_logged_in() check → if true, redirect to index.php
  - $isLoginPage = true → skips require_admin() check
        │
        ▼ (form POST to same page)
admin/login.php (POST handler)
        │
        ├── 1. Read inputs:
        │       $username = trim($_POST['username'])
        │       $password = $_POST['password']
        │
        ├── 2. Database lookup:
        │       SELECT * FROM admins WHERE username = ?
        │       (by username, NOT email)
        │
        ├── 3. Password verification:
        │       password_verify($password, $admin['password'])
        │
        ├── 4. Session creation (on success):
        │       $_SESSION['admin_id']   = (int) $admin['id']
        │       $_SESSION['admin_name'] = $admin['full_name']
        │       // Session timestamps stamped for idle + absolute TTL tracking:
        │       $_SESSION['session_start_time'] = time()
        │       $_SESSION['last_activity']      = time()
        │       $_SESSION['_regen_time']        = time()
        │       redirect('index.php')
        │
        └── 5. On failure:
                $loginError = 'Invalid username or password.'
                (renders inline in the form — no flash message used)
```

### Admin Route Protection

Every admin page (except login) calls `require_admin()` via `admin-header.php`:

```php
function require_admin(): void {
    if (!is_admin_logged_in()) {
        // is_admin_logged_in() = !empty($_SESSION['admin_id'])
        $inAdmin = strpos($_SERVER['PHP_SELF'], '/admin/') !== false;
        redirect($inAdmin ? 'login.php' : 'admin/login.php');
    }
}
```

`admin-header.php` enforces this on every page load:
```php
$adminPage = basename($_SERVER['PHP_SELF'], '.php');
$isLoginPage = ($adminPage === 'login');
if (!$isLoginPage) {
    require_admin();
}
```

### Admin Logout

`admin/logout.php` — full session teardown (previously only removed two keys):
```php
// Clear ALL session data (not just admin keys — session file must not linger)
$_SESSION = [];
// Expire the PHPSESSID cookie in the browser
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
// Destroy the server-side session file
session_destroy();
header('Location: login.php');
```

This is a complete teardown. Both admin and user session keys are removed, the cookie is expired in the browser, and the session file is deleted from the server.

---

## 7. User Registration Flow

```
User visits register.php
        │
        ▼
register.php renders form
  - is_logged_in() → if true, redirect to index.php
  - Form fields: full_name, email, phone, password, confirm_password,
                 address, city, district, pincode
  - js/validation.js active: real-time field validation + password strength bar
        │
        ▼ (POST to actions/register-process.php)
actions/register-process.php
        │
        ├── 1. Method check: POST only
        │
        ├── 2. Server-side validation:
        │       full_name:  strlen >= 2
        │       email:      filter_var(FILTER_VALIDATE_EMAIL)
        │       phone:      preg_match('/^[0-9]{10}$/', digits-only)
        │       password:   strlen >= 8
        │                   + /[A-Z]/ (uppercase required)
        │                   + /[a-z]/ (lowercase required)
        │                   + /[0-9]/ (number required)
        │       confirm:    $password === $confirm
        │       → Errors collected in array, flashed and redirected back
        │
        ├── 3. Duplicate email check:
        │       SELECT id FROM users WHERE email = ?
        │       → If found: flash('danger', 'Email already registered.')
        │
        ├── 4. Password hashing:
        │       $hash = password_hash($password, PASSWORD_DEFAULT)
        │       (bcrypt with cost 10 — irreversible one-way hash)
        │
        ├── 5. Database insert:
        │       INSERT INTO users (full_name, email, phone, password,
        │                          address, city, district, pincode)
        │       → Returns new user ID via lastInsertId()
        │
        ├── 6. Auto-login after registration:
        │       $_SESSION['user_id']    = $userId
        │       $_SESSION['user_name']  = $fullName
        │       $_SESSION['user_email'] = $email
        │       // Session timestamps stamped for idle + absolute TTL tracking:
        │       $_SESSION['session_start_time'] = time()
        │       $_SESSION['last_activity']      = time()
        │       $_SESSION['_regen_time']        = time()
        │
        ├── 7. Guest cart merge:
        │       sync_guest_cart_to_db($pdo, $userId)
        │
        └── 8. Redirect to homepage with success message
```

### Client-Side Validation (js/validation.js)

The registration form uses `novalidate` attribute (disables browser native validation) and the custom validator kicks in:

- **On blur** (field loses focus): validates each field individually
- **On submit**: validates all fields, prevents submission if any fail
- **Password strength bar**: scores 0-5 based on length, uppercase, lowercase, numbers, special chars
  - 1 = red (very weak), 5 = green (strong)
- **Confirm password**: checked against `#reg-password` via `data-match` attribute

---

## 8. Logout Flow

Three logout handlers exist. All now perform a complete, identical teardown sequence:

1. `$_SESSION = []` — clear all session variables in memory
2. `setcookie(session_name(), '', time() - 42000, ...)` — expire the PHPSESSID cookie in the browser (sets it to a past timestamp so the browser discards it immediately)
3. `session_destroy()` — delete the session file from the server's filesystem

### Customer Logout — `logout.php` (root, used by navbar)
```php
require_once __DIR__ . '/includes/init.php';
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
header('Location: ' . BASE_URL . '/index.php');
```

### Customer Logout — `actions/logout.php` (alternate, used by some pages)
```php
require_once dirname(__DIR__) . '/includes/config.php';
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
header('Location: ../index.php');
```

### Admin Logout — `admin/logout.php`
```php
require_once dirname(__DIR__) . '/includes/config.php';
$_SESSION = [];   // Clears both admin AND user keys
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
header('Location: login.php');
```

Previously `admin/logout.php` only called `unset($_SESSION['admin_id'], $_SESSION['admin_name'])`, leaving the session file alive on the server and the cookie active in the browser. A shared-computer attacker could have continued the session. This has been fixed to perform the same full teardown as the customer logout handlers.

---

## 9. Product Browsing & Search Flow

### Homepage (`index.php`)

On load:
```sql
SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order
SELECT p.*, c.name AS category_name FROM products p ... WHERE is_featured = 1 AND status = 'active' LIMIT 8
SELECT p.*, c.name AS category_name FROM products p ... WHERE is_new_arrival = 1 AND status = 'active' LIMIT 4
SELECT * FROM categories WHERE status = 'active' LIMIT 5
```

Displays:
- **Hero carousel**: Active banners with Bootstrap 5 carousel
- **Category tiles**: Linked to `shop.php?category={slug}`
- **Featured Sarees section**: `is_featured = 1`
- **New Arrivals section**: `is_new_arrival = 1`

Each product rendered by `render_product_card()` function.

### Shop Page (`shop.php`)

Accepts GET parameters: `category`, `q` (search), `type` (saree type), `filter` (featured/new), `sort`, `min_price`, `max_price`

**Dynamic SQL construction:**
```php
$sql = 'SELECT p.*, c.name AS category_name FROM products p
        JOIN categories c ON c.id = p.category_id
        WHERE p.status = "active"';

// Each filter appends to $sql with bound parameters:
if ($categorySlug)  → AND c.slug = ?
if ($search)        → AND (p.name LIKE ? OR p.description LIKE ? OR p.saree_type LIKE ?)
if ($sareeType)     → AND p.saree_type = ?
if ($filter=featured) → AND p.is_featured = 1
if ($filter=new)    → AND p.is_new_arrival = 1
if ($minPrice)      → AND COALESCE(p.discount_price, p.price) >= ?
if ($maxPrice)      → AND COALESCE(p.discount_price, p.price) <= ?

// Sort:
'newest'     → ORDER BY p.created_at DESC
'price_low'  → ORDER BY COALESCE(p.discount_price, p.price) ASC
'price_high' → ORDER BY COALESCE(p.discount_price, p.price) DESC
'name'       → ORDER BY p.name ASC
```

All parameters are bound via PDO prepared statements — no SQL injection possible.

### Search Autocomplete (`api/search.php`)

JavaScript in `assets/js/main.js` triggers this with a 300ms debounce:

```
User types in search box (2+ chars)
        │
        ▼ fetch('/api/search.php?q=...')
api/search.php
        │
        ├── $q = trim($_GET['q'])
        ├── strlen($q) < 2 → return []
        └── SELECT id, name, price, sale_price FROM products
            WHERE is_active = 1 AND name LIKE '%q%' LIMIT 8
            → Returns JSON array of matches
```

Results displayed as dropdown links pointing to `product-detail.php?id=`.

---

## 10. Product Detail Page Flow

Two separate product detail pages exist (legacy + newer):

### `product.php` (by slug — primary)
```
GET product.php?slug=royal-kanjivaram-silk
        │
        ├── SELECT p.*, c.name ... WHERE p.slug = ? AND p.status = 'active'
        ├── If not found → redirect to shop.php
        ├── Load related products (same category, different ID, LIMIT 4)
        └── Render product image, price, stock, description
            + "Add to Cart" button (triggers cart.js)
            + Related products via render_product_card()
```

### `product-detail.php` (by ID — newer version)
```
GET product-detail.php?id=5
        │
        ├── SELECT p.*, c.name ... WHERE p.id = ? AND p.is_active = 1
        ├── If logged in → recordRecentlyViewed($db, $userId, $id)
        ├── Handle review POST submission
        ├── Load reviews with user names
        ├── Check wishlist status (is this product in user's wishlist?)
        └── Render with:
            - Star rating display
            - Wishlist toggle button (heart icon)
            - Review form (logged-in only)
            - Qty selector + "Add to Cart"
```

### Price Display Logic
```php
$price = $product['discount_price'] ?? $product['price'];
// If discount_price exists: show discount_price (struck-through original)
// Otherwise: show price
```

---

## 11. Cart System Flow

The cart has two modes depending on login state:

### Guest Cart (not logged in)
Stored in `$_SESSION['guest_cart']` as an associative array:
```php
$_SESSION['guest_cart'] = [
    product_id => ['product_id' => X, 'quantity' => Y],
    ...
];
```

### Logged-in Cart
Stored in the `cart` database table. Enforces uniqueness via `UNIQUE KEY (user_id, product_id)`.

### Adding to Cart

Two parallel AJAX endpoints handle add-to-cart:

**`js/cart.js` → `actions/cart-action.php`** (FormData, used on shop pages):
```
User clicks "Add to Cart"
        │
        ├── btn disabled to prevent double-click
        ├── POST FormData to actions/cart-action.php
        │       action=add, product_id=X, quantity=Y
        │
actions/cart-action.php:
        ├── Validates product exists and is active
        ├── Checks stock: quantity <= product.stock
        ├── If logged in:
        │       SELECT existing cart row
        │       If exists: UPDATE quantity (capped at stock)
        │       Else: INSERT new row
        ├── If guest:
        │       Add/update $_SESSION['guest_cart'][product_id]
        └── Returns JSON: {success, message, cart_count}

        ▼
Cart badge in navbar updates: #cart-badge.textContent = cart_count
"Added" feedback shown on button for 1.5 seconds
```

**`assets/js/cart.js` → `api/cart.php`** (JSON body, used by product-detail.php):
```
fetch('/api/cart.php', {method:'POST', body: JSON.stringify({action:'add', ...})})
        │
api/cart.php:
        ├── Reads JSON from php://input
        ├── If not logged in:
        │       Adds to $_SESSION['guest_cart'] (simplified format)
        ├── If logged in:
        │       Same DB logic as actions/cart-action.php
        └── Returns {success, cart_count}
```

### Cart Page (`cart.php`)

```
GET cart.php
        │
        ├── get_cart_items($pdo) → returns items array
        │       If logged in: JOINs cart + products tables
        │       If guest: fetches product details for session cart IDs
        │
        ├── Calculate subtotal:
        │       unit = discount_price ?? price
        │       line_total = unit × quantity
        │       subtotal = sum of all line_totals
        │
        └── Renders cart table + order summary box
            + cart.js bound for qty changes and remove
```

### Updating Quantity
```
User changes qty input → cart.js fires 'change' event
        │
        ├── POST to actions/cart-action.php {action:'update', cart_id, product_id, quantity}
        └── On success: location.reload()
```

### Removing an Item
```
User clicks trash icon → confirm dialog
        │
        ├── POST {action:'remove', cart_id, product_id}
        ├── row.remove() (DOM)
        ├── updateBadge(new_count)
        └── If no more rows: location.reload() (shows empty state)
```

### Guest Cart Sync on Login/Register
```php
function sync_guest_cart_to_db(PDO $pdo, int $userId): void {
    foreach ($_SESSION['guest_cart'] as $productId => $item) {
        // Check if product already in DB cart
        $existing = SELECT id, quantity FROM cart WHERE user_id=? AND product_id=?
        if ($existing) {
            // Merge quantities
            UPDATE cart SET quantity = quantity + ? WHERE id = ?
        } else {
            // Add new row
            INSERT INTO cart (user_id, product_id, quantity)
        }
    }
    unset($_SESSION['guest_cart']);  // Clear session cart after sync
}
```

---

## 12. Checkout & Order Processing Flow

### Prerequisites
- User must be logged in (`require_login()` enforced)
- Cart must not be empty

### Checkout Page (`checkout.php`)

```
GET checkout.php
        │
        ├── require_login() → redirect to login.php if not authenticated
        ├── get_cart_items($pdo) → if empty, redirect to cart.php
        ├── Calculate subtotal (same logic as cart.php)
        ├── Load user's saved address (pre-fills the form)
        ├── get_user_loyalty_total($pdo, $userId) → show points available
        └── Render:
            - Shipping form (address, city, district, pincode, phone)
            - Payment method (COD or Online Demo)
            - Loyalty points checkbox (if points > 0)
            - Order notes textarea
            - Order summary (item list + total)
```

### Order Processing (`actions/checkout-process.php`)

```
POST to actions/checkout-process.php
        │
        ├── 1. Auth check: require_login()
        ├── 2. Method check: POST only
        │
        ├── 3. Shipping validation:
        │       address:  not empty
        │       city:     not empty
        │       district: not empty
        │       pincode:  preg_match('/^[0-9]{5}$/')   — 5 digits
        │       phone:    preg_match('/^[0-9]{10}$/')   — 10 digits
        │       → Failure: flash + redirect to checkout.php
        │
        ├── 4. Re-fetch cart from DB (prevents cart tampering via form):
        │       SELECT c.id, c.quantity, p.id, p.name, p.price, p.discount_price, p.stock
        │       FROM cart JOIN products WHERE user_id = ? AND status = 'active'
        │       → If empty: redirect to cart.php
        │
        ├── 5. Stock validation:
        │       For each item: if quantity > stock → flash + redirect to cart.php
        │
        ├── 6. Subtotal calculation:
        │       price = discount_price ?? price
        │       subtotal += price × quantity
        │
        ├── 7. Loyalty points redemption (if checked):
        │       $points = get_user_loyalty_total($pdo, $userId)
        │       $discount = min(subtotal × 0.10, $points)
        │           (caps at 10% of order value; 1 point = Rs.1)
        │       → Deduct from users.loyalty_points
        │       → Insert negative record into loyalty_points table
        │
        ├── 8. Order number generation:
        │       generate_order_number() = 'ES' + date('Ymd') + substr(uniqid(), -6)
        │       Example: ES20260606A1B2C3
        │
        ├── 9. Database transaction (atomic):
        │       BEGIN TRANSACTION
        │       │
        │       ├── INSERT INTO orders (...) → get $orderId
        │       │
        │       ├── For each cart item:
        │       │       INSERT INTO order_items (order_id, product_id, product_name,
        │       │                                quantity, unit_price, subtotal)
        │       │       UPDATE products SET stock = stock - quantity WHERE id = ?
        │       │       (stock decremented immediately)
        │       │
        │       ├── DELETE FROM cart WHERE user_id = ?
        │       │   (cart cleared after order)
        │       │
        │       ├── award_loyalty_points($pdo, $userId, $orderId, $total)
        │       │
        │       └── COMMIT
        │           On Exception: ROLLBACK → flash error
        │
        └── 10. Redirect to orders.php with success message
```

### Order Number Format
```
ES  +  YYYYMMDD  +  6-char uppercase hex
ES20260606A1B2C3
└─┘  └────────┘  └────────────────────┘
Prefix  Date        Unique suffix from uniqid()
```

### Payment
No real payment gateway is integrated. `payment_method` is stored as `'cod'` (Cash on Delivery) or `'online'` (demo only). No payment processing occurs.

### Order Status Lifecycle
```
pending → confirmed → processing → shipped → delivered
                                              ↘
                                           cancelled
```
Status transitions are manually managed by admin.

---

## 13. Loyalty Points System

### Earning Points

Called inside the checkout transaction:
```php
function award_loyalty_points(PDO $pdo, int $userId, int $orderId, float $orderTotal): void {
    $points = (int) floor($orderTotal / 100);  // 1 point per Rs.100 spent
    if ($points <= 0) return;

    INSERT INTO loyalty_points (user_id, order_id, points, description)
    VALUES (?, ?, ?, 'Points earned on order')

    UPDATE users SET loyalty_points = loyalty_points + ? WHERE id = ?
}
```

**Rate:** Rs.100 spent = 1 loyalty point. Example: Rs.10,999 order → 109 points.

### Redeeming Points

At checkout, if the user checks "Use loyalty points":
```php
$points = get_user_loyalty_total($pdo, $userId);   // From users.loyalty_points
$discount = min($subtotal * 0.1, $points);          // Max 10% of cart total
                                                     // 1 point = Rs.1 discount

// Deduct from balance:
UPDATE users SET loyalty_points = loyalty_points - ? WHERE id = ?

// Record the redemption:
INSERT INTO loyalty_points (user_id, points, description)
VALUES (?, negative_amount, 'Redeemed on checkout')
```

**Capping rule:** The discount cannot exceed 10% of the order subtotal.  
**Rate:** 1 point = Rs.1 discount.

### Points Display
- Profile page: Shows current balance with a badge
- Checkout page: Shows available points and checkbox to apply
- Order history: Shows if loyalty discount was applied to an order

---

## 14. Wishlist Flow

### Adding/Removing from Wishlist (AJAX)

```
User clicks heart icon on product-detail.php
        │
        ├── toggleWishlist(productId, btn) called from main.js
        │
        ├── fetch('/api/wishlist.php', {
        │       method: 'POST',
        │       body: JSON.stringify({product_id: X})
        │   })
        │
api/wishlist.php:
        ├── Check isLoggedIn() → if not: return {redirect: '/login.php'}
        │       (JS then redirects the browser)
        │
        ├── SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?
        │
        ├── If EXISTS: DELETE → return {success: true, added: false}
        └── If NOT EXISTS: INSERT → return {success: true, added: true}

        ▼
JS toggles button style:
  added=true  → btn-danger + filled heart icon
  added=false → btn-outline-danger + empty heart icon
```

### Wishlist Page (`wishlist.php`)

```
GET wishlist.php
        │
        ├── requireLogin() → redirect if not authenticated
        │
        ├── Handle ?remove=product_id:
        │       DELETE FROM wishlist WHERE user_id = ? AND product_id = ?
        │       redirect to wishlist.php
        │
        ├── SELECT p.* FROM wishlist w JOIN products p ON w.product_id = p.id
        │   WHERE w.user_id = ?
        │
        └── Render product cards (includes product-card.php partial)
```

---

## 15. Reviews & Ratings Flow

Implemented in `product-detail.php` only (not in `product.php`).

### Submitting a Review

```
Logged-in user fills star rating + comment on product-detail.php
        │
        ├── JS validates: at least one star must be selected
        │
        └── POST to same page (product-detail.php?id=X)
                with name="submit_review"

product-detail.php POST handler:
        ├── isLoggedIn() check
        ├── $rating = (int) between 1-5
        ├── INSERT INTO reviews (product_id, user_id, rating, comment)
        │   → UNIQUE constraint on (product_id, user_id) — one review per user per product
        │   → PDOException caught if duplicate → "You have already reviewed this product"
        │
        ├── updateProductRating($db, $id)
        │   (Updates avg_rating and review_count on the product record)
        │
        └── redirect to same page (PRG pattern — prevents duplicate on refresh)
```

### Star Rating Input (CSS trick)
Stars are radio inputs in reverse order (5 to 1), hidden with `d-none`. Labels are styled with CSS sibling selectors — clicking a star label checks the corresponding hidden radio input.

### Display
```php
// Reviews loaded newest-first:
SELECT r.*, u.full_name FROM reviews r
JOIN users u ON r.user_id = u.id
WHERE r.product_id = ? ORDER BY r.created_at DESC
```

Rendered as: Name + Stars + Date + Comment text.

---

## 16. User Profile Management

### Profile Page (`profile.php`)

```
GET profile.php
        │
        ├── require_login()
        ├── Load user data: SELECT * FROM users WHERE id = ?
        ├── get_user_loyalty_total($pdo, $userId)
        └── Render form pre-filled with current data
            + loyalty points badge
            + optional password change section
```

### Updating Profile (POST)

```
POST profile.php
        │
        ├── Validation:
        │       full_name: strlen >= 2
        │       phone: 10 digits (after stripping non-digits)
        │
        ├── On success:
        │       UPDATE users SET full_name=?, phone=?, address=?,
        │                        city=?, district=?, pincode=? WHERE id=?
        │       $_SESSION['user_name'] = $fullName   (updates navbar immediately)
        │
        ├── Password change (only if new_password submitted):
        │       1. SELECT password FROM users WHERE id = ?
        │       2. password_verify($currentPass, $hash) → error if wrong
        │       3. strlen($newPass) >= 8 → error if too short
        │       4. UPDATE users SET password = password_hash($newPass) WHERE id = ?
        │
        └── flash('success') + redirect('profile.php')
```

Email cannot be changed (field is rendered as disabled).

---

## 17. Admin — Product Management Flow

### Product List (`admin/products.php`)

```
GET admin/products.php
        │
        ├── require_admin() via admin-header.php
        │
        ├── Handle ?delete=ID:
        │       SELECT image FROM products WHERE id = ?
        │       DELETE FROM products WHERE id = ?
        │       @unlink(uploads/products/image_file)  (deletes uploaded file)
        │       flash + redirect
        │
        └── SELECT p.*, c.name AS category_name FROM products p
            JOIN categories c ON c.id = p.category_id
            ORDER BY p.id DESC
```

### Add/Edit Product (`admin/product-form.php`)

```
GET admin/product-form.php         → new product form
GET admin/product-form.php?id=X    → edit existing product

POST admin/product-form.php
        │
        ├── 1. Read all form fields:
        │       name, category_id, description, saree_type,
        │       price, discount_price, stock, status,
        │       is_featured (checkbox), is_new_arrival (checkbox)
        │
        ├── 2. Slug generation:
        │       $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $name))
        │       Example: "Royal Kanjivaram Silk" → "royal-kanjivaram-silk"
        │
        ├── 3. Server-side validation:
        │       name:       strlen >= 2
        │       category_id: > 0
        │       price:       > 0
        │       saree_type:  in_array(['Silk','Cotton','Designer','Bridal','Banarasi','Chiffon','Georgette'])
        │
        ├── 4. Image upload (if file provided):
        │       - Validate extension: jpg, jpeg, png, webp
        │       - Generate filename: {slug}_{timestamp}.{ext}
        │       - Upload destination: EleganceSarees/uploads/products/
        │       - move_uploaded_file($tmp, $dest)
        │       - On failure: add to errors array
        │       - If no new image + editing: keep existing image
        │
        ├── 5. On success:
        │       INSERT INTO products (...) for new
        │       UPDATE products SET ... WHERE id = ? for edit
        │
        └── redirect to products.php
```

### Image Filename Convention
```
{url-slug}_{unix-timestamp}.{extension}
Example: royal-kanjivaram-silk_1780026667.jpg
```

---

## 18. Admin — Category Management Flow

```
admin/categories.php handles all CRUD on same page:

GET ?delete=ID:
    - Check: SELECT COUNT(*) FROM products WHERE category_id = ?
    - If products exist: flash error (cannot delete with products)
    - Else: DELETE FROM categories WHERE id = ?

GET ?edit=ID:
    - Load category data into edit form

POST (create or update based on hidden 'id' field):
    - name: strlen >= 2
    - slug: auto-generated from name
    - status: active/inactive
    - id == 0: INSERT
    - id > 0: UPDATE
```

---

## 19. Admin — Order Management Flow

### Order List

All orders shown newest-first with customer name, total, payment method, status, date.

### View Order Detail

```
GET admin/orders.php?id=X
        │
        ├── SELECT order + user details WHERE o.id = ?
        ├── SELECT order items WHERE order_id = ?
        └── Render order detail card + status update form
```

### Update Order Status

```
POST admin/orders.php
        │
        ├── $orderId = (int) $_POST['order_id']
        ├── $status validated against allowed values:
        │       ['pending','confirmed','processing','shipped','delivered','cancelled']
        └── UPDATE orders SET status = ? WHERE id = ?
```

No automated email notifications are sent on status change.

---

## 20. Admin — User Management Flow

**Actions available:**

| Action | URL | Effect |
|--------|-----|--------|
| View all | `users.php` | Lists all users |
| Edit | `?edit=ID` | Loads edit form (name, phone, loyalty points) |
| Toggle status | `?toggle=ID` | Flips active ↔ blocked |
| Delete | `?delete=ID` | Hard-deletes user record |

**Blocking a user:**  
Sets `users.status = 'blocked'`. The login process checks `AND status = 'active'`, so blocked users cannot log in.

**Editing via POST:**
```php
UPDATE users SET full_name=?, phone=?, loyalty_points=? WHERE id=?
```
Admin can manually adjust loyalty points balance.

---

## 21. Admin — Banner Management Flow

```
admin/banners.php

GET ?delete=ID:
    DELETE FROM banners WHERE id = ?

POST (add new banner):
    INSERT INTO banners (title, subtitle, link_url, sort_order, is_active, image)
    Image filename is stored as text reference (no file upload in current implementation)
    Default image: 'banner1.svg'
```

Banners displayed on homepage as Bootstrap carousel slides. The `sort_order` field determines carousel sequence. Only `is_active = 1` banners are shown.

---

## 22. Admin — Coupon Management

The coupon CRUD UI is fully implemented in `admin/coupons.php`. The `coupons` table stores:

- **Percent discounts**: e.g. 15% off entire order
- **Fixed amount discounts**: e.g. Rs.500 off
- **Minimum order requirement**
- **Usage limits** (max_uses, used_count tracked)
- **Expiry dates**

**Current status:** The coupon system is NOT connected to the checkout process. `actions/checkout-process.php` does not accept or validate coupon codes. This is infrastructure-ready but incomplete — the checkout form and process would need a coupon code input field and validation logic.

---

## 23. Admin — Dashboard & Analytics

### Metrics (`admin/index.php`)

```sql
-- Real-time counts:
SELECT COUNT(*) FROM products
SELECT COUNT(*) FROM orders
SELECT COUNT(*) FROM users
SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status != 'cancelled'

-- Last 6 months sales trend:
SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, SUM(total_amount) AS total
FROM orders WHERE status != 'cancelled'
GROUP BY month ORDER BY month DESC LIMIT 6

-- Orders by status (for donut chart):
SELECT status, COUNT(*) AS cnt FROM orders GROUP BY status
```

### Charts (Chart.js v4)

Data is injected server-side into a JavaScript object:
```php
window.adminChartData = {
    months: [...],
    sales: [...],
    statuses: [...],
    statusCounts: [...]
};
```

`js/admin-charts.js` reads this on `DOMContentLoaded` and renders:
- **Bar chart**: Monthly sales (last 6 months), Rs. formatted y-axis
- **Donut chart**: Orders by status with color coding

---

## 24. Contact Form Flow

```
GET contact.php → renders form with id="contactForm"

js/validation.js validates:
    - Email format via regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    - Prevents submit if invalid

POST to actions/contact-process.php:
    ├── Validation:
    │       name: strlen >= 2
    │       email: FILTER_VALIDATE_EMAIL
    │       message: strlen >= 10
    │
    └── No email is actually sent (demo placeholder)
        flash('success', 'Thank you! We will respond soon.')
        redirect to contact.php
```

In a production deployment, the handler would call `mail()` or an SMTP library here.

---

## 25. Helper Functions Reference

All defined in `includes/functions.php`.

### Output & Utilities

| Function | Signature | Description |
|----------|-----------|-------------|
| `e()` | `(string): string` | `htmlspecialchars()` with ENT_QUOTES + UTF-8. Use on all user-generated output. |
| `redirect()` | `(string): void` | `header('Location: ...')` + `exit` |
| `flash()` | `(string $type, string $msg): void` | Stores message in `$_SESSION['flash']` |
| `get_flash()` | `(): ?array` | Reads and clears flash message |
| `format_price()` | `(float): string` | Returns `Rs.X,XXX.XX` formatted string |
| `generate_order_number()` | `(): string` | `ES` + date + 6-char uniqid suffix |

### Authentication

| Function | Description |
|----------|-------------|
| `is_logged_in()` | Returns `!empty($_SESSION['user_id'])` |
| `is_admin_logged_in()` | Returns `!empty($_SESSION['admin_id'])` |
| `require_login()` | Redirects to login.php if not logged in |
| `require_admin()` | Redirects to admin/login.php if not admin |

### Product Images

| Function | Description |
|----------|-------------|
| `product_image_url(?string $filename)` | Resolves image filename to web-relative URL. Checks `uploads/products/` first, then `assets/images/sarees/` and `assets/images/products/`, falls back to `default_saree.jpg` |
| `productImageUrl(?string $filename)` | camelCase alias of above (used by product-detail.php, product-card.php) |

### Cart

| Function | Description |
|----------|-------------|
| `get_cart_count(PDO)` | Returns total item quantity (DB for logged-in, session for guest) |
| `get_cart_items(PDO)` | Returns full cart array with product details |
| `sync_guest_cart_to_db(PDO, int $userId)` | Merges session cart into DB on login |

### Loyalty

| Function | Description |
|----------|-------------|
| `award_loyalty_points(PDO, $userId, $orderId, $total)` | Calculates and records points earned |
| `get_user_loyalty_total(PDO, $userId)` | Returns current points balance from `users.loyalty_points` |

### Rendering

| Function | Description |
|----------|-------------|
| `render_product_card(array $product)` | Returns HTML string for a product card (used on shop, homepage, search results) |

---

## 26. JavaScript Modules

### `js/cart.js` (used by shop.php, cart.php)

Communicates with `actions/cart-action.php` using `FormData`:

- **Add to cart**: `.add-to-cart-btn` click → POST `action=add`
- **Update quantity**: `.cart-qty-input` change → POST `action=update` → `location.reload()`
- **Remove item**: `.remove-cart-item` click → confirm → POST `action=remove` → DOM row removal

### `assets/js/cart.js` (used by product-detail.php)

Communicates with `api/cart.php` using JSON:

- Same add/update/remove operations but different endpoint and data format
- Uses `BASE_URL` global variable

### `js/main.js` / `assets/js/main.js`

- `js/main.js`: Auto-dismisses flash alerts after 5 seconds
- `assets/js/main.js`: Search autocomplete with 300ms debounce, star rating hover highlight, `toggleWishlist()` function

### `js/validation.js` / `assets/js/validation.js`

Two versions exist. The version in `assets/js/` is more comprehensive:
- Password strength bar (5-level score)
- Per-field validation on blur
- Full form validation on submit
- Supports `data-validate="email|phone|password|confirm"` attributes
- `data-match="#selector"` for password confirmation

The version in `js/` is simpler — separate listeners per form ID.

### `js/admin-charts.js`

- Reads `window.adminChartData` injected by `admin/index.php`
- Creates Chart.js bar chart (monthly sales) and doughnut chart (order status)

---

## 27. Security Model

### Input Validation
- All form inputs validated server-side before database operations
- Client-side validation is a UX enhancement only — server-side is authoritative

### SQL Injection Prevention
- 100% PDO prepared statements with bound parameters
- No raw string interpolation in SQL queries

### XSS Prevention
- All output wrapped in `e()` (htmlspecialchars) before rendering to HTML
- This applies to user-provided data, database values, and URL parameters

### Password Security
- `password_hash($password, PASSWORD_DEFAULT)` → bcrypt, cost factor 10
- `password_verify()` → constant-time comparison
- Passwords never logged or stored in plain text

### Session Security
- `httponly=true` — session cookie inaccessible to JavaScript (XSS protection)
- `samesite=Lax` — CSRF protection for cross-site requests
- **Idle timeout**: session destroyed after 30 minutes of inactivity (`SESSION_IDLE_TIMEOUT`)
- **Absolute TTL**: session destroyed after 8 hours regardless of activity (`SESSION_ABSOLUTE_TTL`)
- **Session ID rotation**: new ID issued every 5 minutes, old file deleted (`SESSION_REGEN_INTERVAL`)
- `session_regenerate_id(true)` — old session file deleted immediately on rotation
- Admin and user sessions use separate keys — no privilege escalation possible
- All three logout handlers perform a complete teardown: clear data → expire cookie → destroy file

### Authentication Checks
- All protected pages call `require_login()` or `require_admin()` at the top
- Admin routes protected globally by `admin-header.php`

### Open Redirect Prevention
- Login redirect parameter validated to start with `/`

### File Upload Security
- Extension whitelist: `jpg, jpeg, png, webp` only
- No executable file types accepted
- Upload directory is outside web root content scope (under `uploads/products/`)

### Missing Security Measures
- **No CSRF tokens**: Form submissions don't include hidden CSRF tokens
- **No rate limiting**: No protection against brute-force login attempts
- `APP_DEBUG = true` in production config (should be `false`)

---

## 28. Known Issues & Notes

### ~~Session ID Not Expiring~~ — FIXED
**Previously:** `session.php` only tracked when the session ID was last regenerated (`$_SESSION['created']`), not when the user last did anything. There was no idle timeout and no absolute TTL — a logged-in user would stay authenticated indefinitely as long as the browser kept the cookie.

**Fixed:** Three-layer expiry now enforced on every request:
- **Idle timeout** (30 min) via `$_SESSION['last_activity']` updated per request
- **Absolute TTL** (8 hours) via `$_SESSION['session_start_time']` set at login
- **Session ID rotation** (every 5 min) via `$_SESSION['_regen_time']`

All three timestamps are stamped at login, registration, and admin login. All three logout handlers now perform a full teardown (clear data + expire cookie + destroy file).

### ~~Incomplete Admin Logout~~ — FIXED
**Previously:** `admin/logout.php` only called `unset($_SESSION['admin_id'], $_SESSION['admin_name'])`, leaving the session file alive on the server and the cookie active in the browser.

**Fixed:** Admin logout now performs the same full teardown as customer logout.

### ~~`actions/logout.php` Re-started Session After Destroy~~ — FIXED
**Previously:** Called `session_destroy()` then immediately `session_start()` without expiring the browser cookie, effectively undoing the destroy.

**Fixed:** Proper sequence — clear `$_SESSION`, expire cookie, destroy file.

### Dual Naming Convention (Active Bug Risk)
Pages using `includes/init.php` call functions with camelCase names (`isLoggedIn()`, `getDB()`, `requireLogin()`, `formatPrice()`, `renderStars()`, `getProductPrice()`, `recordRecentlyViewed()`, `updateProductRating()`) that are not defined in `functions.php`. These pages will throw fatal errors:
- `products.php`
- `product-detail.php`
- `wishlist.php`
- `order-confirmation.php`
- `admin/banners.php`
- `admin/coupons.php`

### Two Config Files
`includes/config.php` and `config/constants.php` define overlapping constants with different names. Pages must be aware of which bootstrap path they use. `config.php` now defers full session initialisation to `session.php` — it only calls a bare `session_start()` as a fallback for pages that reach it without going through `session.php` first.

### Missing DB Columns
`products.avg_rating`, `products.review_count`, `products.sale_price`, `products.is_active` are referenced in newer pages but not in the SQL dump schema. These columns only exist if the database was updated manually after the dump.

### Missing DB Tables
`reviews`, `wishlist` tables not in the SQL dump. These must be created manually.

### Coupon Codes Not Wired to Checkout
The `coupons` table and admin UI exist, but checkout does not accept or apply coupon codes.

### No Email Notifications
Order confirmations, status updates, registration welcome emails — none are sent. The contact form also discards messages rather than emailing them.

### Demo Payment Only
No real payment gateway. "Online Payment" option is a placeholder.

### No CSRF Tokens
Form submissions do not include hidden CSRF tokens. All state-changing forms (login, register, checkout, profile update, admin actions) are vulnerable to cross-site request forgery.

### No Brute-Force Protection
No rate limiting or account lockout on the login forms. An attacker can attempt unlimited password guesses.

### `APP_DEBUG = true` in Config
Should be set to `false` in production to suppress detailed error output.

---

*Documentation updated June 2026 — Session expiry, idle timeout, and logout security fixes applied*
