# Book Management System

A web application for managing books and authors with role-based access control, built on Yii Framework v1.

## Features

- **Book Management**: Create, read, update, and delete books with cover images
- **Author Management**: Manage authors with biographical information
- **Multi-Author Support**: Books can have multiple authors with ordering
- **User Authentication**: Registration, login, and profile management
- **Subscription System**: Subscribe to authors for new book notifications
- **SMS Notifications**: Automatic SMS alerts via SMS Pilot when new books are added
- **TOP 10 Authors Report**: View top authors by book count with year filtering
- **Role-Based Access Control**: Guest and authenticated user roles

## Requirements

- PHP 8.0 or higher
- MySQL/MariaDB 5.7+
- Apache/Nginx web server with mod_rewrite enabled
- Composer (for dependencies)

![2026-02-25_03-37-38.png](/docs/images/2026-02-25_03-37-38.png)
![2026-02-25_03-38-15.png](/docs/images/2026-02-25_03-38-15.png)
![2026-02-25_03-38-55.png](/docs/images/2026-02-25_03-38-55.png)
![2026-02-25_03-39-56.png](/docs/images/2026-02-25_03-39-56.png)

## Installation

### 1. Clone or Download

```bash
cd /path/to/your/web/root
# Clone or extract the project to 'bookz1' directory
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

Copy the example environment file and configure your settings:

```bash
cp .env.example .env
```

Edit `.env` with your configuration:

```env
# SMS Pilot Configuration
SMSPILOT_API_KEY=emulator          # Use 'emulator' for testing
SMSPILOT_SENDER=BookSystem
SMSPILOT_TEST_MODE=true

# Database (if using environment variables)
# DB_HOST=localhost
# DB_NAME=bookz1
# DB_USER=root
# DB_PASSWORD=
```

### 4. Configure Database

Edit `protected/config/database.php` with your database credentials:

```php
return array(
    'connectionString' => 'mysql:host=localhost;dbname=bookz1',
    'emulatePrepare' => true,
    'username' => 'your_username',
    'password' => 'your_password',
    'charset' => 'utf8',
);
```

### 5. Create Database

```sql
CREATE DATABASE bookz1 CHARACTER SET utf8 COLLATE utf8_unicode_ci;
```

### 6. Run Migrations

```bash
cd bookz1
php protected/yiic migrate
```

Apply all migrations when prompted.

### 7. Set Directory Permissions

Ensure the following directories are writable by the web server:

```bash
chmod -R 777 protected/runtime
chmod -R 777 assets
chmod -R 777 uploads/covers
```

### 8. Configure Web Server

#### Apache

The project includes `.htaccess` files for Apache. Ensure `mod_rewrite` is enabled:

### 9. Access the Application

Open your browser and navigate to:
- Application: `http://bookz1.localhost/` (or your configured URL)

## Directory Structure

```
bookz1/
├── assets/              # Published asset files
├── css/                 # Stylesheet files
├── images/              # Image assets
├── protected/           # Application core
│   ├── commands/        # Console commands
│   ├── components/      # Application components
│   ├── config/          # Configuration files
│   ├── controllers/     # Controller classes
│   ├── migrations/      # Database migrations
│   ├── models/          # Model classes
│   ├── runtime/         # Runtime files (logs, cache)
│   └── views/           # View templates
├── themes/              # Theme templates
├── uploads/             # User uploaded files
│   └── covers/          # Book cover images
├── .env.example         # Environment configuration template
├── .htaccess            # Apache URL rewriting
├── index.php            # Application entry point
└── index-test.php       # Debug entry point
```

## Console Commands

### Seed Data

Populate the database with sample data:

```bash
# Seed all sample data (authors + books)
php protected/yiic seed
```

### SMS Notifications

```bash
# Send notification for a specific book
php protected/yiic bookNotification notify --bookId=123

# Notify about books added in the last 24 hours
php protected/yiic bookNotification notifyRecent --hours=24

# Test SMS sending
php protected/yiic bookNotification test --phone=79087964781
```

### Migrations

```bash
# Apply pending migrations
php protected/yiic migrate
```

## User Roles

| Role | Permissions |
|------|-------------|
| Guest | View books, view authors, subscribe to authors |
| Authenticated User | All guest permissions + create/edit/delete own books |

## API Routes

| Route | Method | Description |
|-------|--------|-------------|
| `/` | GET | Homepage |
| `/books` | GET | List books with pagination and filters |
| `/books/<id>` | GET | View book details |
| `/books/create` | GET/POST | Create new book (auth required) |
| `/books/update/<id>` | GET/POST | Edit book (owner only) |
| `/books/delete/<id>` | POST | Delete book (owner only) |
| `/authors` | GET | List authors with pagination |
| `/authors/<id>` | GET | View author details |
| `/user/login` | GET/POST | User login |
| `/user/register` | GET/POST | User registration |
| `/user/logout` | GET | User logout |
| `/user/profile` | GET | User profile with subscriptions |
| `/report/topAuthors` | GET | TOP 10 authors report |

## SMS Integration

The system uses SMS Pilot for sending notifications. Configure your API key in `.env`:

- **Testing**: Use `SMSPILOT_API_KEY=emulator` to test without sending real SMS
- **Production**: Get your API key from [SMS Pilot](https://smspilot.ru/apikey.php)

### Notification Flow

1. User subscribes to an author
2. When a new book is added by that author
3. SMS notification is sent to all subscribers
4. Notification includes book title and author name

## Credits

- Built with [Yii Framework 1.1](https://www.yiiframework.com/)
- SMS integration by [SMS Pilot](https://smspilot.ru/)
