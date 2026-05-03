# ✈ Airlines Management System (AMS)
### Hardy ATI Ampara · AMP/IT/2324/F/104
**Author:** K.G.S.H. Madumali | **Supervisor:** Mr. A.C. Aasik | **Year:** 2023/2024

---

## 📁 Project Structure

```
ams/
├── index.php          ← Login page
├── dashboard.php      ← Admin dashboard (KPIs, live flights)
├── flights.php        ← Flight search
├── booking.php        ← Seat selection + passenger form
├── ticket.php         ← E-ticket / boarding pass
│
├── css/
│   └── style.css      ← All styles (CSS variables, layout, components)
│
├── js/
│   └── app.js         ← UI interactions, seat map, card formatting
│
├── php/
│   ├── login.php      ← Login handler (POST)
│   ├── logout.php     ← Session destroy
│   └── book.php       ← Booking processor (POST)
│
├── includes/
│   ├── db.php         ← PDO database connection
│   ├── auth.php       ← Session helpers, requireLogin(), isAdmin()
│   └── sidebar.php    ← Navigation sidebar partial
│
└── ams_db.sql         ← Full database schema + demo data
```

---

## ⚙️ Setup Instructions

### 1. Requirements
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10+
- Apache / Nginx (XAMPP, WAMP, Laragon)

### 2. Database Setup
```sql
-- In phpMyAdmin or MySQL shell:
SOURCE /path/to/ams/ams_db.sql;
```

### 3. Configure DB Connection
Edit `includes/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'ams_db');
define('DB_USER', 'root');
define('DB_PASS', '');          // your MySQL password
```

### 4. Place in Web Root
Copy the `ams/` folder into:
- XAMPP: `C:/xampp/htdocs/ams/`
- WAMP:  `C:/wamp64/www/ams/`
- Linux: `/var/www/html/ams/`

### 5. Access
```
http://localhost/ams/
```

---

## 🔐 Demo Login Credentials

| Role      | Username | Password  |
|-----------|----------|-----------|
| Admin     | admin    | admin123  |
| Passenger | user     | user123   |

> **Note:** Passwords are stored as bcrypt hashes. Regenerate with:
> ```bash
> php -r "echo password_hash('admin123', PASSWORD_DEFAULT);"
> ```

---

## 🔧 Technology Stack

| Layer     | Technology                    |
|-----------|-------------------------------|
| Frontend  | HTML5, CSS3 (Custom, no Bootstrap needed), Vanilla JS |
| Backend   | PHP 8 (PDO, Sessions, bcrypt) |
| Database  | MySQL (ACID-compliant, InnoDB)|
| Fonts     | Google Fonts (Syne + DM Sans) |
| Security  | HTTPS/SSL ready, PDO prepared statements (SQL injection proof), XSS escaping |

---

## 📄 Pages Summary

| Page          | Access    | Description                        |
|---------------|-----------|------------------------------------|
| index.php     | Public    | Login with animated sky background |
| dashboard.php | Admin     | KPIs, live flights, recent bookings|
| flights.php   | All users | Flight search with dynamic results |
| booking.php   | All users | Seat map + passenger + payment     |
| ticket.php    | All users | E-ticket / boarding pass + print   |

---

## 🗄️ Database Tables

| Table         | Description                        |
|---------------|------------------------------------|
| users         | Passengers + admin accounts        |
| aircraft      | Fleet details + maintenance status |
| flights       | Scheduled flights + pricing        |
| bookings      | Reservations + passenger details   |
| crew          | Crew roster + availability         |
| flight_crew   | Crew assignments per flight        |

---

*Hardy ATI Ampara · HND in Information Technology · 2023/2024*
