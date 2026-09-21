# Farm Link

A web-based system that links farmers directly to buyers, built from the
functional requirements in Chapter 3 (3.6.1) and the design in Chapter 4
of the final year project (register/list produce, browse/search/order,
admin manages accounts and listings).

**Stack:** PHP (PDO/MySQL) + vanilla HTML/CSS/JavaScript. No frameworks
required — this matches the "PHP + relational database" justification
in section 3.7 of the project.

## Requirements

- PHP 8.0+ with the `pdo_mysql` extension
- MySQL or MariaDB
- Apache or Nginx (or PHP's built-in server for local testing)

## Setup

1. **Create the database.** Import the schema:
   ```
   mysql -u root -p < sql/schema.sql
   ```
2. **Set your DB credentials** in `includes/db.php` (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`).
3. **Seed the admin account.** Start the server, then visit `install.php`
   once in your browser. It creates:
   - Email: `admin@farmlink.com`
   - Password: `Admin@123`

   **Delete `install.php` after running it once.**
4. Make the `uploads/` folder writable (`chmod 755 uploads`) — product
   photos are stored there.

### Run locally with PHP's built-in server

```
php -S localhost:8000
```
Then open `http://localhost:8000/index.php`.

## Project structure

```
index.php                Landing page + featured produce
register.php / login.php / logout.php   Auth (farmer/buyer signup)
install.php              One-time admin seed script (delete after use)

farmer/
  dashboard.php           Stats + quick links
  add_product.php         Add produce listing (Fig. 4.1 input design)
  my_products.php         List/edit/delete own produce
  edit_product.php
  delete_product.php
  orders.php               Pending / Completed deliveries (Fig. 4.2.2 output design)

buyer/
  browse.php              Search + filter produce by category
  product.php             Product detail + place order
  orders.php              Buyer's order history

admin/
  dashboard.php           System-wide stats
  manage_users.php        Suspend / activate / delete farmers & buyers
  manage_products.php     Monitor & remove listings

includes/
  db.php, auth.php, flash.php, header.php, footer.php

css/style.css             All styling
js/script.js              Client-side search filter, live order total, confirmations
sql/schema.sql             Database schema
uploads/                  Product photo uploads (writable)
```

## Notes for your write-up

- **Functional requirements (3.6.1)** map directly to page groups: farmer
  registration & listing management, buyer registration & ordering, and
  admin account/listing management.
- **Security:** passwords are hashed with PHP's `password_hash()`;
  all queries use PDO prepared statements to prevent SQL injection;
  output is escaped with `htmlspecialchars()` to prevent XSS.
- **Input Design (Fig. 4.1)** — the Add Product form fields (company
  name, phone, category, product, price, description) match exactly.
- **Output Design (Fig. 4.2.2)** — Pending/Completed deliveries and
  "My Products" tables are implemented in `farmer/orders.php` and
  `farmer/my_products.php`.
