# CampusPulse - Smart Feedback System

A modern campus feedback web application built with PHP and MySQL/SQLite. Features a clean UI with Tailwind CSS, dark mode support, responsive design, optimized performance, and comprehensive user profile management.

## 📁 Project Structure & File Details

```
smart_feedback/
├── admin/                    # Admin Panel
│   ├── dashboard.php        # Admin dashboard with statistics, recent feedback overview
│   ├── feedbacks.php        # Manage all feedback: view details and delete
│   ├── messages.php         # Manage contact messages
│   └── users.php            # User management: enable/disable accounts, view user list
│
├── assets/                   # Static Resources
│   ├── anonymous.png        # Anonymous user avatar
│   └── default-avatar.png   # Default profile picture (used when user has no upload)
│
├── inc/                      # Core System Files
│   ├── config.php           # Database configuration (MySQL/SQLite settings)
│   ├── db.php               # PDO connection, authentication helpers (current_user, require_login)
│   ├── footer.php           # Page footer template with closing HTML tags
│   └── header.php           # Navigation bar with Tailwind CSS, dark mode toggle, user profile dropdown
│
├── storage/                  # User-Generated Content
│   └── profile_pics/        # Uploaded profile pictures (writable, 755/777 permissions)
│
├── index.php                 # 🏠 Entry Point - Animated welcome screen with brand introduction and main feedback feed
├── feed.php                  # 📰 Pulse Feed - Main feedback feed with voting and filtering
├── about.php                 # ℹ️ About Page - Platform information and features
├── contact.php               # ✉️ Contact Page - Contact form for user inquiries
├── login.php                 # 🔐 Login Page - User authentication
├── logout.php                # 🚪 Logout Handler - Session destruction and redirect
├── register.php              # ✍️ Registration Page - New user signup with validation
├── reset.php                 # 🔑 Password Reset - Password recovery flow
├── profile.php               # 👤 User Profile - Edit profile info, change password, upload avatar
├── submit.php                # 📝 Submit Feedback - Form to create new feedback
├── my_feedback.php           # 📋 My Feedback - User's own feedback list with edit/delete
├── edit_feedback.php         # ✏️ Edit Feedback - Modify existing feedback (owner only)
├── delete_feedback.php       # 🗑️ Delete Feedback - Remove feedback (owner/admin only)
├── delete_profile_pic.php    # 🖼️ Delete Avatar - Remove user's profile picture
├── feedback.php              # 📄 Single Feedback View - Detailed view of one feedback item
├── vote.php                  # 👍👎 Vote API - AJAX endpoint for like/dislike actions
├── rating.php                # ⭐ Rate Platform - Users rate and review the website
├── setup.php                 # ⚙️ Setup Script - Creates database tables and admin user
├── .htaccess                 # 🛡️ Apache Config - Security headers, URL rewriting, caching
├── .gitignore                # 📦 Git Ignore - Files to exclude from version control
└── README.md                 # 📖 Documentation - This file
```

## 📝 Detailed File Descriptions

### 🎯 Main Pages

#### `index.php` - Entry Point
Animated welcome page serving as entry point with:
- Gradient background animation with floating elements
- Brand introduction (CampusPulse logo and tagline)
- Auto-redirect to feed after 8 seconds
- Click button for immediate access to main feed

#### `feed.php` - Pulse Feed
The main feedback feed showing all approved submissions. Features:
- Compact, centered card layout (max 1024px width)
- Category filtering (Course, Infrastructure, Cafeterias)
- Real-time voting (like/dislike) with AJAX
- User avatars and anonymous posting support
- Optimized performance with reduced animations
- Displays vote counts and submission time

#### `submit.php` - Submit Feedback
Form for users to create new feedback. Features:
- Title and description fields
- Category dropdown selection
- Anonymous submission option
- Auto-approved status (no admin approval needed)
- Login requirement enforcement
- Validation and error handling

