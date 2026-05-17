<?php
/**
 * User Profile - CampusPulse
 * 
 * User profile management page for viewing and updating account information.
 * Handles profile picture uploads, personal info updates, and password changes.
 * 
 * Features:
 * - View profile information
 * - Upload/change profile picture (JPG, PNG, GIF, WebP)
 * - Update email and student details
 * - Change password with validation
 * - Delete profile picture
 * - Activity statistics display
 * 
 * Security:
 * - Requires user authentication
 * - File type validation for images
 * - File size limit (2MB)
 * - Password confirmation required
 * - Old password verification
 * - XSS protection
 * 
 * File Upload:
 * - Allowed types: JPEG, PNG, GIF, WebP
 * - Max size: 2MB
 * - Unique filename generation
 * - Old file cleanup
 * 
 * Note: All POST operations happen BEFORE header inclusion
 * to allow redirects without 'headers already sent' errors.
 */

// Process all POST requests and redirects BEFORE including header
require_once __DIR__ . '/inc/db.php';

// Require user to be logged in
$me = require_login();

// Get database connection
$pdo = getPDO();

// Initialize status message variables
$edit_error = '';
$edit_success = false;
$pwd_error = '';
$pwd_success = false;
$pic_error = '';
$pic_success = false;

// ============================================
// CHECK FOR REDIRECT SUCCESS MESSAGE
// ============================================

// Check for successful picture upload from redirect
if (isset($_GET['pic_upload']) && $_GET['pic_upload'] === 'success') {
    $pic_success = 'Profile picture updated successfully!';
    // Force refresh user data from database after redirect
    $me = current_user();
}

// ============================================
// HANDLE PROFILE PICTURE UPLOAD
// ============================================
// Must be handled BEFORE header.php to allow redirect

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        // ============================================
        // FILE VALIDATION
        // ============================================
        
        // Define allowed image MIME types
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['profile_picture']['type'];
        $file_size = $_FILES['profile_picture']['size'];
        
        // Validate file type
        if (!in_array($file_type, $allowed_types)) {
            $pic_error = 'Only JPG, PNG, GIF, and WebP images are allowed.';
        } 
        // Validate file size (2MB maximum)
        elseif ($file_size > 2 * 1024 * 1024) {
            $pic_error = 'File size must be less than 2MB.';
        } 
        // File passed validation
        else {
            // Generate unique filename
            $extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $filename = 'user_' . $me['id'] . '_' . time() . '.' . strtolower($extension);
            
            // WINDOWS/XAMPP PATH - Absolute path
            $upload_dir = __DIR__ . '/storage/profile_pics/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $upload_path = $upload_dir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                // Delete old profile picture if exists
                if ($me['profile_picture'] && file_exists($upload_dir . $me['profile_picture'])) {
                    unlink($upload_dir . $me['profile_picture']);
                }
                
                // Update database
                try {
                    $stmt = $pdo->prepare('UPDATE users SET profile_picture = ? WHERE id = ?');
                    if ($stmt->execute([$filename, $me['id']])) {
                        // Redirect to same page with success message to refresh the page and show new picture
                        header('Location: profile.php?pic_upload=success&t=' . time());
                        exit;
                    } else {
                        $pic_error = 'Failed to update database.';
                        // Delete the uploaded file if DB update failed
                        if (file_exists($upload_path)) {
                            unlink($upload_path);
                        }
                    }
                } catch (Exception $e) {
                    $pic_error = 'Database error: ' . $e->getMessage();
                    if (file_exists($upload_path)) {
                        unlink($upload_path);
                    }
                }
            } else {
                $pic_error = 'Failed to upload file. Please check directory permissions.';
            }
        }
    } else {
        $pic_error = 'File upload error (code: ' . $_FILES['profile_picture']['error'] . '). Please try again.';
    }
}

