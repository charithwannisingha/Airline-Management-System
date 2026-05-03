# ✈️ Nexus Airlines | Airline Management System (AMS)

![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=flat-square&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-Custom_UI-E34F26?style=flat-square&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-Glassmorphism-1572B6?style=flat-square&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?style=flat-square&logo=javascript&logoColor=black)

A modern, robust, and fully responsive **Airline Management System** built for **Nexus Airlines**. It provides a premium user experience with a dual-role interface (Admin & Passenger), handling everything from dynamic pricing to interactive seat selection.

---

## 📁 Project Structure

```text
ams/
├── index.php          🔑 Login page (Glassmorphism UI)
├── dashboard.php      📊 Admin dashboard (KPIs, live flights, Chart.js)
├── flights.php        ✈️ Flight search (Dynamic routing)
├── booking.php        💺 Seat selection + passenger form
├── ticket.php         🎫 E-ticket / boarding pass generation
│
├── css/
│   └── style.css      🎨 All styles (CSS variables, Dark theme, responsive)
│
├── js/
│   └── app.js         ⚡ UI interactions, seat map logic, formatting
│
├── php/
│   ├── login.php      🔐 Login handler (POST)
│   ├── logout.php     🚪 Session destroy
│   └── book.php       💳 Booking processor (POST)
│
├── includes/
│   ├── db.php         🛢️ PDO database connection
│   ├── auth.php       🛡️ Session helpers, requireLogin(), isAdmin()
│   └── sidebar.php    🧭 Navigation sidebar partial
│
└── ams_db.sql         💾 Full database schema + demo data

⚙️ Setup Instructions
1. Requirements
PHP: 8.0 or higher

Database: MySQL 5.7+ / MariaDB 10+

Server: Apache / Nginx (XAMPP, WAMP, Laragon)

2. Database Setup
-- Import the SQL file via phpMyAdmin or MySQL shell:
SOURCE /path/to/ams/ams_db.sql;

3. Configure DB Connection
Edit the includes/db.php file to match your local database credentials:
define('DB_HOST', 'localhost');
define('DB_NAME', 'ams_db');
define('DB_USER', 'root');
define('DB_PASS', '');          // Enter your MySQL password here

4. Place in Web Root
Copy the entire ams/ folder into your local server directory:

XAMPP: C:/xampp/htdocs/ams/

WAMP: C:/wamp64/www/ams/

Linux: /var/www/html/ams/

5. Access the System
Open your web browser and navigate to:
http://localhost/ams/

🔐 Demo Login Credentials
Use the following credentials to explore the system:

Role,Username,Password
👑 Admin,admin,admin123
👤 Passenger,user,user123

Note: Passwords are stored securely as bcrypt hashes. To generate a new hashed password, run:

php -r "echo password_hash('your_password', PASSWORD_DEFAULT);"

🔧 Technology Stack

Layer,Technology
Frontend,"HTML5, CSS3 (Custom UI, Glassmorphism), Vanilla JS"
Backend,"PHP 8+ (PDO, Sessions, bcrypt encryption)"
Database,"MySQL (ACID-compliant, InnoDB engine)"
Fonts,Google Fonts (Syne + DM Sans)
Security,"HTTPS ready, PDO prepared statements (SQLi proof), XSS escaping"

📄 Pages Summary

Page,Access,Description
index.php,Public,Secure login with a premium dark-theme UI
dashboard.php,Admin,"Real-time KPIs, revenue charts, recent bookings"
flights.php,All Users,Flight search with real-time dynamic pricing
booking.php,All Users,Interactive seat map + passenger details
ticket.php,All Users,Downloadable E-ticket and boarding pass

🗄️ Database Tables 

Table,Description
users,Passenger and Admin accounts (Secure authentication)
aircraft,"Fleet details, capacity, and maintenance status"
flights,"Scheduled routes, departure times, and base fares"
bookings,"Reservations, seat numbers, and payment status"
crew,"Crew roster, roles, and availability"
flight_crew,Specific crew assignments per scheduled flight

Developed with ❤️ by Charith Wannisingha