#### `my_feedback.php` - My Feedback
Personal feedback management page. Features:
- List all user's submitted feedback
- Edit and delete buttons for each item
- All feedback auto-approved
- Quick access to modify content

### 👤 User Management

#### `profile.php` - User Profile
Comprehensive profile management. Features:
- **Profile Picture Upload**: JPG, PNG, GIF, WebP (max 2MB)
- **Edit Profile Info**: Email, student ID, program, year
- **Change Password**: With validation (8+ chars, mixed case, numbers)
- **Remove Avatar**: Delete current profile picture
- Real-time preview of uploaded images

#### `login.php` - User Login
Authentication page with:
- Username/password validation
- Remember session between visits
- Link to registration and password reset
- Error message display

#### `register.php` - User Registration
New user signup with:
- Username, email, password fields
- Optional: Student ID, program, year
- Password strength validation
- Duplicate username/email checking
- Automatic profile picture placeholder

#### `reset.php` - Password Reset
Password recovery system:
- Username verification
- Security question (if implemented)
- New password setting
- Validation and confirmation

#### `logout.php` - Logout Handler
Simple logout script that:
- Destroys user session
- Clears authentication cookies
- Redirects to home with success message

### ✏️ Feedback Management

#### `edit_feedback.php` - Edit Feedback
Modify existing feedback:
- Pre-filled form with current data
- Title, description, category editing
- Anonymous toggle modification
- Owner verification (security)
- Redirects to My Feedback on success

#### `delete_feedback.php` - Delete Feedback
Remove feedback submission:
- POST-only for security
- Owner/admin authorization check
- Confirmation before deletion
- Database cleanup

#### `delete_profile_pic.php` - Delete Profile Picture
Remove user avatar:
- Deletes file from storage/profile_pics/
- Updates database to remove reference
- Reverts to default avatar
- Requires user authentication

#### `feedback.php` - Single Feedback View
Detailed feedback display:
- Full content view
- Author information (or anonymous)
- Category and timestamp
- Admin back button for moderation

#### `vote.php` - Vote AJAX Endpoint
Handles like/dislike actions:
- JSON response format
- Prevents duplicate votes
- Updates vote counts
- Returns new totals instantly

### 🎨 Additional Pages

#### `about.php` - About Page
Information about CampusPulse:
- Platform mission and vision
- Feature highlights
- Team information (customizable)
- Links to other sections

#### `contact.php` - Contact Form
User inquiry system:
- Name, email, subject, message fields
- Subject category dropdown
- Message validation
- Stores in database for admin review

#### `rating.php` - Rate Platform
Website rating system:
- 1-5 star rating interface
- Optional text review
- Shows average rating and total reviews
- Displays recent reviews from other users
- User can update their rating

### 🔧 Admin Panel

#### `admin/dashboard.php` - Admin Dashboard
Central admin control:
- **Statistics Cards**: Today's submissions, total feedback, total users, total messages
- **Recent Feedback**: Last 10 submissions with quick view
- **Quick Actions**: Links to feedback, user, and message management
- Clean, simplified interface

#### `admin/feedbacks.php` - Manage Feedback
Feedback management:
- View all feedback submissions
- View Details button (full content view)
- Delete button (removes entry)
- Batch delete with checkbox selection
- All feedback auto-approved on submission
- Author and timestamp display

#### `admin/users.php` - User Management
Account administration:
- List all registered users
- Enable/disable user accounts
- View registration dates
- Admin status indicator
- Account status badges

### ⚙️ Core System Files

#### `inc/config.php` - Database Configuration
Central configuration for database connections:
```php
'driver' => 'mysql',  // or 'sqlite'

// Base URL - IMPORTANT for production deployment
// Set this to your web root path:
// - '' (empty) if at domain root: yourdomain.com/
// - '/smart_feedback' if in subfolder: yourdomain.com/smart_feedback/
// - Auto-detects if left empty
'base_url' => '',

'mysql' => [
    'host' => 'localhost',
    'port' => 3306,
    'dbname' => 'your_database',
    'user' => 'your_username',
    'pass' => 'your_password'
]
```

