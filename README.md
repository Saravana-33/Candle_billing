# Candle Billing System

PHP + MySQL billing app intended for XAMPP/WAMP.

## Install
1. Copy this `billing` folder into your XAMPP `htdocs` directory.
2. Start Apache and MySQL, then import `database.sql` in phpMyAdmin.
3. Copy `config.example.php` to `config.php` and set the database and shop values in the environment used by Apache. See the variable names in the example file.
4. Set `APP_ADMIN_USERNAME` and `APP_ADMIN_PASSWORD` in Apache's environment before the first visit. Use a unique password of at least 12 characters. The app creates that admin account if it does not already exist.
5. Open `http://localhost/billing/login.php`.

Do not commit `config.php` or real customer data. The app includes a dashboard, new bills, search, payments, customer explorer, profit analytics, and printable invoices. Chart.js is loaded from a CDN, so the revenue charts need internet access.
