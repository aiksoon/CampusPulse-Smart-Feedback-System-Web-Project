<?php
/**
 * Submit Feedback - CampusPulse
 * 
 * Page for users to submit new feedback about campus services.
 * Main entry point for creating feedback submissions.
 * 
 * Features:
 * - Submit new feedback with title and description
 * - Select feedback category
 * - Option to post anonymously
 * - Form validation
 * - Login requirement check
 * - Auto-approval of submissions
 * 
 * Categories:
 * - Course: Academic and teaching related
 * - Infrastructure: Buildings and facilities
 * - Cafeterias: Food services and dining
 * - Technology: IT systems and resources
 * - Support Services: Student support
 * - Campus Life: Events and activities
 * - Other: Miscellaneous feedback
 * 
 * Security:
 * - Requires user authentication
 * - XSS protection via htmlspecialchars
 * - SQL injection prevention
 * 
 * Status:
 * - All submissions auto-approved (status = 'Approved')
 * - Can be changed to 'New' for moderation
 */

// Include required dependencies
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/inc/db.php';

// Get current user (may be null if not logged in)
$me = current_user();

// Initialize status variables
$error = '';
$success = false;

// ============================================
// HANDLE FORM SUBMISSION
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Redirect to login if user is not authenticated
    if (!$me) { header('Location: login.php'); exit; }
    
    // Get and sanitize form data
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $category = $_POST['category'];
    $anonymous = !empty($_POST['anonymous']) ? 1 : 0;
    
    // Validate required fields
    if (!$title || !$desc) {
        $error = 'Title and description are required.';
    } else {
        // Insert feedback into database
        $pdo = getPDO();
        $stmt = $pdo->prepare('INSERT INTO feedback (user_id, title, description, category, anonymous, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
        // Auto-approve feedback (change to 'New' if moderation is needed)
        $stmt->execute([$me['id'], $title, $desc, $category, $anonymous, 'Approved', date('c')]);
        $success = true;
    }
}
?>

<div class="min-h-[calc(100vh-20rem)] py-12">
  <div class="max-w-3xl mx-auto">
    <!-- Header Section -->
    <div class="mb-8 text-center">
      <h1 class="text-5xl font-bold gradient-text mb-4 animate-float">Submit Feedback</h1>
      <p class="text-xl text-gray-600 dark:text-gray-400">Share your thoughts and help improve our campus</p>
    </div>
    
    <!-- Login Warning -->
    <?php if (!$me): ?>
      <div class="mb-6 p-6 rounded-xl glass border-l-4 border-yellow-500">
        <div class="flex items-center">
          <svg class="w-8 h-8 text-yellow-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
          </svg>
          <div>
            <p class="text-gray-800 dark:text-white font-semibold mb-1">Login Required</p>
            <p class="text-gray-600 dark:text-gray-400">
              You must <a href="login.php" class="text-tech-blue dark:text-tech-pink font-semibold hover:underline">login</a> to submit feedback.
            </p>
          </div>
        </div>
      </div>
    <?php endif; ?>
    
    <!-- Success Message -->
    <?php if ($success): ?>
      <div class="mb-6 p-6 rounded-xl glass border-l-4 border-green-500 animate-fade-in">
        <div class="flex items-center">
          <svg class="w-8 h-8 text-green-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <div>
            <p class="text-gray-800 dark:text-white font-semibold mb-1">Feedback Submitted Successfully!</p>
            <p class="text-gray-600 dark:text-gray-400">Your feedback has been published and is now visible to everyone.</p>
          </div>
        </div>
      </div>
    <?php endif; ?>
    
    <!-- Error Message -->
    <?php if ($error): ?>
      <div class="mb-6 p-6 rounded-xl glass border-l-4 border-red-500">
        <div class="flex items-center">
          <svg class="w-8 h-8 text-red-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <span class="text-gray-800 dark:text-white font-semibold"><?=htmlspecialchars($error)?></span>
        </div>
      </div>
    <?php endif; ?>
    
    <!-- Submit Form -->
    <div class="glass rounded-2xl p-8 shadow-2xl">
      <form method="post" class="space-y-6">
        <!-- Title Input -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
            <span class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
              </svg>
              Feedback Title <span class="text-red-500">*</span>
            </span>
          </label>
          <input 
            type="text" 
            name="title" 
            class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200" 
            placeholder="Enter a clear and descriptive title"
            <?= $me ? '' : 'disabled' ?>
            required>
        </div>
        
        <!-- Category Select -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
            <span class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
              </svg>
              Category <span class="text-red-500">*</span>
            </span>
          </label>
          <select 
            name="category" 
            class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200"
            <?= $me ? '' : 'disabled' ?>
            required>
            <option value="">Choose a category</option>
            <option value="Course">📚 Course</option>
            <option value="Infrastructure">🏢 Infrastructure</option>
            <option value="Cafeterias">🍽️ Cafeterias</option>
          </select>
        </div>
        
        <!-- Description Textarea -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
            <span class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
              </svg>
              Detailed Description <span class="text-red-500">*</span>
            </span>
          </label>
          <textarea 
            name="description" 
            rows="8" 
            class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200 resize-none" 
            placeholder="Provide detailed feedback to help us understand your concerns better..."
            <?= $me ? '' : 'disabled' ?>
            required></textarea>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Be specific and constructive in your feedback</p>
        </div>
        
        <!-- Anonymous Checkbox -->
        <div class="flex items-center p-4 rounded-xl glass">
          <input 
            type="checkbox" 
            name="anonymous" 
            id="anon" 
            class="w-5 h-5 rounded border-gray-300 text-tech-blue focus:ring-tech-blue focus:ring-2"
            <?= $me ? 'checked' : 'disabled' ?>>
          <label for="anon" class="ml-3 text-gray-700 dark:text-gray-200 cursor-pointer">
            <span class="font-semibold">Post as Anonymous</span>
            <p class="text-sm text-gray-500 dark:text-gray-400">Your identity will be hidden from other users</p>
          </label>
        </div>
        
        <!-- Submit Button -->
        <button 
          type="submit" 
          class="w-full px-6 py-4 rounded-xl bg-gradient-to-r from-tech-blue via-tech-purple to-tech-pink text-white font-semibold hover:shadow-lg hover:scale-105 transition-all <?= $me ? '' : 'opacity-50 cursor-not-allowed' ?>"
          <?= $me ? '' : 'disabled' ?>>
          <span class="flex items-center justify-center text-lg">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
            </svg>
            Submit Feedback
          </span>
        </button>
      </form>
    </div>
    
    <!-- Tips Section -->
    <div class="mt-8 grid md:grid-cols-3 gap-4">
      <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">💡</div>
        <h3 class="font-semibold text-gray-800 dark:text-white mb-1">Be Specific</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Provide clear details about your concern</p>
      </div>
      <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">🎯</div>
        <h3 class="font-semibold text-gray-800 dark:text-white mb-1">Be Constructive</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Suggest improvements when possible</p>
      </div>
      <div class="glass rounded-xl p-4 text-center">
        <div class="text-3xl mb-2">🤝</div>
        <h3 class="font-semibold text-gray-800 dark:text-white mb-1">Be Respectful</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400">Maintain a professional tone</p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