#### `inc/db.php` - Database & Auth Functions
Core functionality:
- **getPDO()**: Database connection handler
- **current_user()**: Returns logged-in user data
- **require_login()**: Enforces authentication
- **is_admin()**: Checks admin privileges
- **get_user_by_id()**: Fetch user information
- Session management and password verification

#### `inc/header.php` - Page Header
Universal header template:
- **Navigation Menu**: Responsive navbar with active states
- **Dark Mode Toggle**: Switch between light/dark themes (localStorage persistence)
- **User Profile Dropdown**: Avatar, profile link, admin panel, logout
- **Authentication Links**: Login/Register for guests
- **Tailwind CSS**: Modern utility-first CSS framework
- **Performance Optimized**: Removed backdrop-filter, optimized animations
- Auto-detection of local vs production environment

#### `inc/footer.php` - Page Footer
Closing HTML tags and scripts:
- Dark mode toggle JavaScript
- Mobile menu interactions
- Optimized for performance
- Closes body and html tags

### 🛡️ Security & Configuration

#### `.htaccess` - Apache Configuration
Critical security settings:
- **Protects Sensitive Files**: Blocks access to config.php, .htaccess
- **Blocks inc/ Directory**: Prevents direct access to core files
- **Security Headers**: XSS protection, clickjacking prevention
- **Disables Directory Listing**: Hides file structure
- **URL Rewriting**: Clean URL support
- **Compression**: Gzip for faster loading
- **Cache Control**: Browser caching for static assets

#### `setup.php` - Database Setup
One-time installation script:
- **Creates Tables**: users, feedback, votes, website_ratings, contact_messages
- **Default Admin**: Creates admin/admin123 account
- **Supports Both**: MySQL and SQLite
- **Auto-Detection**: Uses config.php settings
- ⚠️ **Must Delete After Setup** for security

#### `.gitignore` - Version Control
Excludes from Git:
- Database files (*.db, *.sqlite)
- User uploads (optional)
- IDE settings (.idea/, .vscode/)
- Environment files (.env)
- Logs and cache files

### 📦 Assets

#### `assets/default-avatar.png` - Default Avatar
Fallback profile picture used when:
- User hasn't uploaded an avatar
- Image fails to load
- New user registration
- Anonymous posting

## 🚀 Setup & Installation

### Requirements
- **PHP**: 7.4 or higher
- **Database**: MySQL 5.7+ / MariaDB 10.3+ or SQLite 3
- **Web Server**: Apache with mod_rewrite enabled
- **Extensions**: PDO, PDO_MySQL (or PDO_SQLite), GD or Imagick

### Installation Steps

1. **Upload Files** to your web server:
   ```
   /public_html/  or  /var/www/html/
   ```

2. **Set Permissions**:
   ```bash
   chmod 755 storage/profile_pics/
   ```

3. **Configure Database** in `inc/config.php`:
   - Set driver: 'mysql' or 'sqlite'
   - Update MySQL credentials
   - Or set SQLite path
   - **Important**: Set `base_url` if deploying to a subfolder
     - Example: `'base_url' => ''` (for root domain)
     - Example: `'base_url' => '/smart_feedback'` (for subfolder)
     - Leave empty for auto-detection

4. **Run Setup**:
   ```
   http://yourdomain.com/setup.php
   ```

5. **Delete setup.php** after installation!

6. **Login as Admin**:
   - Username: `admin`
   - Password: `admin123`
   - **Change this immediately!**

## ✨ Features Overview

### 🎯 User Features
- ✍️ Submit anonymous or public feedback
- 👍 👎 Like and dislike posts
- 📷 Upload and manage profile pictures
- 🔐 Secure password-protected accounts
- 👤 Comprehensive profile management
- 🌙 Dark mode with persistent preference
- ⭐ Rate and review the platform
- ✉️ Contact form for inquiries

