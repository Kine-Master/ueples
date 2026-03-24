# UEP LES Scheduling System

Web-based faculty scheduling and workload management system for UEP LES.

## Tech Stack
- Frontend: HTML, CSS, JavaScript
- Backend: Native PHP
- Database: MySQL or MariaDB
- Web server: Apache (XAMPP on Windows, Apache2 on Linux)

## Requirements
- PHP 8.0+ with `pdo_mysql`
- MySQL 8+ or MariaDB 10+
- Apache 2.4+
- Git

## Environment Configuration
The project now reads database settings from `.env`.

1. Copy `.env.example` to `.env`.
2. Update database values.

Required keys:
- `UEP_DB_HOST`
- `UEP_DB_PORT`
- `UEP_DB_NAME`
- `UEP_DB_USER`
- `UEP_DB_PASS`
- `UEP_TIMEZONE`

## Windows Setup (XAMPP)

1. Clone the repository into `C:\xampp\htdocs`.
```bash
cd C:\xampp\htdocs
git clone https://github.com/Kine-Master/ueples-scheduling-system.git
```

2. Create env file.
```bash
cd C:\xampp\htdocs\ueples-scheduling-system
copy .env.example .env
```

3. Edit `.env` and set your database credentials.

4. Start services in XAMPP Control Panel:
- Apache
- MySQL

5. Run installer in browser:
- `http://localhost/ueples-scheduling-system/setup.php`

6. After successful setup, delete or restrict `setup.php`.

7. Open login page:
- `http://localhost/ueples-scheduling-system/frontend/login/index.php`

## Linux Setup (Apache + MariaDB/MySQL)

Example below is for Ubuntu/Debian.

1. Install packages.
```bash
sudo apt update
sudo apt install -y apache2 mariadb-server php php-mysql php-mbstring php-xml php-curl git
```

2. Clone into web root.
```bash
cd /var/www/html
sudo git clone https://github.com/Kine-Master/ueples-scheduling-system.git
sudo chown -R www-data:www-data /var/www/html/ueples-scheduling-system
```

3. Create env file.
```bash
cd /var/www/html/ueples-scheduling-system
sudo cp .env.example .env
sudo nano .env
```

4. Start and enable services.
```bash
sudo systemctl enable --now apache2
sudo systemctl enable --now mariadb
```

5. Open installer:
- `http://<server-ip>/ueples-scheduling-system/setup.php`

6. After successful setup, delete or restrict `setup.php`.

7. Open login page:
- `http://<server-ip>/ueples-scheduling-system/frontend/login/index.php`

## Default Credentials (Seeded by setup)
| Role | Username | Password |
| :--- | :--- | :--- |
| Admin | `admin` | `password123` |
| Principal | `principal` | `password123` |
| Secretary | `secretary` | `password123` |
| Teacher | `teacher` | `password123` |

## Notes
- If you use a different folder name, update URLs accordingly.
- Keep `.env` private. Do not commit real credentials.
