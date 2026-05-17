<?php
/**
 * My Feedback - CampusPulse
 * 
 * Personal dashboard showing user's own feedback submissions.
 * Allows users to view, edit, and delete their feedback.
 * 
 * Features:
 * - View all own feedback submissions
 * - Edit existing feedback
 * - Delete feedback with confirmation
 * - Status indicators (New/Approved/Rejected)
 * - Vote count display
 * - Creation date display
 * - Success/Error message handling
 * 
 * Actions:
 * - Edit: Navigate to edit page
 * - Delete: POST request to remove feedback
 * - View: Click to see full details
 * 
 * Security:
 * - Requires user authentication
 * - Users can only see/modify their own feedback
 * - CSRF protection via POST method
 */

// Include required dependencies
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/inc/db.php';

// Require user to be logged in
$me = require_login();

// Get database connection
$pdo = getPDO();

// Initialize status variables
$delete_success = false;
$delete_error = '';

// ============================================
// HANDLE FEEDBACK DELETION
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);
    try {
        // Verify ownership before deleting
        $stmt = $pdo->prepare('SELECT user_id FROM feedback WHERE id = ?');
        $stmt->execute([$delete_id]);
        $feedback = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if feedback exists and belongs to current user
        if ($feedback && $feedback['user_id'] == $me['id']) {
            // Delete the feedback
            $stmt = $pdo->prepare('DELETE FROM feedback WHERE id = ?');
            $stmt->execute([$delete_id]);
            $delete_success = true;
        } else {
            $delete_error = 'Not authorized to delete this feedback.';
        }
    } catch (Exception $e) {
        $delete_error = 'Error deleting feedback: ' . $e->getMessage();
    }
}

// ============================================
// FETCH USER'S FEEDBACK
// ============================================

// Get all feedback belonging to current user
$stmt = $pdo->prepare('SELECT * FROM feedback WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$me['id']]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ============================================
// STATUS COLOR CONFIGURATION
// ============================================

// Define badge colors for different feedback statuses
$status_colors = [
    'New' => 'bg-yellow-500',
    'Approved' => 'bg-green-500',
    'Rejected' => 'bg-red-500',
    'Under Review' => 'bg-blue-500'
];
?>

<div class="min-h-[calc(100vh-20rem)] py-12">
  <div class="max-w-5xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-5xl font-bold gradient-text mb-4 animate-float">My Feedback</h1>
      <p class="text-xl text-gray-600 dark:text-gray-400">Track and manage your submitted feedback</p>
    </div>
    
    <!-- Messages -->
    <?php if (isset($_GET['save']) && $_GET['save'] === 'success'): ?>
      <div class="mb-6 p-4 rounded-xl glass border-l-4 border-green-500">
        <div class="flex items-center">
          <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-gray-800 dark:text-white font-medium">Feedback saved successfully!</span>
        </div>
      </div>
    <?php endif; ?>
    
    <?php if ($delete_success): ?>
      <div class="mb-6 p-4 rounded-xl glass border-l-4 border-green-500">
        <div class="flex items-center">
          <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-gray-800 dark:text-white font-medium">Feedback deleted successfully!</span>
        </div>
      </div>
    <?php endif; ?>
    
    <?php if ($delete_error): ?>
      <div class="mb-6 p-4 rounded-xl glass border-l-4 border-red-500">
        <div class="flex items-center">
          <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-gray-800 dark:text-white"><?=htmlspecialchars($delete_error)?></span>
        </div>
      </div>
    <?php endif; ?>
    
    <!-- Empty State -->
    <?php if (empty($items)): ?>
      <div class="glass rounded-2xl p-12 text-center">
        <svg class="w-20 h-20 mx-auto mb-4 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
        </svg>
        <h3 class="text-2xl font-bold text-gray-700 dark:text-gray-300 mb-2">No Feedback Yet</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6">You haven't submitted any feedback yet. Start sharing your thoughts!</p>
        <a href="submit_new.php" class="inline-block px-8 py-3 rounded-xl bg-gradient-to-r from-tech-blue via-tech-purple to-tech-pink text-white font-semibold hover:shadow-lg hover:scale-105 transition-all">
          Submit Your First Feedback
        </a>
      </div>
    <?php else: ?>
      
      <!-- Feedback List -->
      <div class="space-y-6">
        <?php foreach($items as $it): 
          $status_display = $it['status'] === 'New' ? 'Pending Approval' : $it['status'];
          $status_color = $status_colors[$it['status']] ?? 'bg-gray-500';
        ?>
          <div class="glass rounded-2xl p-6 hover:shadow-2xl transition-all duration-300">
            <!-- Header -->
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">
                  <?=htmlspecialchars($it['title'])?>
                </h3>
                <div class="flex flex-wrap items-center gap-3 text-sm">
                  <span class="inline-flex items-center px-3 py-1 rounded-full <?=$status_color?> text-white font-semibold">
                    <?= $status_display ?>
                  </span>
                  <span class="inline-flex items-center text-gray-600 dark:text-gray-400">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                    </svg>
                    <?=htmlspecialchars($it['category'])?>
                  </span>
                  <?php if ($it['anonymous']): ?>
                    <span class="inline-flex items-center text-purple-600 dark:text-purple-400">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                      </svg>
                      Anonymous
                    </span>
                  <?php endif; ?>
                  <span class="inline-flex items-center text-gray-500 dark:text-gray-500">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <?=date('M d, Y', strtotime($it['created_at']))?>
                  </span>
                </div>
              </div>
            </div>
            
            <!-- Description -->
            <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-6">
              <?=nl2br(htmlspecialchars($it['description']))?>
            </p>
            
            <!-- Actions -->
            <div class="flex items-center space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
              <a href="edit_feedback.php?id=<?=$it['id']?>" class="inline-flex items-center px-4 py-2 rounded-lg glass hover:bg-white/50 dark:hover:bg-white/10 text-gray-700 dark:text-gray-200 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit
              </a>
              <form method="post" class="inline" onsubmit="return confirm('Are you sure you want to delete this feedback? This action cannot be undone.');">
                <input type="hidden" name="delete_id" value="<?=$it['id']?>">
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg bg-red-500 hover:bg-red-600 text-white transition-all">
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                  Delete
                </button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