### 🛡️ Admin Features
- 📊 Dashboard with real-time statistics
- 🔍 View and manage all feedback submissions
- 🗑️ Delete feedback and batch operations
- 👥 Enable/disable user accounts
- ✉️ Manage contact messages
- 📈 Monitor platform engagement
- ✨ Simplified interface (auto-approval workflow)

### 🎨 Design Features
- 📱 Fully responsive (mobile, tablet, desktop)
- 🌓 Dark/light mode toggle with localStorage
- 🎨 Modern glassmorphism UI with solid semi-transparent backgrounds
- ⚡ Performance optimized (removed backdrop-filter, optimized CSS)
- 🖼️ Profile picture integration
- 🏷️ Category-based filtering
- 🎯 Compact, centered card layout for better readability
- 💨 Smooth scrolling without lag

### 🔐 Security Features
- 🔒 Password hashing (bcrypt)
- 🛡️ SQL injection protection (PDO)
- 🚫 XSS prevention
- 🔐 CSRF protection considerations
- 📁 Protected configuration files
- 🚷 Access control (admin/user roles)

## 🗄️ Database Schema

### Tables

**users**
- User accounts with profiles
- Fields: id, username, email, password, profile_picture, student_id, program, year
- Admin flag and enabled status

**feedback**
- Feedback submissions
- Fields: id, user_id, title, description, category, anonymous, status, created_at
- Supports anonymous posting

**votes**
- Like/dislike tracking
- Fields: id, feedback_id, user_id, vote (-1 or 1)
- Unique constraint per user per feedback

**website_ratings**
- Platform ratings and reviews
- Fields: id, user_id, rating (1-5), review, created_at, updated_at
- One rating per user

**contact_messages**
- User inquiries and messages
- Fields: id, user_id, name, email, subject, message, created_at, status

## 🛠️ Customization

### Change Colors
Edit Tailwind configuration in `inc/header.php`:
```javascript
tailwind.config = {
  theme: {
    extend: {
      colors: {
        tech: {
          blue: '#667eea',
          purple: '#764ba2',
          pink: '#f093fb',
        }
      }
    }
  }
}
```

### Add Categories
Update category arrays in:
- `feed.php` (main feed filter dropdown)
- `submit.php` (submission form)
- `edit_feedback.php` (edit form)

### Modify Base URL
Edit `inc/header.php`:
```php
$base_url = '/your-subdirectory';  // or '' for root
```

### Upload Limits
Adjust in `profile.php`:
```php
$file_size > 2 * 1024 * 1024  // 2MB limit
```

## 🔒 Security Best Practices

1. ⚠️ **Delete `setup.php`** after installation
2. 🔑 Change default admin password immediately
3. 🔐 Use strong database passwords
4. 📁 Keep `.htaccess` file in place
5. 🔄 Regular database backups
6. 📊 Monitor admin access logs
7. 🛡️ Keep PHP and dependencies updated

## 📱 Browser Support

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

## 🐛 Troubleshooting

**Cannot upload profile pictures:**
- Check `storage/profile_pics/` permissions (755 or 777)
- Verify PHP `upload_max_filesize` setting

**Database connection errors:**
- Verify credentials in `inc/config.php`
- Check if database exists
- Ensure PDO extension is enabled

**Headers already sent:**
- Remove spaces before `<?php` tags
- Check file encoding (UTF-8 without BOM)
- Ensure no output before redirects

**Dark mode not persisting:**
- Check if localStorage is enabled in browser
- Clear browser cache

## 📄 License

This project is for educational purposes. Feel free to modify and distribute.

## 🤝 Contributing

Contributions welcome! Please test thoroughly before submitting changes.

---

**CampusPulse** - Empowering campus communities through transparent feedback 🎓✨
