<?php
/**
 * Registration Page - CampusPulse
 * 
 * New user registration for the feedback system.
 * 
 * Features:
 * - Username, email, and password validation
 * - Optional student information (ID, program, year)
 * - Password strength requirements
 * - Duplicate username detection
 * - Automatic password hashing
 * 
 * Password Requirements:
 * - Minimum 8 characters
 * - Must contain uppercase letter
 * - Must contain lowercase letter
 * - Must contain number
 */

// Include database functions
require_once __DIR__ . '/inc/db.php';

// Initialize status variables
$error = '';
$success = false;

// ============================================
// HANDLE REGISTRATION FORM SUBMISSION
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'] ?? '';
    $student_id = trim($_POST['student_id'] ?? '');
    $program = $_POST['program'] ?? '';
    $year = $_POST['year'] ?? '';
    
    // ============================================
    // INPUT VALIDATION
    // ============================================
    
    // Check required fields
    if (!$username || !$email || !$password) {
        $error = 'Username, email, and password are required.';
    } 
    // Validate email format
    elseif (strpos($email, '@') === false) {
        $error = 'Email must contain @ symbol.';
    } 
    // Check minimum password length
    elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } 
    // Check password complexity
    elseif (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain uppercase, lowercase, and numbers.';
    } 
    // All validations passed
    else {
        // Get database connection
        $pdo = getPDO();
        
        // Hash password securely using PHP's default algorithm
        $pw = password_hash($password, PASSWORD_DEFAULT);
        
        // Prepare SQL statement to insert new user
        $stmt = $pdo->prepare('INSERT INTO users (username, email, password, student_id, program, year, registered_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
        
        try {
            // Execute insert with current ISO 8601 timestamp
            $stmt->execute([$username, $email, $pw, $student_id ?: null, $program ?: null, $year ?: null, date('c')]);
            $success = true;
        } catch (Exception $e) {
            // Registration failed (likely duplicate username)
            $error = 'Unable to register: ' . $e->getMessage();
        }
    }
}

// Include page header
require_once __DIR__ . '/inc/header.php';
?>

<div class="min-h-[calc(100vh-20rem)] flex items-center justify-center py-12 px-4">
  <div class="w-full max-w-2xl">
    <!-- Logo and Title -->
    <div class="text-center mb-8">
      <div class="w-20 h-20 rounded-full bg-gradient-to-r from-tech-blue via-tech-purple to-tech-pink flex items-center justify-center font-bold text-white text-3xl shadow-lg mb-4 animate-glow mx-auto">
        CP
      </div>
      <h1 class="text-4xl font-bold gradient-text mb-2">Join CampusPulse</h1>
      <p class="text-gray-600 dark:text-gray-400">Create your account and start sharing feedback</p>
    </div>
    
    <!-- Success Message -->
    <?php if ($success): ?>
      <div class="mb-6 p-4 rounded-xl glass border-l-4 border-green-500 animate-fade-in">
        <div class="flex items-center">
          <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <div>
            <span class="text-gray-800 dark:text-white font-medium">Registration successful!</span>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
              You can now <a href="login.php" class="text-tech-blue dark:text-tech-pink font-semibold hover:underline">login here</a>
            </p>
          </div>
        </div>
      </div>
    <?php endif; ?>
    
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
    
    <?php if (!$success): ?>
    <!-- Register Form -->
    <div class="glass rounded-2xl p-8 shadow-2xl">
      <form method="post" class="space-y-6">
        <div class="grid md:grid-cols-2 gap-6">
          <!-- Username Input -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
              <span class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Username <span class="text-red-500">*</span>
              </span>
            </label>
            <input 
              type="text" 
              name="username" 
              class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200" 
              placeholder="Choose a username" 
              required>
          </div>
          
          <!-- Email Input -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
              <span class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Email <span class="text-red-500">*</span>
              </span>
            </label>
            <input 
              type="email" 
              name="email" 
              class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200" 
              placeholder="your.email@example.com" 
              required>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Must contain @ symbol</p>
          </div>
        </div>
        
        <!-- Password Input -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
            <span class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
              </svg>
              Password <span class="text-red-500">*</span>
            </span>
          </label>
          <input 
            type="password" 
            name="password" 
            class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200" 
            placeholder="Create a strong password" 
            required>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Min 8 characters with uppercase, lowercase, and numbers</p>
        </div>
        
        <!-- Optional Fields Divider -->
        <div class="relative">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-4 bg-white/50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400">Optional Information</span>
          </div>
        </div>
        
        <div class="grid md:grid-cols-2 gap-6">
          <!-- Student ID Input -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
              <span class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                </svg>
                Student ID
              </span>
            </label>
            <input 
              type="text" 
              name="student_id" 
              class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200" 
              placeholder="Your student ID">
          </div>
          
          <!-- Year Select -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
              <span class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Year
              </span>
            </label>
            <select 
              name="year" 
              class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200">
              <option value="">Select year</option>
              <option>1</option>
              <option>2</option>
              <option>3</option>
              <option>4</option>
              <option>Postgraduate</option>
            </select>
          </div>
        </div>
        
        <!-- Program Select -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
            <span class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
              </svg>
              Program
            </span>
          </label>
          <select 
            name="program" 
            class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200">
            <option value="">Select your program</option>
            <option>Bachelor of Accounting (Information System) with Honours</option>
            <option>Bachelor of Accounting with Honours</option>
            <option>Bachelor of Business Administration with Honours</option>
            <option>Bachelor of Computer Science with Honours</option>
            <option>Bachelor of Communication with Honours</option>
            <option>Bachelor of Finance with Honours</option>
            <option>Bachelor of Marketing with Honours</option>
            <option>Bachelor of Science with Honours (Information Technology)</option>
            <option>Bachelor of Science with Honours (Multimedia)</option>
          </select>
        </div>
        
        <!-- Register Button -->
        <button 
          type="submit" 
          class="w-full px-6 py-3 rounded-xl bg-gradient-to-r from-tech-blue via-tech-purple to-tech-pink text-white font-semibold hover:shadow-lg hover:scale-105 transition-all">
          <span class="flex items-center justify-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
            </svg>
            Create Account
          </span>
        </button>
      </form>
      
      <!-- Login Link -->
      <div class="mt-6 text-center">
        <p class="text-gray-600 dark:text-gray-400">
          Already have an account? 
          <a href="login.php" class="font-semibold text-tech-blue dark:text-tech-pink hover:underline">
            Login here
          </a>
        </p>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
