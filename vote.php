<?php
/**
 * Vote Handler - CampusPulse
 * 
 * This AJAX endpoint handles voting on feedback posts.
 * 
 * Features:
 * - Upvote (vote = 1) or downvote (vote = -1)
 * - Toggle vote (clicking same vote removes it)
 * - Change vote (clicking opposite vote changes it)
 * - Returns JSON response with updated vote counts
 * 
 * Security: Requires user authentication
 * Returns: JSON with success status, counts, and user's current vote
 */

// Include database functions
require_once __DIR__ . '/inc/db.php';

// Set response type to JSON
header('Content-Type: application/json');

// Get current user
$me = current_user();

// Check if user is logged in
if (!$me) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// ============================================
// PROCESS VOTE SUBMISSION
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and validate form data
    $fid = intval($_POST['feedback_id']);
    $vote = intval($_POST['vote']);
    // Ensure vote is either 1 (upvote) or -1 (downvote)
    // Ensure vote is either 1 (upvote) or -1 (downvote)
    if (!in_array($vote, [1, -1])) $vote = 1;
    
    $pdo = getPDO();
    
    // ============================================
    // CHECK EXISTING VOTE
    // ============================================
    
    // Check if user already voted on this feedback
    $stmt_check = $pdo->prepare('SELECT vote FROM votes WHERE feedback_id = ? AND user_id = ?');
    $stmt_check->execute([$fid, $me['id']]);
    $existing_vote = $stmt_check->fetch(PDO::FETCH_ASSOC);
    
    // ============================================
    // HANDLE VOTE LOGIC
    // ============================================
    
    if ($existing_vote && intval($existing_vote['vote']) === $vote) {
        // User clicked same vote - remove vote (toggle off)
        $stmt_delete = $pdo->prepare('DELETE FROM votes WHERE feedback_id = ? AND user_id = ?');
        $stmt_delete->execute([$fid, $me['id']]);
        $current_vote = 0; // No vote
    } else {
        // Different vote or no previous previous vote - insert or update
        $stmt = $pdo->prepare('INSERT INTO votes (feedback_id, user_id, vote) VALUES (?, ?, ?)');
        try {
            // Try to insert new vote
            $stmt->execute([$fid, $me['id'], $vote]);
        } catch (Exception $e) {
            // Vote already exists (unique constraint) - update instead
            $stmt = $pdo->prepare('UPDATE votes SET vote = ? WHERE feedback_id = ? AND user_id = ?');
            $stmt->execute([$vote, $fid, $me['id']]);
        }
        $current_vote = $vote;
    }
    
    // ============================================
    // GET UPDATED VOTE COUNTS
    // ============================================
    
    // Count total upvotes (vote = 1)
    $stmt_likes = $pdo->prepare('SELECT COUNT(*) FROM votes WHERE feedback_id = ? AND vote = 1');
    $stmt_likes->execute([$fid]);
    $likes = $stmt_likes->fetchColumn();
    
    // Count total downvotes (vote = -1)
    $stmt_dislikes = $pdo->prepare('SELECT COUNT(*) FROM votes WHERE feedback_id = ? AND vote = -1');
    $stmt_dislikes->execute([$fid]);
    $dislikes = $stmt_dislikes->fetchColumn();
    
    echo json_encode(['success' => true, 'likes' => $likes, 'dislikes' => $dislikes, 'current_vote' => $current_vote]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>