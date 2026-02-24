# Book Management System - Tasks List

**Project:** Book Management System  
**Technology Stack:** PHP 8+, MySQL/MariaDB, Yii Framework v1  
**Created:** February 24, 2026

---

## Phase 1: Project Setup & Infrastructure

### 1.1 Project Initialization
- [ ] Initialize Yii v1 project structure
- [ ] Configure composer.json with dependencies (yii ~1.1.0, guzzlehttp/guzzle ^7.0)
- [ ] Set up environment configuration (.env file support)
- [ ] Configure database connection in config/database.php
- [ ] Set up main application config (config/main.php)
- [ ] Configure console application (config/console.php)
- [ ] Set up runtime directory permissions

### 1.2 Database Migrations
- [ ] Create migration: `m260224_000001_create_users_table.php`
- [ ] Create migration: `m260224_000002_create_authors_table.php`
- [ ] Create migration: `m260224_000003_create_books_table.php`
- [ ] Create migration: `m260224_000004_create_book_authors_table.php`
- [ ] Create migration: `m260224_000005_create_subscriptions_table.php`
- [ ] Run all migrations and verify schema

---

## Phase 2: Models & Relations

### 2.1 Core Models
- [ ] Create `User.php` model with validation rules
- [ ] Create `Author.php` model with validation rules
- [ ] Create `Book.php` model with validation rules
- [ ] Create `BookAuthor.php` model (junction table)
- [ ] Create `UserSubscription.php` model with validation rules

### 2.2 Model Relations
- [ ] Define Book-Authors MANY_MANY relation in `Book.php`
- [ ] Define Book-User BELONGS_TO relation (creator) in `Book.php`
- [ ] Define Author-Books MANY_MANY relation in `Author.php`
- [ ] Define Author-Subscriptions HAS_MANY relation in `Author.php`
- [ ] Define User-Subscriptions HAS_MANY relation in `User.php`

### 2.3 Model Behaviors & Validation
- [ ] Add timestamp behaviors to models
- [ ] Implement unique validation for book title
- [ ] Implement unique validation for ISBN
- [ ] Add year range validation (1000-2100) for books
- [ ] Add phone number validation (10-15 digits)

---

## Phase 3: Authentication & Authorization

### 3.1 User Authentication
- [ ] Create `UserIdentity.php` component for authentication
- [ ] Implement login functionality in `UserController`
- [ ] Implement logout functionality
- [ ] Implement user registration
- [ ] Configure password hashing (bcrypt)

### 3.2 RBAC Implementation
- [ ] Configure RBAC in main config (dbManager or phpManager)
- [ ] Create `guest` role with permissions
- [ ] Create `authenticated_user` role with permissions
- [ ] Implement access control filters in controllers

---

## Phase 4: Controllers & Actions

### 4.1 SiteController
- [ ] Create `SiteController` with basic actions
- [ ] Implement homepage action
- [ ] Implement error handling action

### 4.2 BookController
- [ ] Create `BookController` with access control
- [ ] Implement `actionIndex()` - list books with pagination
- [ ] Implement `actionView($id)` - book detail page
- [ ] Implement `actionCreate()` - create new book (auth required)
- [ ] Implement `actionUpdate($id)` - edit book (owner only)
- [ ] Implement `actionDelete($id)` - delete book (owner only)
- [ ] Add filters: by author, by year, search by title/ISBN

### 4.3 AuthorController
- [ ] Create `AuthorController` with access control
- [ ] Implement `actionIndex()` - list authors with pagination
- [ ] Implement `actionView($id)` - author detail page
- [ ] Implement `actionCreateInline()` - AJAX inline author creation

### 4.4 UserController
- [ ] Create `UserController` with access control
- [ ] Implement `actionLogin()` - user login
- [ ] Implement `actionLogout()` - user logout
- [ ] Implement `actionRegister()` - user registration
- [ ] Implement `actionProfile()` - user profile with subscriptions

### 4.5 ReportController
- [ ] Create `ReportController` with access control
- [ ] Implement `actionTopAuthors()` - TOP 10 authors report
- [ ] Add year filter functionality

---

## Phase 5: Views & UI

### 5.1 Layout
- [ ] Create main layout (`views/layouts/main.php`)
- [ ] Implement navigation bar with menu items
- [ ] Add search bar in navigation
- [ ] Implement responsive design (Bootstrap/Tailwind)
- [ ] Add flash message display area

### 5.2 Book Views
- [ ] Create `books/index.php` - book list with filters and pagination
- [ ] Create `books/view.php` - book detail page
- [ ] Create `books/create.php` - book creation form
- [ ] Create `books/update.php` - book edit form
- [ ] Implement multi-select for authors in book form
- [ ] Add cover image upload functionality

### 5.3 Author Views
- [ ] Create `authors/index.php` - author list with pagination
- [ ] Create `authors/view.php` - author detail with books list
- [ ] Create inline author creation modal/form

### 5.4 User Views
- [ ] Create `user/login.php` - login form
- [ ] Create `user/register.php` - registration form
- [ ] Create `user/profile.php` - profile with subscriptions list

### 5.5 Report Views
- [ ] Create `report/topAuthors.php` - TOP 10 report with year filter

### 5.6 Static Assets
- [ ] Set up CSS files in public/css/
- [ ] Set up JavaScript files in public/js/
- [ ] Configure assets directory

---

## Phase 6: Subscription System

### 6.1 Subscription Functionality
- [ ] Implement guest subscription with phone number
- [ ] Implement authenticated user subscription
- [ ] Implement unsubscribe functionality
- [ ] Add subscription button on author detail page
- [ ] Display subscription status (subscribed/unsubscribed)

### 6.2 Subscription Management
- [ ] Create subscription list in user profile
- [ ] Implement subscription validation (prevent duplicates)
- [ ] Add phone number confirmation flow

---

## Phase 7: SMS Notification System

### 7.1 SMS Service Component
- [ ] Create `SmsPilotService.php` component
- [ ] Implement `send($phone, $message)` method
- [ ] Configure API key and test mode (emulator)
- [ ] Add error handling and logging

### 7.2 Notification Triggers
- [ ] Integrate SMS notification on book creation
- [ ] Get all subscribers for book's authors
- [ ] Send notifications to all subscribers
- [ ] Log notification results

### 7.3 Console Command (Optional)
- [ ] Create console command for scheduled notifications
- [ ] Configure cron job for batch processing

---

## Phase 8: Reporting

### 8.1 TOP 10 Authors Report
- [ ] Implement SQL query for TOP 10 authors by book count
- [ ] Add year filter functionality
- [ ] Display rank, author name, book count, total books, latest book
- [ ] Add clickable author links

### 8.2 Report Enhancements (Nice-to-have)
- [ ] Add PDF export functionality
- [ ] Add chart visualization

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
- [ ] Create README.md with setup instructions
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
