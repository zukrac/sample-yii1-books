# Product Requirement Document
## Book Management System

**Project Name:** Book Management System  
**Technology Stack:** PHP 8+, MySQL/MariaDB, Yii Framework v1, Docker environment  
**Date:** February 24, 2026

---

## 1. Executive Summary

A web application for managing books and authors with role-based access control. The system supports guest users (view-only with SMS subscription capability), authenticated users (full CRUD operations on books), and public reporting features. Integration with SMS Pilot API enables automated notifications for new book releases from subscribed authors.

---

## 2. Business Requirements

### 2.1 Core Objectives
- Create a centralized book catalog management system
- Enable user subscription to author updates
- Provide SMS notifications for new releases
- Generate analytics reports on author productivity
- Support multiple user roles with appropriate access levels

### 2.2 Key Stakeholders
- **Guest Users:** Browse books, subscribe to authors
- **Authenticated Users:** Full content management
- **System:** Automated SMS notifications

---

## 3. Functional Requirements

### 3.1 Books Entity

**Database Table:** `books`

| Field | Type | Description | Constraints |
|-------|------|-------------|-------------|
| id | INT | Primary key | NOT NULL, AUTO_INCREMENT |
| title | VARCHAR(255) | Book title/название | NOT NULL, UNIQUE |
| year_published | INT | Year of release/год выпуска | NOT NULL |
| description | TEXT | Book description/описание | NULLABLE |
| isbn | VARCHAR(20) | ISBN number | NULLABLE, UNIQUE |
| cover_image | VARCHAR(500) | Cover photo URL or file path/фото главной страницы | NULLABLE |
| created_at | TIMESTAMP | Creation timestamp | DEFAULT CURRENT_TIMESTAMP |
| updated_at | TIMESTAMP | Last update timestamp | ON UPDATE CURRENT_TIMESTAMP |
| created_by | INT | User ID who created record | FOREIGN KEY to users.id |

**Key Features:**
- ISBN field supports various formats
- Cover image can be stored as file path or S3 URL
- Timestamps track record lifecycle

### 3.2 Authors Entity

**Database Table:** `authors`

| Field | Type | Description | Constraints |
|-------|------|-------------|-------------|
| id | INT | Primary key | NOT NULL, AUTO_INCREMENT |
| full_name | VARCHAR(255) | Full name (ФИО) | NOT NULL |
| biography | TEXT | Author biography | NULLABLE |
| created_at | TIMESTAMP | Creation timestamp | DEFAULT CURRENT_TIMESTAMP |

**Key Features:**
- Authors are NOT system users (separate entities)
- Support for multiple books per author
- Basic biographical information

### 3.3 Book-Author Association

**Database Table:** `book_authors` (Many-to-Many)

| Field | Type | Description |
|-------|------|-------------|
| id | INT | Primary key |
| book_id | INT | FOREIGN KEY to books.id (CASCADE DELETE) |
| author_id | INT | FOREIGN KEY to authors.id (CASCADE DELETE) |
| author_order | INT | Display order for co-authors |
| UNIQUE(book_id, author_id) | INDEX | Prevent duplicates |

**Key Features:**
- Support multiple authors per book
- Maintain author order for display
- Cascading deletes ensure data integrity

### 3.4 User Subscriptions

**Database Table:** `user_subscriptions`

| Field | Type | Description |
|-------|------|-------------|
| id | INT | Primary key |
| user_id | INT | FOREIGN KEY to users.id (CASCADE DELETE) |
| author_id | INT | FOREIGN KEY to authors.id (CASCADE DELETE) |
| subscribed_at | TIMESTAMP | Subscription timestamp |
| phone_number | VARCHAR(15) | Contact phone for notifications |
| UNIQUE(user_id, author_id) | INDEX | One subscription per author |

**Key Features:**
- Store phone number for SMS delivery
- Track subscription timestamp
- Prevent duplicate subscriptions

---

## 4. Access Control & Authorization

### 4.1 User Roles

#### 4.1.1 Guest (Unauthenticated User)
**Permissions:**
- ✓ View all books (list and detail)
- ✓ View all authors
- ✓ Subscribe to author updates (via phone number)
- ✓ View TOP 10 Authors report
- ✓ Access SMS subscription form

