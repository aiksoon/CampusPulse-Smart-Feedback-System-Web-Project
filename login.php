<?php
/**
 * Login Page - CampusPulse
 * 
 * User authentication page for the feedback system.
 * 
 * Features:
 * - Username and password validation
 * - Account status check (enabled/disabled)
 * - Password verification using PHP's password_verify()
 * - Session management
 * - Redirect to feed after successful login
 * 
 * Security:
 * - Passwords are hashed and never stored in plain text
 * - Accounts can be disabled by administrators
 */

// Include database functions
require_once __DIR__ . '/inc/db.php';

// Initialize error message variable
$error = '';

// ============================================
// HANDLE LOGIN FORM SUBMISSION
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Get database connection
    $pdo = getPDO();
    
    // Query user by username
    $stmt = $pdo->prepare('SELECT id, password, enabled FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Validate credentials and account status
    if (!$u || !$u['enabled'] || !password_verify($password, $u['password'])) {
        // Invalid username, disabled account, or wrong password
        $error = 'Invalid credentials or account disabled.';
    } else {
        // Login successful
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['login_success'] = true;
        header('Location: feed.php');
        exit;
    }
}

// Include page header (HTML/navigation)
require_once __DIR__ . '/inc/header.php';
?>

<div class="min-h-[calc(100vh-20rem)] flex items-center justify-center py-12 px-4">
  <div class="w-full max-w-md">
    <!-- Logo and Title -->
    <div class="text-center mb-8">
      <div class="w-20 h-20 rounded-full bg-gradient-to-r from-tech-blue via-tech-purple to-tech-pink flex items-center justify-center font-bold text-white text-3xl shadow-lg mb-4 animate-glow mx-auto">
        CP
      </div>
      <h1 class="text-4xl font-bold gradient-text mb-2">Welcome Back</h1>
      <p class="text-gray-600 dark:text-gray-400">Sign in to continue to CampusPulse</p>
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
    
    <!-- Login Form -->
    <div class="glass rounded-2xl p-8 shadow-2xl">
      <form method="post" class="space-y-6">
        <!-- Username Input -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
            <span class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
              Username
            </span>
          </label>
          <input 
            type="text" 
            name="username" 
            class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200" 
            placeholder="Enter your username" 
            required>
        </div>
        
        <!-- Password Input -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
            <span class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
              </svg>
              Password
            </span>
          </label>
          <input 
            type="password" 
            name="password" 
            class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200" 
            placeholder="Enter your password" 
            required>
        </div>
        
        <!-- Forgot Password Link -->
        <div class="text-right">
          <a href="reset.php" class="text-sm text-tech-blue dark:text-tech-pink hover:underline">
            Forgot Password?
          </a>
        </div>
        
        <!-- Login Button -->
        <button 
          type="submit" 
          class="w-full px-6 py-3 rounded-xl bg-gradient-to-r from-tech-blue via-tech-purple to-tech-pink text-white font-semibold hover:shadow-lg hover:scale-105 transition-all">
          <span class="flex items-center justify-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
            </svg>
            Sign In
          </span>
        </button>
      </form>
      
      <!-- Register Link -->
      <div class="mt-6 text-center">
        <p class="text-gray-600 dark:text-gray-400">
          Don't have an account? 
          <a href="register.php" class="font-semibold text-tech-blue dark:text-tech-pink hover:underline">
            Register here
          </a>
        </p>
      </div>
    </div>
    
    <!-- Features -->
    <div class="mt-8 grid grid-cols-3 gap-4 text-center">
      <div class="glass rounded-xl p-4">
        <div class="text-2xl mb-1">🔒</div>
        <p class="text-xs text-gray-600 dark:text-gray-400">Secure</p>
      </div>
      <div class="glass rounded-xl p-4">
        <div class="text-2xl mb-1">⚡</div>
        <p class="text-xs text-gray-600 dark:text-gray-400">Fast</p>
      </div>
      <div class="glass rounded-xl p-4">
        <div class="text-2xl mb-1">💜</div>
        <p class="text-xs text-gray-600 dark:text-gray-400">Community</p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
