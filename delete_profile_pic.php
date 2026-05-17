<?php
/**
 * Delete Profile Picture - CampusPulse
 * 
 * This script handles the deletion of a user's profile picture.
 * 
 * Process:
 * 1. Verifies user is logged in
 * 2. Deletes physical file from storage
 * 3. Updates database to remove reference
 * 4. Redirects back to profile page
 * 
 * Security: Requires user authentication
 */

// Include database functions
require_once __DIR__ . '/inc/db.php';

// Require user to be logged in
$me = require_login();
$pdo = getPDO();

// ============================================
// DELETE PROFILE PICTURE
// ============================================

// Check if user has a profile picture
// Check if user has a profile picture
if ($me['profile_picture']) {
    // Construct full file path
    $file_path = __DIR__ . '/storage/profile_pics/' . $me['profile_picture'];
    
    // Delete physical file if it exists
    if (file_exists($file_path)) {
        unlink($file_path);
    }
    
    // Update database to remove profile picture reference
    $stmt = $pdo->prepare('UPDATE users SET profile_picture = NULL WHERE id = ?');
    $stmt->execute([$me['id']]);
}

// Redirect back to profile page
header('Location: profile.php');
exit;
?>