**Restrictions:**
- ✗ Cannot add/edit/delete books
- ✗ Cannot edit/delete subscriptions
- ✗ Cannot create accounts (registration external)

#### 4.1.2 Authenticated User (Юзер)
**Permissions:**
- ✓ All guest permissions (view, subscribe, report)
- ✓ Create books
- ✓ Edit own created books
- ✓ Delete own created books
- ✓ Manage personal subscriptions
- ✓ Edit personal profile

**Restrictions:**
- ✗ Cannot edit/delete books created by others
- ✗ Cannot manage other users' subscriptions
- ✗ Cannot access admin functions

### 4.2 Authorization Implementation

**RBAC Strategy:**
- Use Yii RBAC (role-based access control)
- Two roles: `guest` (default) and `authenticated_user`
- Implement via phpManager or dbManager (not critical)
- Controller actions protected with access filters

**Code Example Structure:**
```php
// In controller
public function filters()
{
    return array(
        'accessControl', // enable access control filter
    );
}

public function accessRules()
{
    return array(
        array('allow',  // allow authenticated users
            'actions'=>array('create','update','delete'),
            'users'=>array('@'),
        ),
        array('allow',  // allow guests
            'actions'=>array('index','view','report'),
            'users'=>array('?'),
        ),
        array('deny'),
    );
}
```

---

## 5. Features & Functionality

### 5.1 Book Management (CRUD)

#### 5.1.1 List Books
- **Access:** Guest, Authenticated User
- **Display:** Table/list with columns: Title, Authors, Year, ISBN, Cover thumbnail
- **Pagination:** 20 items per page
- **Filters:** By author, by year, search by title/ISBN
- **Actions:** View details

#### 5.1.2 View Book Detail
- **Access:** Guest, Authenticated User
- **Content:** 
  - Full title, year, ISBN
  - Complete description
  - Cover image (if available)
  - All associated authors with links
  - Links to author pages
- **No Edit/Delete buttons for guests**

#### 5.1.3 Create Book (Authenticated Users Only)
- **Form Fields:**
  - Title (required, unique validation)
  - Year published (required, numeric, 1000-2100 range)
  - Description (optional, text area)
  - ISBN (optional, regex validation)
  - Cover image (optional, file upload)
  - Select authors (multi-select, create new author inline)
- **Validation:** Server-side (Yii form validation rules)
- **Success:** Redirect to book detail, show success message
- **Stored by:** Current authenticated user ID (created_by)

#### 5.1.4 Edit Book
- **Access:** Only book creator or admin
- **Behavior:** Same form as create, pre-populated with existing data
- **Permission Check:** `if($book->created_by !== Yii::app()->user->id) throw 403`
- **Update:** created_by unchanged, updated_at updated

#### 5.1.5 Delete Book
- **Access:** Only book creator or admin
- **Confirmation:** JavaScript confirm dialog
- **Cascade:** Deletes book_authors associations automatically (DB constraint)
- **Redirect:** Back to book list with success message

### 5.2 Author Management

#### 5.2.1 View Author List
- **Access:** Guest, Authenticated User
- **Display:** Table with: Name, Number of books, Links to all books
- **Pagination:** 50 items per page

#### 5.2.2 View Author Detail
- **Content:**
  - Full name
  - Biography (if available)
  - All published books with years
  - Subscribe button (with phone input for guests)
- **No Edit/Delete (no author CRUD in current scope)**

#### 5.2.3 Inline Author Creation (During Book Create)
- **Trigger:** "+ Add new author" button in book form
- **Modal/Popup:** Form to enter author full name
- **Validation:** Full name required, 2-100 characters
- **Success:** Author added to selection, AJAX update of author list

### 5.3 Subscription Management

#### 5.3.1 Subscribe Guest User to Author
- **Access:** Guest users
- **Interface:** Phone subscription form on author detail page
- **Flow:**
  1. Guest enters phone number (validation: 10-15 digits)
  2. Submit creates record in `user_subscriptions` with:
     - user_id = NULL or 0 (or use special guest user record if needed)
     - author_id = selected author
     - phone_number = submitted phone
  3. Send confirmation SMS (emulator mode for testing)
  4. Show "Subscription confirmed" message

