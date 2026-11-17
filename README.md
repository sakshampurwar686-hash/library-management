# Library Management System (PHP + MySQL + Bootstrap 5)

## Structure
```
library-management/
├── index.php              (redirects to admin login)
├── admin/                 login, logout, dashboard, books, students, issue, return, reports
├── assets/css/style.css
├── includes/              db.php, auth.php, header.php, footer.php
└── database/library_db.sql
```

## Run with XAMPP
1. Install XAMPP and start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Copy the `library-management` folder into `C:\xampp\htdocs\` (Linux: `/opt/lampp/htdocs/`).
3. Open http://localhost/phpmyadmin → **Import** → choose `database/library_db.sql` → Go.
4. If your MySQL has a password, edit credentials in `includes/db.php`.
5. Open http://localhost/library-management/ and log in:
   - Email: `admin@library.com`
   - Password: `admin123`

Change the admin password after first login. Bootstrap loads from a CDN, so an internet connection is needed.
