# NeighborlyIFix

NeighborlyIFix is a PHP-based civic issue reporting system that allows citizens to submit infrastructure issues, attach images, track status updates, and receive administrator notifications.

## Requirements

- PHP 8.0 or newer
- MySQL / MariaDB
- Composer
- Web server such as Apache (XAMPP, WAMP, Laragon, etc.)

## Installation

1. Place the project folder in your web server document root.
   - Example: `C:\xampp\htdocs\neighborlyfix`
2. Install Composer dependencies:
   ```bash
   composer install
   ```
3. Copy the environment template:
   ```bash
   copy .env.example .env
   ```
4. Update `.env` with your database and SMTP settings.

## Configuration

The application uses environment variables in `.env`:

- `DB_HOST` — database host
- `DB_NAME` — database name
- `DB_USER` — database username
- `DB_PASS` — database password
- `DB_CHARSET` — character set, typically `utf8mb4`
- `ADMIN_EMAIL` — email address to receive notifications
- `FROM_EMAIL` — sender email address
- `SMTP_HOST` — SMTP server hostname
- `SMTP_PORT` — SMTP port
- `SMTP_USER` — SMTP username
- `SMTP_PASS` — SMTP password
- `USE_SMTP` — set to `true` to enable SMTP email sending

## Database Setup

Create a database for the application and update `.env` accordingly.

The app expects the following main tables:

- `users`
- `issues`
- `categories`
- `comments`
- `issue_history`

There is no automated migration file included in this repository, so create the schema manually or use the SQL schema provided with your assignment.

## Running the Application

1. Start your web server and MySQL service.
2. Open a browser and visit:
   - `http://localhost/neighborlyfix/`
3. Register a user or log in with an administrator account.

## Demo Credentials

Use these sample accounts for testing:

- Admin: `admin@neighborlyifix.com` / `admin123`
- Citizen: `hawa@gmail.com` / `Hawa@123`

## File Structure

- `index.php` — public landing page
- `login.php` / `register.php` — authentication pages
- `dashboard.php` / `my_issues.php` — user interfaces
- `report_issue.php` / `view_issue.php` — issue reporting and details
- `admin/` — administrator management pages
- `includes/` — shared helper files and templates
- `config/` — database and environment settings
- `uploads/issues/` — uploaded issue images

## Permissions

Ensure the following directories are writable by the web server:

- `uploads/issues/`
- `logs/`

## Notes

- The base URL is currently configured for a folder named `neighborlyfix`.
- If you move the project to a different path or virtual host, update `base_url()` in `includes/functions.php` if needed.
- Use `.env.example` as the template for your local configuration.
