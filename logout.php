<?php
/**
 * Logout - CampusPulse
 * 
 * This script handles user logout by destroying the session.
 * 
 * Process:
 * 1. Clears all session variables
 * 2. Destroys the session
 * 3. Redirects to homepage with logout success message
 * 
 * Usage: Access directly via logout.php or link from navigation
 */

// Include database functions
require_once __DIR__ . '/inc/db.php';

// Clear all session variables
session_unset();

// Destroy the session
session_destroy();

// Redirect to homepage with success message
header('Location: index.php?logout=success');
exit;
