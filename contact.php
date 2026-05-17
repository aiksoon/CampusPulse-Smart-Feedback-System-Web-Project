<?php
/**
 * Contact Page - CampusPulse
 * 
 * This page allows users to send messages to administrators.
 * Users can contact the team with questions, issues, or feedback.
 * 
 * Features:
 * - Contact form with validation
 * - Auto-fills user info if logged in
 * - Stores messages in database
 * - Email and subject validation
 * - Success/error message display
 */

// Include required files
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/inc/db.php';

// Get current user (if logged in)
$me = current_user();

// Initialize message variables
$success = '';
$error = '';

// ============================================
// HANDLE CONTACT FORM SUBMISSION
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validate required fields
    if (!$name || !$email || !$subject || !$message) {
        $error = 'All fields are required.';
    } 
    // Validate email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } 
    // All validations passed - save message
    else {
        try {
            $pdo = getPDO();
            // Prepare SQL statement to insert message
            $stmt = $pdo->prepare('INSERT INTO contact_messages (user_id, name, email, subject, message, created_at, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
            // Get user ID if logged in, otherwise null
            $user_id = $me ? $me['id'] : null;
            // Execute insert with current timestamp and 'New' status
            $stmt->execute([$user_id, $name, $email, $subject, $message, date('Y-m-d H:i:s'), 'New']);
            $success = 'Your message has been sent successfully! We will get back to you soon.';
            // Clear form fields after successful submission
            $name = $email = $subject = $message = '';
        } catch (Exception $e) {
            // Database error occurred
            $error = 'Failed to send message. Please try again later.';
        }
    }
}

// ============================================
// AUTO-FILL USER INFORMATION
// ============================================
// Pre-fill form with user's info if logged in and not a form submission
if ($me && !isset($_POST['name'])) {
    $name = $me['username'] ?? '';
    $email = $me['email'] ?? '';
}
?>

<div class="min-h-[calc(100vh-20rem)] py-12">
  <div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-12 text-center">
      <h1 class="text-5xl font-bold gradient-text mb-4 animate-float">Contact Us</h1>
      <p class="text-xl text-gray-600 dark:text-gray-400">We're here to help and answer any question you might have</p>
    </div>
    
    <div class="grid md:grid-cols-3 gap-8">
      <!-- Contact Form -->
      <div class="md:col-span-2">
        <?php if ($success): ?>
          <div class="mb-6 p-6 rounded-xl glass border-l-4 border-green-500 animate-fade-in">
            <div class="flex items-center">
              <svg class="w-8 h-8 text-green-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <span class="text-gray-800 dark:text-white font-medium"><?=$success?></span>
            </div>
          </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
          <div class="mb-6 p-6 rounded-xl glass border-l-4 border-red-500">
            <div class="flex items-center">
              <svg class="w-8 h-8 text-red-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <span class="text-gray-800 dark:text-white"><?=htmlspecialchars($error)?></span>
            </div>
          </div>
        <?php endif; ?>
        
        <div class="glass rounded-2xl p-8">
          <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">Send us a message</h2>
          <form method="post" class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Your Name</label>
                <input type="text" name="name" value="<?=htmlspecialchars($name ?? '')?>" class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200" placeholder="John Doe" required>
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Email Address</label>
                <input type="email" name="email" value="<?=htmlspecialchars($email ?? '')?>" class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200" placeholder="john@example.com" required>
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Subject</label>
              <input type="text" name="subject" value="<?=htmlspecialchars($subject ?? '')?>" class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200" placeholder="How can we help?" required>
            </div>
            
            <div>
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Message</label>
              <textarea name="message" rows="6" class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200 resize-none" placeholder="Tell us more about your inquiry..." required><?=htmlspecialchars($message ?? '')?></textarea>
            </div>
            
            <button type="submit" class="w-full px-6 py-4 rounded-xl bg-gradient-to-r from-tech-blue via-tech-purple to-tech-pink text-white font-semibold hover:shadow-lg hover:scale-105 transition-all">
              <span class="flex items-center justify-center text-lg">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Send Message
              </span>
            </button>
          </form>
        </div>
      </div>
      
      <!-- Contact Info -->
      <div class="space-y-6">
        <div class="glass rounded-2xl p-6">
          <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Contact Information</h3>
          
          <div class="space-y-4">
            <div class="flex items-start">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-tech-blue to-tech-purple flex items-center justify-center mr-4 flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
              </div>
              <div>
                <p class="font-semibold text-gray-800 dark:text-white">Email</p>
                <p class="text-gray-600 dark:text-gray-400">info@campuspulse.edu</p>
              </div>
            </div>
            
            <div class="flex items-start">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-tech-purple to-tech-pink flex items-center justify-center mr-4 flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
              </div>
              <div>
                <p class="font-semibold text-gray-800 dark:text-white">Location</p>
                <p class="text-gray-600 dark:text-gray-400">Campus Administration Building</p>
              </div>
            </div>
            
            <div class="flex items-start">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-tech-pink to-tech-blue flex items-center justify-center mr-4 flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
              <div>
                <p class="font-semibold text-gray-800 dark:text-white">Office Hours</p>
                <p class="text-gray-600 dark:text-gray-400">Mon-Fri: 9AM-5PM</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="glass rounded-2xl p-6">
          <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4">Quick Links</h3>
          <div class="space-y-2">
            <a href="about.php" class="block px-4 py-2 rounded-lg hover:bg-white/50 dark:hover:bg-white/10 text-gray-700 dark:text-gray-200 transition-all">About Us</a>
            <a href="submit.php" class="block px-4 py-2 rounded-lg hover:bg-white/50 dark:hover:bg-white/10 text-gray-700 dark:text-gray-200 transition-all">Submit Feedback</a>
            <a href="rating.php" class="block px-4 py-2 rounded-lg hover:bg-white/50 dark:hover:bg-white/10 text-gray-700 dark:text-gray-200 transition-all">Rate Us</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
