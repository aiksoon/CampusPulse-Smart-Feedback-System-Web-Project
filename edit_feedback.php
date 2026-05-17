<?php
/**
 * Edit Feedback - CampusPulse
 * 
 * Interface for users to edit their own feedback submissions.
 * Only the feedback owner can access and modify their feedback.
 * 
 * Features:
 * - Edit feedback title and description
 * - Change feedback category
 * - Toggle anonymous posting
 * - Form validation
 * - Ownership verification
 * 
 * Security:
 * - Requires user authentication
 * - Ownership check (user can only edit their own feedback)
 * - XSS protection via htmlspecialchars
 * - SQL injection prevention via prepared statements
 * 
 * Workflow:
 * 1. Verify user is logged in
 * 2. Validate feedback ID and ownership
 * 3. Display edit form with current values
 * 4. Process updates on form submission
 * 5. Redirect to My Feedback page on success
 */

// Include database functions
require_once __DIR__ . '/inc/db.php';

// Require user to be logged in
$me = require_login();

// ============================================
// GET AND VALIDATE FEEDBACK ID
// ============================================

// Get feedback ID from URL or form submission
$id = intval($_GET['id'] ?? ($_POST['id'] ?? 0));
if (!$id) { 
    header('Location: my_feedback.php');
    exit;
}

// Get database connection
$pdo = getPDO();

// ============================================
// VERIFY OWNERSHIP
// ============================================

// Fetch feedback from database
$stmt = $pdo->prepare('SELECT * FROM feedback WHERE id = ?');
$stmt->execute([$id]);
$it = $stmt->fetch(PDO::FETCH_ASSOC);

// Verify feedback exists and belongs to current user
if (!$it || $it['user_id'] != $me['id']) { 
    header('Location: my_feedback.php');
    exit;
}

// Initialize status variables
$error = '';
$success = false;

// ============================================
// HANDLE FORM SUBMISSION
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $category = $_POST['category'];
    $anonymous = !empty($_POST['anonymous']) ? 1 : 0;
    
    // Validate required fields
    if (!$title || !$desc) {
        $error = 'Title and description are required.';
    } else {
        // Update feedback in database
        $stmt = $pdo->prepare('UPDATE feedback SET title = ?, description = ?, category = ?, anonymous = ? WHERE id = ?');
        $stmt->execute([$title, $desc, $category, $anonymous, $id]);
        // Redirect to My Feedback page with success message
        header('Location: my_feedback.php?save=success');
        exit;
    }
}

// Include page header
require_once __DIR__ . '/inc/header.php';
?>

<div class="min-h-[calc(100vh-20rem)] py-12">
  <div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-5xl font-bold gradient-text mb-4 animate-float">Edit Feedback</h1>
      <p class="text-xl text-gray-600 dark:text-gray-400">Update your feedback details</p>
    </div>
    
    <!-- Error Message -->
    <?php if ($error): ?>
      <div class="mb-6 p-4 rounded-xl glass border-l-4 border-red-500">
        <div class="flex items-center">
          <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-gray-800 dark:text-white"><?=htmlspecialchars($error)?></span>
        </div>
      </div>
    <?php endif; ?>
    
    <!-- Edit Form -->
    <div class="glass rounded-2xl p-8 shadow-2xl">
      <form method="post" class="space-y-6">
        <input type="hidden" name="id" value="<?=$it['id']?>">
        
        <!-- Title -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
            <span class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
              </svg>
              Title
            </span>
          </label>
          <input type="text" name="title" value="<?=htmlspecialchars($it['title'])?>" class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200" required>
        </div>
        
        <!-- Category -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
            <span class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
              </svg>
              Category
            </span>
          </label>
          <select name="category" class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200">
            <?php foreach(['Course','Infrastructure','Cafeterias'] as $c): ?>
              <option value="<?=$c?>" <?=($it['category']==$c)?'selected':''?>><?=$c?></option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <!-- Description -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
            <span class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
              Description
            </span>
          </label>
          <textarea name="description" rows="8" class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200 resize-none" required><?=htmlspecialchars($it['description'])?></textarea>
        </div>
        
        <!-- Anonymous -->
        <div class="flex items-center p-4 rounded-xl glass">
          <input type="checkbox" name="anonymous" id="anon" class="w-5 h-5 rounded border-gray-300 text-tech-blue focus:ring-tech-blue focus:ring-2" <?= $it['anonymous'] ? 'checked' : '' ?>>
          <label for="anon" class="ml-3 text-gray-700 dark:text-gray-200 cursor-pointer">
            <span class="font-semibold">Post as Anonymous</span>
          </label>
        </div>
        
        <!-- Buttons -->
        <div class="flex gap-4">
          <button type="submit" class="flex-1 px-6 py-4 rounded-xl bg-gradient-to-r from-tech-blue via-tech-purple to-tech-pink text-white font-semibold hover:shadow-lg hover:scale-105 transition-all">
            <span class="flex items-center justify-center text-lg">
              <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              Save Changes
            </span>
          </button>
          <a href="my_feedback.php" class="flex-1 px-6 py-4 rounded-xl glass hover:bg-white/50 dark:hover:bg-white/10 text-gray-700 dark:text-gray-200 font-semibold transition-all text-center">
            <span class="flex items-center justify-center text-lg">
              <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
              Cancel
            </span>
          </a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
