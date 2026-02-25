# Book Management System - Tasks List

**Project:** Book Management System  
**Technology Stack:** PHP 8+, MySQL/MariaDB, Yii Framework v1  
**Created:** February 24, 2026

---

## Phase 1: Project Setup & Infrastructure

### 1.1 Project Initialization
- [x] Initialize Yii v1 project structure
- [x] Configure composer.json with dependencies (yii ~1.1.0, guzzlehttp/guzzle ^7.0)
- [x] Set up environment configuration (.env file support)
- [x] Configure database connection in config/database.php
- [x] Set up main application config (config/main.php)
- [x] Configure console application (config/console.php)
- [x] Set up runtime directory permissions

### 1.2 Database Migrations
- [x] Create migration: `m260224_000001_create_users_table.php`
- [x] Create migration: `m260224_000002_create_authors_table.php`
- [x] Create migration: `m260224_000003_create_books_table.php`
- [x] Create migration: `m260224_000004_create_book_authors_table.php`
- [x] Create migration: `m260224_000005_create_subscriptions_table.php`
- [x] Run all migrations and verify schema

---

## Phase 2: Models & Relations

### 2.1 Core Models
- [x] Create `User.php` model with validation rules
- [x] Create `Author.php` model with validation rules
- [x] Create `Book.php` model with validation rules
- [x] Create `BookAuthor.php` model (junction table)
- [x] Create `UserSubscription.php` model with validation rules

### 2.2 Model Relations
- [x] Define Book-Authors MANY_MANY relation in `Book.php`
- [x] Define Book-User BELONGS_TO relation (creator) in `Book.php`
- [x] Define Author-Books MANY_MANY relation in `Author.php`
- [x] Define Author-Subscriptions HAS_MANY relation in `Author.php`
- [x] Define User-Subscriptions HAS_MANY relation in `User.php`

### 2.3 Model Behaviors & Validation
- [x] Add timestamp behaviors to models
- [x] Implement unique validation for book title
- [x] Implement unique validation for ISBN
- [x] Add year range validation (1000-2100) for books
- [x] Add phone number validation (10-15 digits)

---

## Phase 3: Authentication & Authorization

### 3.1 User Authentication
- [x] Create `UserIdentity.php` component for authentication
- [x] Implement login functionality in `UserController`
- [x] Implement logout functionality
- [x] Implement user registration
- [x] Configure password hashing (bcrypt)

### 3.2 RBAC Implementation
- [x] Configure RBAC in main config (dbManager or phpManager)
- [x] Create `guest` role with permissions
- [x] Create `authenticated_user` role with permissions
- [x] Implement access control filters in controllers

---

## Phase 4: Controllers & Actions

### 4.1 SiteController
- [x] Create `SiteController` with basic actions
- [x] Implement homepage action
- [x] Implement error handling action

### 4.2 BookController
- [x] Create `BookController` with access control
- [x] Implement `actionIndex()` - list books with pagination
- [x] Implement `actionView($id)` - book detail page
- [x] Implement `actionCreate()` - create new book (auth required)
- [x] Implement `actionUpdate($id)` - edit book (owner only)
- [x] Implement `actionDelete($id)` - delete book (owner only)
- [x] Add filters: by author, by year, search by title/ISBN

### 4.3 AuthorController
- [x] Create `AuthorController` with access control
- [x] Implement `actionIndex()` - list authors with pagination
- [x] Implement `actionView($id)` - author detail page
- [x] Implement `actionCreateInline()` - AJAX inline author creation

### 4.4 UserController
- [x] Create `UserController` with access control
- [x] Implement `actionLogin()` - user login
- [x] Implement `actionLogout()` - user logout
- [x] Implement `actionRegister()` - user registration
- [x] Implement `actionProfile()` - user profile with subscriptions

### 4.5 ReportController
- [x] Create `ReportController` with access control
- [x] Implement `actionTopAuthors()` - TOP 10 authors report
- [x] Add year filter functionality

---

## Phase 5: Views & UI

### 5.1 Layout
- [x] Create main layout (`views/layouts/main.php`)
- [x] Implement navigation bar with menu items
- [x] Add search bar in navigation
- [x] Implement responsive design (Bootstrap/Tailwind)
- [x] Add flash message display area

### 5.2 Book Views
- [x] Create `books/index.php` - book list with filters and pagination
- [x] Create `books/view.php` - book detail page
- [x] Create `books/create.php` - book creation form
- [x] Create `books/update.php` - book edit form
- [x] Implement multi-select for authors in book form
- [x] Add cover image upload functionality

### 5.3 Author Views
- [x] Create `authors/index.php` - author list with pagination
- [x] Create `authors/view.php` - author detail with books list
- [x] Create inline author creation modal/form

### 5.4 User Views
- [x] Create `user/login.php` - login form
- [x] Create `user/register.php` - registration form
- [x] Create `user/profile.php` - profile with subscriptions list

### 5.5 Report Views
- [x] Create `report/topAuthors.php` - TOP 10 report with year filter

