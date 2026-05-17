<?php
/**
 * Database Setup Script - CampusPulse
 * 
 * This script initializes the database with all required tables and a default admin user.
 * 
 * Features:
 * - Creates database structure for both MySQL and SQLite
 * - Drops existing tables (for fresh installation)
 * - Creates all necessary tables:
 *   - users: User accounts and profiles
 *   - feedback: Feedback submissions
 *   - votes: User votes on feedback
 *   - website_ratings: Platform ratings
 *   - contact_messages: Contact form submissions
 * - Creates default admin account (username: admin, password: admin123)
 * 
 * IMPORTANT SECURITY NOTES:
 * - Run this script only once during initial setup
 * - Delete or restrict access to this file after running
 * - Change the default admin password immediately after setup
 * 
 * Usage:
 * 1. Configure database settings in inc/config.php
 * 2. Access this file via browser (e.g., localhost/smart_feedback/setup.php)
 * 3. Delete or protect this file after successful setup
 */

// ============================================
// INITIALIZATION
// ============================================

// Load configuration
$cfg = require __DIR__ . '/inc/config.php';

// ============================================
// MYSQL/MARIADB SETUP
// ============================================

if (($cfg['driver'] ?? 'sqlite') === 'mysql') {
    // Extract MySQL configuration
    $m = $cfg['mysql'];
    $host = $m['host'] ?? '127.0.0.1';
    $port = $m['port'] ?? 3306;
    $dbname = $m['dbname'] ?? 'smart_feedback';
    $user = $m['user'] ?? 'root';
    $pass = $m['pass'] ?? '';

    // Connect to server and create database if not exists
    try {
        $tmp = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass);
        $tmp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $tmp->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    } catch (Exception $e) {
        echo "Failed to connect to MySQL server: " . htmlspecialchars($e->getMessage());
        exit;
    }

    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Disable foreign key checks and drop all tables
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("DROP TABLE IF EXISTS votes");
    $pdo->exec("DROP TABLE IF EXISTS website_ratings");
    $pdo->exec("DROP TABLE IF EXISTS contact_messages");
    $pdo->exec("DROP TABLE IF EXISTS feedback");
    $pdo->exec("DROP TABLE IF EXISTS users");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Dropped all existing tables.<br>";

    // create tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(191) NOT NULL UNIQUE,
        email VARCHAR(191) NOT NULL,
        password VARCHAR(255) NOT NULL,
        profile_picture VARCHAR(255) DEFAULT NULL,
        student_id VARCHAR(50),
        program VARCHAR(255),
        year INT,
        registered_at DATETIME NOT NULL,
        is_admin TINYINT(1) DEFAULT 0,
        enabled TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Add profile_picture column if it doesn't exist (for existing databases)
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) DEFAULT NULL AFTER password");
        echo "Added profile_picture column to users table.<br>";
    } catch (Exception $e) {
        // Column already exists, ignore error
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS feedback (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        category VARCHAR(100) NOT NULL,
        anonymous TINYINT(1) DEFAULT 0,
        status VARCHAR(50) DEFAULT 'New',
        created_at DATETIME NOT NULL,
        INDEX (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        feedback_id INT NOT NULL,
        user_id INT NOT NULL,
        vote TINYINT NOT NULL,
        UNIQUE KEY ux_vote (feedback_id, user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Create website ratings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS website_ratings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        rating TINYINT NOT NULL,
        review TEXT,
        created_at DATETIME NOT NULL,
        updated_at DATETIME,
        UNIQUE KEY ux_user_rating (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Create contact messages table
    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at DATETIME NOT NULL,
        status VARCHAR(50) DEFAULT 'New'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // create admin user
    $pw = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
    $stmt->execute(['admin']);
    if (!$stmt->fetchColumn()) {
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password, registered_at, is_admin) VALUES (?, ?, ?, ?, 1)');
        $stmt->execute(['admin', 'admin@example.com', $pw, date('Y-m-d H:i:s')]);
    }

    echo "MySQL database and tables created (or already exist). Database: $dbname<br>";
    echo "Admin user ensured: username=admin password=admin123<br>";
    echo "Please remove or protect setup.php after running it.";
    exit;
}

// Fallback to sqlite (existing behavior)
@mkdir(__DIR__ . '/database', 0755, true);
$dbfile = $cfg['sqlite']['path'] ?? (__DIR__ . '/database/feedback.db');

// Delete old database if exists (for development/reset purposes)
if (file_exists($dbfile)) {
    @unlink($dbfile);
}

$pdo = new PDO('sqlite:' . $dbfile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL,
    password TEXT NOT NULL,
    profile_picture TEXT,
    student_id TEXT,
    program TEXT,
    year INTEGER,
    registered_at TEXT NOT NULL,
    is_admin INTEGER DEFAULT 0,
    enabled INTEGER DEFAULT 1
)");

$pdo->exec("CREATE TABLE feedback (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    category TEXT NOT NULL,
    anonymous INTEGER DEFAULT 0,
    status TEXT DEFAULT 'New',
    created_at TEXT NOT NULL
)");

$pdo->exec("CREATE TABLE votes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    feedback_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    vote INTEGER NOT NULL,
    UNIQUE(feedback_id, user_id)
)");

$pdo->exec("CREATE TABLE website_ratings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    rating INTEGER NOT NULL,
    review TEXT,
    created_at TEXT NOT NULL,
    updated_at TEXT,
    UNIQUE(user_id)
)");

$pdo->exec("CREATE TABLE contact_messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    subject TEXT NOT NULL,
    message TEXT NOT NULL,
    created_at TEXT NOT NULL,
    status TEXT DEFAULT 'New'
)");

// create admin user (admin/admin123)
$pw = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO users (username, email, password, registered_at, is_admin) VALUES (?, ?, ?, ?, 1)');
$stmt->execute(['admin', 'admin@example.com', $pw, date('c')]);

echo "Database created at: $dbfile<br>";
echo "Admin user created: username=admin password=admin123<br>";
echo "Please remove or protect setup.php after running it.";