#### 5.3.2 Subscribe Authenticated User
- **Access:** Authenticated users
- **Interface:** Subscribe button on author detail page
- **Flow:**
  1. Click subscribe → auto-subscribe with user's profile phone
  2. Or form to confirm/update phone number
  3. Create record in `user_subscriptions`
  4. Show success message
  5. Update button to "Unsubscribe" status

#### 5.3.3 View Subscriptions
- **Access:** Authenticated users (own subscriptions only)
- **Location:** User profile / dashboard
- **Display:** List of subscribed authors with subscription date
- **Action:** Unsubscribe button (soft delete or status flag)

#### 5.3.4 Unsubscribe
- **Mechanism:** Delete record from `user_subscriptions` (or mark as deleted)
- **Permission:** Only self or admin
- **No re-confirmation needed**

### 5.4 SMS Notification System

#### 5.4.1 Requirements
- **Service:** SMS Pilot API (https://smspilot.ru/)
- **Test Mode:** Use emulator key (no real SMS sent)
- **Trigger:** When new book is created
- **Recipients:** All users subscribed to that book's authors
- **Message Format:**
  ```
  Новая книга от <Author Name>: "<Book Title>" (ISBN: <ISBN>)
  ```

#### 5.4.2 Implementation
- **Console Command:** `yii bookNotification/notify` (Yii v1 console)
- **Trigger Points:**
  1. After book creation in BookController::actionCreate
  2. Or scheduled cron job (optional, nice-to-have)
- **Code Flow:**
  ```
  1. New book saved → get all book_authors
  2. For each author → find all subscriptions
  3. For each subscription → send SMS to phone_number via SMS Pilot API
  4. Log results to database (optional: notifications table)
  ```

#### 5.4.3 SMS Pilot Integration
- **API Endpoint:** POST to SMS Pilot API
- **Authentication:** API key from SMS Pilot account
- **Parameters:**
  - phone: subscriber phone
  - message: notification text
  - sender: app name/identifier
- **Response Handling:** Check success status, handle errors
- **Test Mode Config:** Use emulator key in Yii config

**Example Code Structure:**
```php
// In a helper/component class
class SmsPilotService extends CComponent
{
    public $apiKey = 'emulator'; // test key or real API key
    public $apiUrl = 'https://smspilot.ru/api/';
    
    public function send($phone, $message)
    {
        // Prepare request
        // Make HTTP request to SMS Pilot
        // Handle response
        // Return success/failure
    }
}

// In config/main.php
'components' => array(
    'smsPilot' => array(
        'class' => 'application.components.SmsPilotService',
        'apiKey' => 'emulator', // or from environment variable
    ),
),
```

### 5.5 Reporting

#### 5.5.1 TOP 10 Authors Report

**Page:** `/report/topAuthors` or `/books/topAuthorsReport`

**Access Level:** Public (Guest and Authenticated Users can view)

**Report Definition:**
- **Title:** "TOP 10 Authors by Book Count"
- **Time Period Filter:** 
  - Dropdown to select year (default: current year)
  - Button to apply filter
- **Display Format:** Table with columns:
  1. Rank (1-10)
  2. Author Name (clickable link to author page)
  3. Number of Books Released in Selected Year
  4. Total Books (all time)
  5. Latest Book (title and year)

**Sorting:** Descending by book count in selected year

**Data Query:**
```sql
SELECT 
    a.id,
    a.full_name,
    COUNT(DISTINCT b.id) as books_in_year,
    (SELECT COUNT(*) FROM books b2 
     JOIN book_authors ba2 ON b2.id = ba2.book_id 
     WHERE ba2.author_id = a.id) as total_books,
    MAX(b.title) as latest_book
FROM authors a
LEFT JOIN book_authors ba ON a.id = ba.author_id
LEFT JOIN books b ON ba.book_id = b.id AND YEAR(b.year_published) = :year
GROUP BY a.id
ORDER BY books_in_year DESC
LIMIT 10
```

**Export (Optional):** PDF button (nice-to-have, not critical)

---

## 6. Database Schema

### 6.1 Users Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE,
    phone VARCHAR(15),
    role VARCHAR(50) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 6.2 Authors Table
```sql
CREATE TABLE authors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(255) NOT NULL,
    biography TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 6.3 Books Table
```sql
CREATE TABLE books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL UNIQUE,
    year_published INT NOT NULL,
    description TEXT,
    isbn VARCHAR(20) UNIQUE,
    cover_image VARCHAR(500),
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
```

### 6.4 Book-Authors Junction Table
```sql
CREATE TABLE book_authors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL,
    author_id INT NOT NULL,
    author_order INT DEFAULT 0,
    UNIQUE KEY unique_book_author (book_id, author_id),
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE
);
```

### 6.5 Subscriptions Table
```sql
CREATE TABLE user_subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    author_id INT NOT NULL,
    phone_number VARCHAR(15),
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_subscription (user_id, author_id),
    FOREIGN KEY (author_id) REFERENCES authors(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### 6.6 Migrations Strategy
- Create Yii migration files (not SQL dumps)
- One migration per table
- File naming: `m260224_xxxxxx_create_[table_name]_table.php`
- Include indexes and foreign keys

---

## 7. UI/UX Requirements

### 7.1 Layout
- **Navigation Bar:** 
  - Logo/Home link
  - Books, Authors, TOP 10 Report
  - Search bar (search books by title/ISBN)
  - Login/Profile link (or Logout if authenticated)
- **Responsive:** Mobile-friendly layout (Bootstrap or Tailwind)
- **Theme:** Clean, professional design

### 7.2 Key Pages

| Page | Route | Access | Description |
|------|-------|--------|-------------|
| Books List | `/books` or `/` | Public | All books with filters |
| Book Detail | `/books/view/<id>` | Public | Full book information |
| Create Book | `/books/create` | Auth only | Form to add book |
| Edit Book | `/books/update/<id>` | Auth only | Form to edit book |
| Authors List | `/authors` | Public | All authors |
| Author Detail | `/authors/view/<id>` | Public | Author info + books |
| TOP 10 Report | `/report/topAuthors` | Public | Report page with year filter |
| User Profile | `/user/profile` | Auth only | View subscriptions |
| Login | `/user/login` | Guest only | Authentication form |
| Register | `/user/register` | Guest only | User registration |

### 7.3 Forms
- **Client-side validation:** JavaScript for UX feedback
- **Server-side validation:** Yii CForm with rules
- **Error messages:** Clear, user-friendly text
- **Success messages:** Confirm actions with flash messages

---

## 8. Technical Architecture

### 8.1 Project Structure (Yii v1)
```
project/
├── protected/
│   ├── models/
│   │   ├── User.php
│   │   ├── Book.php
│   │   ├── Author.php
│   │   ├── BookAuthor.php
│   │   ├── UserSubscription.php
│   ├── controllers/
│   │   ├── SiteController.php
│   │   ├── BookController.php
│   │   ├── AuthorController.php
│   │   ├── ReportController.php
│   │   ├── UserController.php
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── main.php
│   │   ├── books/
│   │   │   ├── index.php
│   │   │   ├── view.php
│   │   │   ├── create.php
│   │   │   ├── update.php
│   │   ├── authors/
│   │   ├── report/
│   │   ├── user/
│   ├── migrations/
│   │   ├── m260224_000001_create_users_table.php
│   │   ├── m260224_000002_create_authors_table.php
│   │   ├── m260224_000003_create_books_table.php
│   │   ├── m260224_000004_create_book_authors_table.php
│   │   ├── m260224_000005_create_subscriptions_table.php
│   ├── components/
│   │   ├── SmsPilotService.php
│   │   ├── UserIdentity.php
│   ├── config/
│   │   ├── main.php
│   │   ├── console.php
│   │   ├── database.php
├── public/
│   ├── index.php
│   ├── assets/
│   ├── css/
│   ├── js/
│── composer.json
├── README.md
```

### 8.2 Dependencies (composer.json)
```json
{
    "require": {
        "php": ">=8.0",
        "yiisoft/yii": "~1.1.0",
        "guzzlehttp/guzzle": "^7.0"
    }
}
```

### 8.3 Configuration
- **Database:** MySQL/MariaDB connection via Yii main config
- **Environment Variables:** .env file for sensitive data (API keys, DB credentials)
- **RBAC:** dbManager or phpManager for role-based access

---

## 9. Non-Functional Requirements

### 9.1 Performance
- Book list page: Load in <500ms (indexed queries)
- Report generation: <2 seconds for TOP 10
- Image handling: Optimize cover images (max 2MB)

### 9.2 Security
- **Authentication:** Password hashing (Yii's bcrypt)
- **Authorization:** Check ownership before edit/delete
- **Input Validation:** Prevent SQL injection (Yii ORM)
- **XSS Prevention:** Escape output in views
- **CSRF Protection:** Yii built-in CSRF tokens

### 9.3 Scalability
- Database indexes on: book_id, author_id, user_id, year_published
- Pagination for large result sets
- SMS sending: Async (queue) optional enhancement

### 9.4 Maintainability
- Clear code comments in complex logic
- Consistent naming conventions
- Separation of concerns (models, controllers, views)
- Use Yii's built-in validation and error handling

---

## 10. Testing Strategy

### 10.1 Manual Testing Checklist
- [ ] **Guest Flow:** View books, view authors, subscribe with phone
- [ ] **User Flow:** Create, edit, delete books; manage subscriptions
- [ ] **Permissions:** Verify guests can't edit; users can't edit others' books
- [ ] **Report:** Check TOP 10 accuracy for different years
- [ ] **SMS:** Verify SMS Pilot integration with emulator key
- [ ] **Forms:** Test validation (required fields, unique constraints)
- [ ] **Database:** Verify cascading deletes work
- [ ] **Mobile:** Check responsive layout on mobile browsers

### 10.2 Edge Cases
- Book with multiple authors: Display all, subscribe to each separately
- Author with no books: Still visible in author list
- Year filter: Books published in selected year only
- Duplicate subscriptions: Prevent via UNIQUE constraint
- Invalid phone: Show error message

---

## 11. Deployment Notes

### 11.1 Environment Setup
```bash
# Clone or extract project
cd project

# Install dependencies
composer install

# Run migrations
./protected/yiic migrate

# Set write permissions
chmod -R 777 protected/runtime

# Create .env file with database credentials and SMS Pilot key
```

### 11.2 Deliverables
- Source code (without vendor/ and runtime/ directories)
- Migration files (not SQL dumps)
- README with setup instructions
- .env.example file with required variables
- Git repository or archive file (GitHub/Bitbucket/ZIP)

### 11.3 Code Quality
- No hardcoded credentials
- Clear variable/function naming
- Comments for complex logic
- Follows Yii v1 conventions

---

## 12. Glossary & Definitions

| Term | Definition |
|------|-----------|
| Guest | Unauthenticated user (can view, subscribe) |
| User | Authenticated user (can create, edit, delete books) |
| Author | Non-user entity (separate from user system) |
| Book | Content entity with title, year, description, ISBN, authors |
| Subscription | Association of phone number to author for SMS notifications |
| RBAC | Role-Based Access Control (Yii authorization system) |
| SMS Pilot | Third-party SMS service used for notifications |
| Emulator | Test mode of SMS Pilot (no real SMS sent) |

---

## 13. Appendix

### 13.1 SMS Pilot API Example
```php
$phone = '+37255123456';
$message = 'Новая книга от Иван Петров: "Война и мир" (ISBN: 978-5-17-06490-8)';

$data = array(
    'phone' => $phone,
    'message' => $message,
    'sender' => 'BookApp',
);

$ch = curl_init('https://smspilot.ru/api/send');
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Basic emulator'  // use 'emulator' for test mode
));
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
// Check if sent successfully
```

### 13.2 Yii Model Relations Example
```php
// In Book model
public function relations()
{
    return array(
        'authors' => array(self::MANY_MANY, 'Author', 'book_authors(book_id, author_id)'),
        'creator' => array(self::BELONGS_TO, 'User', 'created_by'),
    );
}

// In Author model
public function relations()
{
    return array(
        'books' => array(self::MANY_MANY, 'Book', 'book_authors(author_id, book_id)'),
        'subscriptions' => array(self::HAS_MANY, 'UserSubscription', 'author_id'),
    );
}
```

### 13.3 References
- Yii v1 Documentation: https://www.yiiframework.com/doc/guide/1.1/
- SMS Pilot API: https://smspilot.ru/
- MySQL Documentation: https://dev.mysql.com/doc/
- PHP 8 Manual: https://www.php.net/manual/en/

---

## 14. Document History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | Feb 24, 2026 | PM | Initial PRD creation |

---

**End of Product Requirement Document**