// Handle profile edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_profile') {
    $email = trim($_POST['email'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');
    $program = trim($_POST['program'] ?? '');
    $year = trim($_POST['year'] ?? '');
    
    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $edit_error = 'Invalid email address.';
    } else {
        try {
            $stmt = $pdo->prepare('UPDATE users SET email = ?, student_id = ?, program = ?, year = ? WHERE id = ?');
            $stmt->execute([
                $email,
                !empty($student_id) ? $student_id : null,
                !empty($program) ? $program : null,
                !empty($year) ? $year : null,
                $me['id']
            ]);
            $edit_success = true;
            // Refresh user data
            $me = current_user();
        } catch (Exception $e) {
            $edit_error = 'Error updating profile: ' . $e->getMessage();
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($old_password)) {
        $pwd_error = 'Current password is required.';
    } elseif (!isset($me['password']) || !password_verify($old_password, $me['password'])) {
        $pwd_error = 'Current password is incorrect.';
    } elseif (strlen($new_password) < 8) {
        $pwd_error = 'New password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
        $pwd_error = 'Password must contain uppercase, lowercase, and numbers.';
    } elseif ($new_password !== $confirm_password) {
        $pwd_error = 'Passwords do not match.';
    } else {
        try {
            $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([password_hash($new_password, PASSWORD_DEFAULT), $me['id']]);
            $pwd_success = true;
        } catch (Exception $e) {
            $pwd_error = 'Error changing password: ' . $e->getMessage();
        }
    }
}

// NOW include header after all redirects are done
require_once __DIR__ . '/inc/header.php';

// Get base URL (same logic as header.php)
if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    $base_url = '/smart_feedback';
} else {
    $base_url = '';  // Production - root directory
}

