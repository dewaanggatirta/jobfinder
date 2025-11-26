JobFinder - XAMPP Ready
=======================

1. Extract this folder to your XAMPP htdocs directory, e.g. C:\xampp\htdocs\jobfinder  (or /opt/lampp/htdocs/jobfinder)
2. Create folder 'uploads' inside project root and make it writable.
3. Start Apache & MySQL in XAMPP.
4. Import `database.sql` via phpMyAdmin (http://localhost/phpmyadmin).
5. Open http://localhost/jobfinder/ in your browser.

Admin access:
 - URL: http://localhost/jobfinder/admin/login.php
 - Username: admin
 - Password: admin123

Notes:
 - Change config.php if your DB credentials differ.
 - This is a simple starter project. For production, add CSRF, input validation, HTTPS, and file scanning.
