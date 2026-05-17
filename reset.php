<?php
/**
 * Password Reset Page - CampusPulse
 * 
 * Allows users to reset their forgotten password.
 * 
 * Process:
 * 1. User enters their username
 * 2. System generates random 8-character password
 * 3. Password is updated in database
 * 4. New password displayed on screen
 * 5. Optionally sent via email (if mail server configured)
 * 
 * Note: In production, this should use a secure token-based
 * password reset process with email verification.
 */

// Include required files
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/inc/db.php';

// Initialize message variables
$msg = '';
$msg_type = ''; // 'success' or 'error'

// ============================================
// HANDLE PASSWORD RESET REQUEST
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get username from form
    $username = trim($_POST['username']);
    
    // Get database connection
    $pdo = getPDO();
    
    // Look up user by username
    $stmt = $pdo->prepare('SELECT id, email FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if user exists
    if (!$u) {
        $msg = 'User not found. Please check your username.';
        $msg_type = 'error';
    } else {
        // ============================================
        // GENERATE NEW PASSWORD
        // ============================================
        
        // Generate random 8-character password
        $newpw = substr(bin2hex(random_bytes(4)),0,8);
        
        // Hash the new password
        $hpw = password_hash($newpw, PASSWORD_DEFAULT);
        
        // Update password in database
        $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([$hpw, $u['id']]);
        
        // ============================================
        // ATTEMPT TO SEND EMAIL
        // ============================================
        
        $sent = false;
        $to = $u['email'];
        $subject = 'Password reset';
        $body = "Your new password: $newpw";
        
        // Try to send email (may not work on localhost without mail server)
        if (@mail($to, $subject, $body)) $sent = true;
        
        // Display success message with new password
        $msg = 'Password reset successfully! Your new password: <strong>' . htmlspecialchars($newpw) . '</strong>' . ($sent? ' (also sent to your email)': ' (email failed; displayed here for local testing)');
        $msg_type = 'success';
    }
}

?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-purple-900/20 dark:to-gray-900 flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-md animate-scale-in">
        
        <!-- Icon & Title -->
        <div class="text-center mb-8">
            <div class="inline-block mb-4">
                <div class="text-6xl animate-float">🔐</div>
            </div>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent mb-3">
                Forgot Password?
            </h1>
            <p class="text-gray-600 dark:text-gray-300">
                No worries! We'll help you reset it 🚀
            </p>
        </div>

        <!-- Main Card -->
        <div class="bg-white/95 dark:bg-gray-800/95 rounded-3xl shadow-2xl overflow-hidden border border-white/20">
            
            <!-- Card Header -->
            <div class="bg-gradient-to-r from-orange-500 to-red-500 p-6 text-white">
                <h2 class="text-xl font-bold flex items-center gap-2">
                    <span>🔑</span> Reset Your Password
                </h2>
                <p class="text-orange-100 mt-1 text-sm">Enter your username to continue</p>
            </div>

            <!-- Card Body -->
            <div class="p-8">
                
                <?php if ($msg && $msg_type === 'success'): ?>
                    <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 rounded-lg animate-slide-in">
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">✅</span>
                            <div class="flex-1">
                                <p class="text-green-700 dark:text-green-300 font-medium">Success!</p>
                                <p class="text-green-600 dark:text-green-400 text-sm mt-1"><?= $msg ?></p>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-green-200 dark:border-green-700">
                            <a href="login.php" 
                               class="inline-block w-full text-center px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-xl font-bold hover:shadow-lg transition-all transform hover:scale-[1.02]">
                                Login with New Password →
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if ($msg && $msg_type === 'error'): ?>
                    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-lg animate-slide-in">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">❌</span>
                            <p class="text-red-700 dark:text-red-300"><?= strip_tags($msg) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Info Box -->
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-xl">
                    <div class="flex items-start gap-3">
                        <span class="text-2xl">💡</span>
                        <div class="flex-1">
                            <p class="text-sm text-gray-700 dark:text-gray-300 font-medium mb-1">How it works:</p>
                            <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                <li>• Enter your username below</li>
                                <li>• We'll generate a new password instantly</li>
                                <li>• Your new password will be displayed here</li>
                                <li>• Email delivery may not work in local environment</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form method="post" class="space-y-6">
                    <div>
                        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">
                            👤 Username
                        </label>
                        <input name="username" 
                               type="text"
                               class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-orange-500 dark:focus:border-orange-400 focus:ring-2 focus:ring-orange-500/20 transition-all duration-200"
                               placeholder="Enter your username" 
                               required
                               autocomplete="username">
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            🔒 This is the username you use to log in
                        </p>
                    </div>

                    <button type="submit" 
                            class="w-full py-4 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-xl font-bold text-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
                        🔄 Reset Password
                    </button>
                </form>

                <!-- Divider -->
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white/70 dark:bg-gray-800/70 text-gray-500 dark:text-gray-400">
                            or
                        </span>
                    </div>
                </div>

                <!-- Back to Login -->
                <div class="text-center space-y-3">
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Remember your password?
                    </p>
                    <a href="login.php" 
                       class="inline-block px-8 py-3 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors border border-gray-200 dark:border-gray-600">
                        ← Back to Login
                    </a>
                </div>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="mt-6 text-center text-sm text-gray-500 dark:text-gray-400">
            <p>Need more help? <a href="contact.php" class="text-purple-600 dark:text-purple-400 hover:underline font-semibold">Contact Support</a></p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