### 5.6 Static Assets
- [x] Set up CSS files in public/css/
- [x] Set up JavaScript files in public/js/
- [x] Configure assets directory

---

## Phase 6: Subscription System

### 6.1 Subscription Functionality
- [x] Implement guest subscription with phone number
- [x] Implement authenticated user subscription
- [x] Implement unsubscribe functionality
- [x] Add subscription button on author detail page
- [x] Display subscription status (subscribed/unsubscribed)

### 6.2 Subscription Management
- [x] Create subscription list in user profile
- [x] Implement subscription validation (prevent duplicates)
- [x] Add phone number confirmation flow

---

## Phase 7: SMS Notification System

### 7.1 SMS Service Component
- [x] Create `SmsPilotService.php` component
- [x] Implement `send($phone, $message)` method
- [x] Configure API key and test mode (emulator)
- [x] Add error handling and logging

### 7.2 Notification Triggers
- [x] Integrate SMS notification on book creation
- [x] Get all subscribers for book's authors
- [x] Send notifications to all subscribers
- [x] Log notification results

### 7.3 Console Command (Optional)
- [x] Create console command for scheduled notifications
- [ ] Configure cron job for batch processing

```php
// In code - send SMS
Yii::app()->smsPilot->send('79087964781', 'Hello!');

// Console commands
./yiic bookNotification notify --bookId=123
./yiic bookNotification notifyRecent --hours=24
./yiic bookNotification test --phone=79087964781
```

---

## Phase 8: Reporting

```php
# From the bookz1 directory
cd bookz1

# Seed all sample data (authors + books)
php protected/yiic seed

# Seed only authors
php protected/yiic seed authors

# Seed only books (requires authors to exist)
php protected/yiic seed books

# Clear all seeded data
php protected/yiic seed clear

# Clear and re-seed (fresh start)
php protected/yiic seed refresh
```

### 8.1 TOP 10 Authors Report
- [x] Implement SQL query for TOP 10 authors by book count
- [x] Add year filter functionality
- [x] Display rank, author name, book count, total books, latest book
- [x] Add clickable author links

---

## Phase 9: Security & Validation

### 9.1 Security Implementation
- [ ] Implement CSRF protection
- [ ] Implement XSS prevention (output escaping)
- [ ] Implement SQL injection prevention (Yii ORM)
- [ ] Add ownership checks for edit/delete operations
- [ ] Validate file uploads (cover images)

### 9.2 Form Validation
- [ ] Client-side validation (JavaScript)
- [ ] Server-side validation (Yii rules)
- [ ] Custom validation rules (ISBN format, phone format)
- [ ] Error message localization

---

## Phase 10: Testing & Quality Assurance

### 10.1 Manual Testing
- [ ] Test guest flow: view books, view authors, subscribe
- [ ] Test user flow: create, edit, delete books
- [ ] Test permissions: guest cannot edit, user cannot edit others' books
- [ ] Test TOP 10 report accuracy for different years
- [ ] Test SMS integration with emulator key
- [ ] Test form validation (required fields, unique constraints)
- [ ] Test database cascading deletes
- [ ] Test responsive layout on mobile

### 10.2 Edge Cases Testing
- [ ] Book with multiple authors
- [ ] Author with no books
- [ ] Year filter with no results
- [ ] Duplicate subscription prevention
- [ ] Invalid phone number handling

---

## Phase 11: Documentation & Deployment

### 11.1 Documentation
- [x] Create README.md with setup instructions
- [ ] Create .env.example file
- [ ] Document API endpoints and routes

### 11.2 Deployment Preparation
- [ ] Remove hardcoded credentials
- [ ] Clear runtime directory
- [ ] Verify vendor/ exclusion
- [ ] Create deployment archive

---

## Task Priority Legend

| Priority | Description |
|----------|-------------|
| 🔴 High | Core functionality, must complete first |
| 🟡 Medium | Important but can be done after core |
| 🟢 Low | Nice-to-have, optional enhancements |

---

## Estimated Timeline

| Phase | Estimated Duration |
|-------|-------------------|
| Phase 1: Setup | 1 day |
| Phase 2: Models | 1 day |
| Phase 3: Auth | 1 day |
| Phase 4: Controllers | 2 days |
| Phase 5: Views | 2 days |
| Phase 6: Subscriptions | 1 day |
| Phase 7: SMS | 1 day |
| Phase 8: Reports | 0.5 day |
| Phase 9: Security | 0.5 day |
| Phase 10: Testing | 1 day |
| Phase 11: Deployment | 0.5 day |
| **Total** | **11.5 days** |

---

## Dependencies Graph

```
Phase 1 (Setup)
    ↓
Phase 2 (Models)
    ↓
Phase 3 (Auth) ←→ Phase 4 (Controllers)
    ↓                  ↓
Phase 5 (Views) ←→ Phase 6 (Subscriptions)
    ↓                  ↓
Phase 7 (SMS) ←→ Phase 8 (Reports)
    ↓
Phase 9 (Security)
    ↓
Phase 10 (Testing)
    ↓
Phase 11 (Deployment)
```

---

**End of Tasks List**
