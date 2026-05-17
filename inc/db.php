<?php
/**
 * Database Configuration and Helper Functions - CampusPulse
 * 
 * This file provides database connectivity and user authentication functions.
 * 
 * Features:
 * - Flexible PDO connection supporting both SQLite and MySQL/MariaDB
 * - Session management
 * - User authentication functions
 * - Profile picture management
 * - Base URL generation
 * 
 * Configuration: Set database options in inc/config.php
 */

// ============================================
// ENVIRONMENT SETUP
// ============================================

// Set timezone for consistent date/time handling
date_default_timezone_set('UTC');

// Start session for user authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Suppress error display in production
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// ============================================
// CONFIGURATION FUNCTIONS
// ============================================

/**
 * Get application configuration
 * Uses static variable to cache config after first load
 * 
 * @return array Configuration array from config.php
 */
/**
 * Get application configuration
 * Uses static variable to cache config after first load
 * 
 * @return array Configuration array from config.php
 */
function get_config() {
    static $cfg = null;
    // Return cached config if already loaded
    if ($cfg) return $cfg;
    // Load configuration file
    $cfg = require __DIR__ . '/config.php';
    return $cfg;
}

/**
 * Generate base URL for the application
 * Handles different installation paths (root, subfolder, localhost)
 * 
 * @param string $path Optional path to append to base URL
 * @return string Complete URL with base path
 */
/**
 * Generate base URL for the application
 * Handles different installation paths (root, subfolder, localhost)
 * 
 * @param string $path Optional path to append to base URL
 * @return string Complete URL with base path
 */
function base_url($path = '') {
    $cfg = get_config();
    $base = $cfg['base_url'] ?? '';
    
    // If base_url is not set in config, try to auto-detect from server variables
    if ($base === '' && isset($_SERVER['SCRIPT_NAME'])) {
        $script_dir = dirname($_SERVER['SCRIPT_NAME']);
        // Remove any /admin or similar from the path
        $base = preg_replace('#/admin.*#', '', $script_dir);
        if ($base === '/') $base = '';
    }
    
    // Append path if provided
    return $base . ($path ? '/' . ltrim($path, '/') : '');
}

// ============================================
// DATABASE CONNECTION
// ============================================

/**
 * Get PDO database connection
 * Supports both MySQL/MariaDB and SQLite
 * Uses singleton pattern (static variable) for connection reuse
 * 
 * @return PDO Database connection object
 * @throws Exception If database connection fails
 */
/**
 * Get PDO database connection
 * Supports both MySQL/MariaDB and SQLite
 * Uses singleton pattern (static variable) for connection reuse
 * 
 * @return PDO Database connection object
 * @throws Exception If database connection fails
 */
function getPDO() {
    static $pdo = null;
    // Return existing connection if already established
    if ($pdo) return $pdo;
    
    $cfg = get_config();
    
    // ============================================
    // MYSQL/MARIADB CONNECTION
    // ============================================
    if (($cfg['driver'] ?? 'sqlite') === 'mysql') {
        $m = $cfg['mysql'];
        $host = $m['host'] ?? '127.0.0.1';
        $port = $m['port'] ?? 3306;
        $dbname = $m['dbname'] ?? '';
        $user = $m['user'] ?? 'root';
        $pass = $m['pass'] ?? '';
        
        // Validate database name is set
        if (!$dbname) throw new Exception('MySQL dbname is not set in inc/config.php');
        
        // Build DSN (Data Source Name) for MySQL
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass);
    }
    // ============================================
    // SQLITE CONNECTION
    // ============================================
    else {
        $dbfile = $cfg['sqlite']['path'] ?? (__DIR__ . '/../database/feedback.db');
        if (!file_exists($dbfile)) {
            throw new Exception("Database file not found. Run setup.php to create it.");
        }
        $pdo = new PDO('sqlite:' . $dbfile);
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

function current_user() {
    if (!empty($_SESSION['user_id'])) {
        try {
            $pdo = getPDO();
            // Updated to include profile_picture column
            $stmt = $pdo->prepare('SELECT id, username, email, password, profile_picture, registered_at, is_admin, enabled, student_id, program, year FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $u = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($u && $u['enabled']) {
                // Add profile picture URL
                if ($u['profile_picture']) {
                    $u['profile_pic_url'] = base_url('storage/profile_pics/' . $u['profile_picture']);
                } else {
                    $u['profile_pic_url'] = base_url('assets/default-avatar.png');
                }
                return $u;
            }
        } catch (Exception $e) {
            return null;
        }
    }
    return null;
}

function require_login() {
    $u = current_user();
    if (!$u) {
        header('Location: login.php');
        exit;
    }
    return $u;
}

function is_admin() {
    $u = current_user();
    return $u && !empty($u['is_admin']);
}

// Function to update user's profile picture
function update_profile_picture($user_id, $filename) {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare('UPDATE users SET profile_picture = ? WHERE id = ?');
        $stmt->execute([$filename, $user_id]);
        return true;
    } catch (Exception $e) {
        error_log("Failed to update profile picture: " . $e->getMessage());
        return false;
    }
}

// Function to get user by ID (for admin operations)
function get_user_by_id($user_id) {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT id, username, email, profile_picture, student_id, program, year, registered_at, is_admin, enabled FROM users WHERE id = ?');
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Add profile picture URL
            if ($user['profile_picture']) {
                $user['profile_pic_url'] = base_url('storage/profile_pics/' . $user['profile_picture']);
            } else {
                $user['profile_pic_url'] = base_url('assets/default-avatar.png');
            }
        }
        
        return $user;
    } catch (Exception $e) {
        error_log("Failed to get user by ID: " . $e->getMessage());
        return null;
    }
}