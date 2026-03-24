# UEP LES Scheduling System - System Documentation

## 1. Overview
UEP LES Scheduling System is a role-based web application for managing:
- faculty schedules
- workloads
- class/room utilization
- printable schedule reports

It supports four roles: `admin`, `principal`, `secretary`, and `teacher`.

## 2. Architecture

### 2.1 Stack
- Frontend: Vanilla HTML/CSS/JS
- Backend: Native PHP modules
- Database: MySQL/MariaDB
- Web Server: Apache

### 2.2 Key Directories
- `frontend/` - Role-based pages and UI assets
- `backend/` - Auth, master data, scheduling, reports, and API endpoints
- `backend/config/` - DB connection, env loader, common functions
- `setup.php` - Database and seed installer

## 3. Environment Variables

Configuration is loaded from `.env` (and optional `.env.local`) by `backend/config/db.php`.

| Variable | Purpose | Example |
| :--- | :--- | :--- |
| `UEP_DB_HOST` | DB host | `localhost` |
| `UEP_DB_PORT` | DB port | `3306` |
| `UEP_DB_NAME` | Database name | `ueples_scheduling_system` |
| `UEP_DB_USER` | Database username | `root` |
| `UEP_DB_PASS` | Database password | `` (empty) |
| `UEP_TIMEZONE` | App timezone | `Asia/Manila` |
| `UEP_APP_ENV` | Environment label | `development` |
| `UEP_APP_DEBUG` | Debug flag | `true` |
| `UEP_BASE_PATH` | Intended base path value | `/ueples-scheduling-system` |

## 4. Installation

### 4.1 Windows Installation (XAMPP)

1. Install XAMPP (Apache + MySQL).
2. Clone project into `C:\xampp\htdocs`.
```bash
cd C:\xampp\htdocs
git clone https://github.com/Kine-Master/ueples-scheduling-system.git
cd ueples-scheduling-system
```
3. Create `.env`.
```bash
copy .env.example .env
```
4. Edit `.env` with correct DB credentials.
5. Start Apache and MySQL from XAMPP Control Panel.
6. Run installer:
- `http://localhost/ueples-scheduling-system/setup.php`
7. Remove or secure `setup.php` after successful install.
8. Login page:
- `http://localhost/ueples-scheduling-system/frontend/login/index.php`

### 4.2 Linux Installation (Apache + MariaDB/MySQL)

Example for Ubuntu/Debian.

1. Install runtime dependencies.
```bash
sudo apt update
sudo apt install -y apache2 mariadb-server php php-mysql php-mbstring php-xml php-curl git
```
2. Deploy source.
```bash
cd /var/www/html
sudo git clone https://github.com/Kine-Master/ueples-scheduling-system.git
sudo chown -R www-data:www-data /var/www/html/ueples-scheduling-system
```
3. Create and edit `.env`.
```bash
cd /var/www/html/ueples-scheduling-system
sudo cp .env.example .env
sudo nano .env
```
4. Enable and start services.
```bash
sudo systemctl enable --now apache2
sudo systemctl enable --now mariadb
```
5. Run installer in browser:
- `http://<server-ip>/ueples-scheduling-system/setup.php`
6. Remove or secure `setup.php` after install.
7. Login:
- `http://<server-ip>/ueples-scheduling-system/frontend/login/index.php`

## 5. Post-Install Checklist
- Confirm you can log in as each role.
- Confirm master data loads (school year, subjects, rooms).
- Confirm schedule pages load without API errors.
- Confirm report pages print with timetable layout.
- Confirm teacher schedule filtering includes school year and semester.

## 6. Default Seed Accounts
| Role | Username | Password |
| :--- | :--- | :--- |
| Admin | `admin` | `password123` |
| Principal | `principal` | `password123` |
| Secretary | `secretary` | `password123` |
| Teacher | `teacher` | `password123` |

## 7. Security Notes
- Never commit real credentials in `.env`.
- Keep `.env` and `.env.local` private.
- Restrict access to `setup.php` after deployment.
- Use strong passwords in production.

## 8. Troubleshooting
- `Database Connection Failed`:
  - Verify `.env` values and DB service status.
- `Failed to open stream ... db.php`:
  - Ensure `backend/config/db.php` exists and paths are unchanged.
- Empty report data:
  - Check active school year and selected filters.
- Permissions issues on Linux:
  - Ensure Apache user can read project files (`www-data` ownership).