// Get profile picture with cache-busting timestamp
$profile_display = $base_url . '/assets/default-avatar.png';
if (!empty($me['profile_picture'])) {
    $profile_display = $base_url . '/storage/profile_pics/' . $me['profile_picture'] . '?t=' . time();
}
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-purple-900/20 dark:to-gray-900 py-8">
    <div class="container mx-auto px-4 max-w-5xl">
        
        <!-- Hero Header -->
        <div class="text-center mb-8 animate-fade-in">
            <div class="inline-block mb-4">
                <div class="text-6xl animate-float">👤</div>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent mb-3">
                My Profile
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-300">
                Manage your personal information and settings ⚙️
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            
            <!-- Profile Picture Card -->
            <div class="md:col-span-1">
                <div class="bg-white/95 dark:bg-gray-800/95 rounded-3xl shadow-2xl p-6 border border-white/20 sticky top-6">
                    <div class="text-center">
                        <div class="relative inline-block mb-4">
                            <img src="<?= htmlspecialchars($profile_display) ?>" 
                                 alt="Profile Picture" 
                                 class="w-40 h-40 rounded-full object-cover ring-4 ring-purple-500/50 shadow-xl"
                                 onerror="this.src='<?= $base_url ?>/assets/default-avatar.png';">
                            <div class="absolute bottom-0 right-0 bg-gradient-to-r from-green-400 to-blue-500 text-white rounded-full p-2 shadow-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                        </div>
                        
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">
                            <?= htmlspecialchars($me['username']) ?>
                        </h2>
                        
                        <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">
                            Member since <?= date('M Y', strtotime($me['registered_at'])) ?>
                        </p>

                        <?php if ($pic_success): ?>
                            <div class="mb-4 p-3 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 rounded-lg text-sm animate-slide-in">
                                <div class="flex items-center gap-2">
                                    <span>✅</span>
                                    <p class="text-green-700 dark:text-green-300"><?= htmlspecialchars($pic_success) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($pic_error): ?>
                            <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-lg text-sm animate-slide-in">
                                <div class="flex items-center gap-2">
                                    <span>❌</span>
                                    <p class="text-red-700 dark:text-red-300"><?= htmlspecialchars($pic_error) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="post" enctype="multipart/form-data" class="space-y-4">
                            <div>
                                <label class="block w-full cursor-pointer">
                                    <div class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                                        📸 Change Photo
                                    </div>
                                    <input type="file" 
                                           name="profile_picture" 
                                           accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" 
                                           class="hidden" 
                                           onchange="this.form.submit()">
                                </label>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                    Max 2MB • JPG, PNG, GIF, WebP
                                </p>
                            </div>
                            
                            <?php if ($me['profile_picture']): ?>
                                <button type="button" 
                                        onclick="confirmDeletePicture()" 
                                        class="w-full px-4 py-2 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-xl font-medium hover:bg-red-100 dark:hover:bg-red-900/50 transition-all">
                                    🗑️ Remove Photo
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="md:col-span-2 space-y-6">
                
                <!-- Profile Information Card -->
                <div id="profileCard" class="bg-white/95 dark:bg-gray-800/95 rounded-3xl shadow-2xl overflow-hidden border border-white/20">
                    <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 p-6 flex justify-between items-center">
                        <div class="text-white">
                            <h2 class="text-2xl font-bold flex items-center gap-3">
                                <span>ℹ️</span> Profile Information
                            </h2>
                        </div>
                        <button type="button" 
                                onclick="toggleEditForm()" 
                                class="px-4 py-2 bg-white/50 text-white rounded-xl font-semibold hover:bg-white/60 transition-colors">
                            ✏️ Edit
                        </button>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <?php if ($edit_success): ?>
                            <div class="p-4 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 rounded-lg animate-slide-in">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">✅</span>
                                    <p class="text-green-700 dark:text-green-300 font-medium">Profile updated successfully!</p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="bg-white/95 dark:bg-gray-700/95 rounded-xl p-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">📧 Email</p>
                                <p class="text-gray-800 dark:text-white font-medium break-all"><?= htmlspecialchars($me['email']) ?></p>
                            </div>
                            
                            <div class="bg-white/95 dark:bg-gray-700/95 rounded-xl p-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">🎓 Student ID</p>
                                <p class="text-gray-800 dark:text-white font-medium">
                                    <?= !empty($me['student_id']) ? htmlspecialchars($me['student_id']) : '<span class="text-gray-400">Not set</span>' ?>
                                </p>
                            </div>
                            
                            <div class="bg-white/95 dark:bg-gray-700/95 rounded-xl p-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">📚 Program</p>
                                <p class="text-gray-800 dark:text-white font-medium">
                                    <?= !empty($me['program']) ? htmlspecialchars($me['program']) : '<span class="text-gray-400">Not set</span>' ?>
                                </p>
                            </div>
                            
                            <div class="bg-white/95 dark:bg-gray-700/95 rounded-xl p-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">📅 Year</p>
                                <p class="text-gray-800 dark:text-white font-medium">
                                    <?= !empty($me['year']) ? 'Year ' . htmlspecialchars($me['year']) : '<span class="text-gray-400">Not set</span>' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Profile Form (Hidden by default) -->
                <div id="editFormCard" class="bg-white/95 dark:bg-gray-800/95 rounded-3xl shadow-2xl overflow-hidden border border-white/20" style="display: none;">
                    <div class="bg-gradient-to-r from-purple-600 to-pink-600 p-6">
                        <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                            <span>✏️</span> Edit Profile
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <?php if ($edit_error): ?>
                            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-lg animate-slide-in">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">❌</span>
                                    <p class="text-red-700 dark:text-red-300"><?= htmlspecialchars($edit_error) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" class="space-y-4">
                            <input type="hidden" name="action" value="edit_profile">
                            
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">📧 Email *</label>
                                <input name="email" 
                                       type="email" 
                                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-purple-500 dark:focus:border-purple-400 focus:ring-2 focus:ring-purple-500/20 transition-all"
                                       value="<?= htmlspecialchars($me['email']) ?>" 
                                       required>
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">🎓 Student ID</label>
                                <input name="student_id" 
                                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-purple-500 dark:focus:border-purple-400 focus:ring-2 focus:ring-purple-500/20 transition-all"
                                       placeholder="Optional"
                                       value="<?= htmlspecialchars($me['student_id'] ?? '') ?>">
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">📚 Program</label>
                                <select name="program" 
                                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-purple-500 dark:focus:border-purple-400 focus:ring-2 focus:ring-purple-500/20 transition-all">
                                    <option value="">Select program (optional)</option>
                                    <?php 
                                    $programs = ['Accounting', 'Actuarial Science', 'Aerospace Engineering', 'Agricultural Science', 'Architecture', 'Biomedical Science', 'Business Administration', 'Chemical Engineering', 'Chemistry', 'Civil Engineering', 'Commerce', 'Computer Science', 'Construction Management', 'Dental Surgery', 'Economics', 'Education', 'Electrical Engineering', 'Electronics Engineering', 'Engineering Science', 'Environmental Engineering', 'Fashion Design', 'Finance', 'Food Science & Technology', 'Geology', 'Geotechnical Engineering', 'Graphics Design', 'Human Resource Management', 'Industrial Design', 'Information Technology', 'Journalism', 'Law', 'Marine Science', 'Marketing', 'Materials Engineering', 'Mathematics', 'Mechanical Engineering', 'Medicine', 'Microbiology', 'Nursing', 'Petroleum Engineering', 'Pharmacy', 'Physics', 'Psychology', 'Quantity Surveying', 'Software Engineering', 'Statistics'];
                                    foreach ($programs as $p): 
                                    ?>
                                        <option value="<?=$p?>" <?= (isset($me['program']) && $me['program'] === $p) ? 'selected' : '' ?>><?=$p?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">📅 Year</label>
                                <select name="year" 
                                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-purple-500 dark:focus:border-purple-400 focus:ring-2 focus:ring-purple-500/20 transition-all">
                                    <option value="">Select year (optional)</option>
                                    <?php for ($i = 1; $i <= 4; $i++): ?>
                                        <option value="<?=$i?>" <?= (isset($me['year']) && $me['year'] == $i) ? 'selected' : '' ?>>Year <?=$i?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            
                            <div class="flex gap-3">
                                <button type="submit" 
                                        class="flex-1 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-bold hover:shadow-lg transition-all duration-300 transform hover:scale-[1.02]">
                                    💾 Save Changes
                                </button>
                                <button type="button" 
                                        onclick="toggleEditForm()" 
                                        class="px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:bg-gray-300 dark:hover:bg-gray-600 transition-all">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password Card -->
                <div class="bg-white/95 dark:bg-gray-800/95 rounded-3xl shadow-2xl overflow-hidden border border-white/20">
                    <div class="bg-gradient-to-r from-orange-500 to-red-500 p-6">
                        <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                            <span>🔐</span> Change Password
                        </h2>
                        <p class="text-orange-100 mt-2">Keep your account secure</p>
                    </div>
                    
                    <div class="p-6">
                        <?php if ($pwd_error): ?>
                            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-lg animate-slide-in">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">❌</span>
                                    <p class="text-red-700 dark:text-red-300"><?= htmlspecialchars($pwd_error) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($pwd_success): ?>
                            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 rounded-lg animate-slide-in">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">✅</span>
                                    <p class="text-green-700 dark:text-green-300 font-medium">Password changed successfully!</p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post" class="space-y-4">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">🔑 Current Password</label>
                                <input name="old_password" 
                                       type="password" 
                                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-orange-500 dark:focus:border-orange-400 focus:ring-2 focus:ring-orange-500/20 transition-all"
                                       placeholder="Enter current password" 
                                       required>
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">🆕 New Password</label>
                                <input name="new_password" 
                                       type="password" 
                                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-orange-500 dark:focus:border-orange-400 focus:ring-2 focus:ring-orange-500/20 transition-all"
                                       placeholder="8+ chars, uppercase, lowercase, numbers" 
                                       required>
                            </div>
                            
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">✅ Confirm Password</label>
                                <input name="confirm_password" 
                                       type="password" 
                                       class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-orange-500 dark:focus:border-orange-400 focus:ring-2 focus:ring-orange-500/20 transition-all"
                                       placeholder="Confirm new password" 
                                       required>
                            </div>
                            
                            <button type="submit" 
                                    class="w-full py-4 bg-gradient-to-r from-orange-500 to-red-500 text-white rounded-xl font-bold text-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
                                🔒 Update Password
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function toggleEditForm() {
    const profileCard = document.getElementById('profileCard');
    const editFormCard = document.getElementById('editFormCard');
    
    if (editFormCard.style.display === 'none') {
        profileCard.style.display = 'none';
        editFormCard.style.display = 'block';
        editFormCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        profileCard.style.display = 'block';
        editFormCard.style.display = 'none';
        profileCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function confirmDeletePicture() {
    if (confirm('Are you sure you want to remove your profile picture?')) {
        window.location.href = 'delete_profile_pic.php';
    }
}

// Show edit form if there was an error
<?php if ($edit_error): ?>
    document.addEventListener('DOMContentLoaded', function() {
        toggleEditForm();
    });
<?php endif; ?>

// Keep edit form visible on success (don't auto-hide)
<?php if ($edit_success): ?>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            document.getElementById('profileCard').style.display = 'block';
            document.getElementById('editFormCard').style.display = 'none';
            document.getElementById('profileCard').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 1500);
    });
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
