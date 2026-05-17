<?php
/**
 * Delete Feedback - CampusPulse
 * 
 * This script handles the deletion of feedback posts.
 * 
 * Security:
 * - Requires user to be logged in
 * - Only POST requests are accepted
 * - Users can only delete their own feedback
 * - Admins can delete any feedback
 */

// Include required files
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/inc/db.php';

// Require user authentication
$me = require_login();

// Only accept POST requests (prevent direct access)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: my_feedback.php'); exit; }

// Get and validate feedback ID
$id = intval($_POST['id'] ?? 0);
if (!$id) { header('Location: my_feedback.php'); exit; }

// Get database connection
$pdo = getPDO();

// ============================================
// VERIFY OWNERSHIP AND PERMISSIONS
// ============================================

// Get the owner (user_id) of the feedback
$stmt = $pdo->prepare('SELECT user_id FROM feedback WHERE id = ?');
$stmt->execute([$id]);
$owner = $stmt->fetchColumn();

// Check if feedback exists
if (!$owner) { echo '<div class="alert alert-danger">Not found</div>'; require_once __DIR__ . '/inc/footer.php'; exit; }

// Check if user has permission to delete (must be owner or admin)
if ($owner != $me['id'] && !is_admin()) { echo '<div class="alert alert-danger">Not allowed</div>'; require_once __DIR__ . '/inc/footer.php'; exit; }

// ============================================
// DELETE FEEDBACK
// ============================================

// Delete the feedback from database
$stmt = $pdo->prepare('DELETE FROM feedback WHERE id = ?');
$stmt->execute([$id]);

// Display success message
echo '<div class="alert alert-success">Deleted.</div>';
echo '<p><a class="btn btn-secondary" href="my_feedback.php">Back to My Feedback</a></p>';

require_once __DIR__ . '/inc/footer.php';
