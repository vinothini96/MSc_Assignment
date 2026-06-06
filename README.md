# Elegance Sarees – Online Saree Shopping Web Application

A full-stack e-commerce website for a saree shop built with **HTML5**, **CSS3**, **Vanilla JavaScript**, **Core PHP**, and **MySQL**. 

## Features

### Customer
- Registration & login with password validation
- Browse sarees by category, search, and filters (type, price, sort)
- Product details, add to cart, update quantity, remove items
- Guest cart (session) and logged-in cart (database)
- Checkout, place orders, order history
- Loyalty points (earn on purchase, redeem at checkout)
- Featured products, new arrivals, promotional banners

### Admin
- Dashboard with statistics and Chart.js charts
- CRUD: Products, Categories, Users, Orders
- Order status updates
- Product image upload

## Tech Stack

| Layer    | Technology        |
|----------|-------------------|
| Frontend | HTML, CSS, JS     |
| UI       | Bootstrap 5       |
| Backend  | Core PHP (PDO)    |
| Database | MySQL             |

## Requirements

- PHP 8.0+ with PDO MySQL extension
- MySQL 5.7+ / MariaDB
- Apache (XAMPP, WAMP, or Laragon)
- PHP GD extension (for placeholder image generation)

## Installation (XAMPP / WAMP)

### 1. Copy project

Place this folder in your web root:

- **XAMPP:** `C:\xampp\htdocs\EleganceSarees`
- **WAMP:** `C:\wamp64\www\EleganceSarees`

### 2. Create database

1. Start Apache and MySQL from XAMPP/WAMP Control Panel.
2. Open phpMyAdmin: `http://localhost/phpmyadmin`
3. Import the SQL file: `database/saree_shop_db.sql`
   - Or run: `mysql -u root < database/saree_shop_db.sql`

### 3. Configure database connection

Edit `includes/config.php` if your MySQL credentials differ:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'saree_shop_db');
```

Update `APP_URL` if your folder name/path is different:

```php
define('APP_URL', 'http://localhost/EleganceSarees');
```

### 4. Run setup (images + passwords)

Open in browser:

```
http://localhost/EleganceSarees/setup/install.php
```

This will:
- Create local placeholder product/banner images
- Set demo passwords (see below)

### 5. Access the site

| Area   | URL |
|--------|-----|
| Store  | `http://localhost/EleganceSarees/` |
| Admin  | `http://localhost/EleganceSarees/admin/login.php` |

## Demo Credentials

| Role   | Username / Email       | Password   	   |
|--------|------------------------|------------    |
| Admin  | `admin`                | `Admin@123`    |
| User   | `priya@example.com`    | `User@123`     |
| User   | `vinothini.s@gmail.com`| `Vinothini96#` |

> Run `setup/install.php` after importing SQL so passwords work correctly.

## Project Structure

```
EleganceSarees/
├── index.php              # Home
├── shop.php               # Collection / filters
├── product.php            # Product details
├── cart.php               # Shopping cart
├── checkout.php           # Checkout
├── login.php / register.php
├── profile.php / orders.php
├── about.php / contact.php
├── admin/                 # Admin panel
│   ├── index.php          # Dashboard
│   ├── products.php
│   ├── product-form.php
│   ├── categories.php
│   ├── users.php
│   ├── orders.php
│   └── login.php
├── actions/               # Form & cart handlers
├── includes/              # config, db, header, footer, functions
├── css/style.css
├── js/                    # validation, cart, charts
├── assets/images/         # Local saree & banner images
├── uploads/products/      # Admin uploaded images
├── database/saree_shop_db.sql
└── setup/install.php
```

## Database Tables

- `users`, `admins`, `products`, `categories`
- `cart`, `orders`, `order_items`, `loyalty_points`, `banners`

All queries use **PDO prepared statements** to prevent SQL injection.

## Security Notes

- Passwords hashed with `password_hash()` / `password_verify()`
- Server-side validation on all forms
- Client-side validation in `js/validation.js`
- Admin routes protected with session checks

## Pages Included (15+)

1. Home  
2. About Us  
3. Contact Us  
4. Shop / Collection  
5. Product Details  
6. Shopping Cart  
7. Checkout  
8. User Login  
9. User Registration  
10. User Profile  
11. Order History  
12. Admin Login  
13. Admin Dashboard  
14. Product Management  
15. Category Management  
(+ Users & Orders management in admin)

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Database connection failed | Import SQL, check `includes/config.php` |
| Images not showing | Run `setup/install.php` |
| Login fails | Run `setup/install.php` to reset passwords |
| 404 on pages | Ensure project is in `htdocs` and Apache is running |


