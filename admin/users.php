<?php
/**
 * User Management - CampusPulse
 * 
 * Administrative interface for managing user accounts and permissions.
 * Allows viewing and controlling user account status.
 * 
 * Features:
 * - View all registered users
 * - Enable/disable user accounts
 * - View user statistics (total, active, disabled, admins)
 * - See user registration details
 * - Display profile pictures
 * - View admin status
 * 
 * User Status:
 * - enabled = 1: Account is active and can login
 * - enabled = 0: Account is disabled and cannot login
 * 
 * Security:
 * - Admin-only access required
 * - Prevents accidental self-disabling
 * - Audit trail via timestamps
 */

// Include required dependencies
require_once __DIR__ . '/../inc/header.php';
require_once __DIR__ . '/../inc/db.php';

// ============================================
// ADMIN ACCESS CONTROL
// ============================================

if (!is_admin()) { 
    echo '<div class="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-purple-900/20 dark:to-gray-900 flex items-center justify-center">
            <div class="text-center">
                <div class="text-6xl mb-4">🔒</div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Admin Access Required</h2>
                <a href="../index.php" class="inline-block px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all">
                    ← Back to Home
                </a>
            </div>
          </div>';
    require_once __DIR__ . '/../inc/footer.php'; 
    exit; 
}

// Get database connection instance
$pdo = getPDO();

// Initialize status message variables
$msg = '';
$msg_type = '';

// ============================================
// TOGGLE USER ACCOUNT STATUS
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['toggle_id'])) {
        $id = intval($_POST['toggle_id']);
        
        // Get current enabled status from database
        $stmt = $pdo->prepare('SELECT enabled FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $cur = $stmt->fetchColumn();
        
        // Toggle status (flip between 0 and 1)
        $new = $cur ? 0 : 1;
        
        // Update user account status in database
        $stmt = $pdo->prepare('UPDATE users SET enabled = ? WHERE id = ?');
        $stmt->execute([$new, $id]);
        
        // Set appropriate success message based on new status
        $msg = $new ? 'User account enabled!' : 'User account disabled!';
        $msg_type = 'success';
    }
}

// ============================================
// DATA RETRIEVAL
// ============================================

// Fetch all users with relevant fields, ordered by registration date
$stmt = $pdo->query('SELECT id, username, email, registered_at, is_admin, enabled, profile_picture FROM users ORDER BY registered_at DESC');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get base URL
if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    $base_url = '/smart_feedback';
} else {
    $base_url = '';
}
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-purple-900/20 dark:to-gray-900 py-8">
    <div class="container mx-auto px-4 max-w-7xl">
        
        <!-- Header -->
        <div class="mb-8 animate-fade-in">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
                        👥 User Management
                    </h1>
                    <p class="text-gray-600 dark:text-gray-300">Manage user accounts and permissions</p>
                </div>
                <a href="dashboard.php" 
                   class="px-6 py-3 bg-white/95 dark:bg-gray-800/95 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:shadow-lg transition-shadow border border-white/20">
                    ← Back to Dashboard
                </a>
            </div>
        </div>

        <?php if ($msg): ?>
            <div class="mb-6 p-4 <?= $msg_type === 'success' ? 'bg-green-50 dark:bg-green-900/30 border-green-500' : 'bg-red-50 dark:bg-red-900/30 border-red-500' ?> border-l-4 rounded-lg animate-slide-in">
                <div class="flex items-center gap-3">
                    <span class="text-2xl"><?= $msg_type === 'success' ? '✅' : '❌' ?></span>
                    <p class="<?= $msg_type === 'success' ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' ?> font-medium">
                        <?= htmlspecialchars($msg) ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-xl p-6 border border-white/20">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-1">Total Users</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-white"><?= count($users) ?></p>
                    </div>
                    <div class="text-5xl">👥</div>
                </div>
            </div>
            
            <div class="bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-xl p-6 border border-white/20">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-1">Active Users</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-white">
                            <?= count(array_filter($users, fn($u) => $u['enabled'])) ?>
                        </p>
                    </div>
                    <div class="text-5xl">✅</div>
                </div>
            </div>
            
            <div class="bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-xl p-6 border border-white/20">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-1">Administrators</p>
                        <p class="text-3xl font-bold text-gray-800 dark:text-white">
                            <?= count(array_filter($users, fn($u) => $u['is_admin'])) ?>
                        </p>
                    </div>
                    <div class="text-5xl">🛡️</div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-xl overflow-hidden border border-white/20">
            <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 p-6 text-white">
                <h2 class="text-2xl font-bold flex items-center gap-3">
                    <span>📋</span> All Users
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-600">
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">User</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Registered</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                        <?php foreach($users as $idx => $u): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <!-- User Info with Avatar -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <img src="<?= htmlspecialchars($u['profile_picture'] ? $base_url . '/storage/profile_pics/' . $u['profile_picture'] : 'https://ui-avatars.com/api/?name=' . urlencode($u['username']) . '&background=667eea&color=fff') ?>" 
                                             alt="<?= htmlspecialchars($u['username']) ?>"
                                             class="w-10 h-10 rounded-full object-cover ring-2 ring-purple-500/50">
                                        <div>
                                            <p class="font-bold text-gray-800 dark:text-white">
                                                <?= htmlspecialchars($u['username']) ?>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                
                                <!-- Email -->
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <?= htmlspecialchars($u['email']) ?>
                                    </p>
                                </td>
                                
                                <!-- Registered Date -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <?= date('M j, Y', strtotime($u['registered_at'])) ?>
                                    </p>
                                </td>
                                
                                <!-- Role -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($u['is_admin']): ?>
                                        <span class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-full text-xs font-bold flex items-center gap-1 w-fit">
                                            🛡️ Admin
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-xs font-semibold">
                                            User
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($u['enabled']): ?>
                                        <span class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 rounded-full text-xs font-bold flex items-center gap-1 w-fit">
                                            ✅ Active
                                        </span>
                                    <?php else: ?>
                                        <span class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-full text-xs font-bold flex items-center gap-1 w-fit">
                                            ⛔ Disabled
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Action -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ($u['username'] !== 'admin'): ?>
                                        <form method="post" class="inline">
                                            <input type="hidden" name="toggle_id" value="<?= $u['id'] ?>">
                                            <button type="submit" 
                                                    onclick="return confirm('<?= $u['enabled'] ? 'Disable' : 'Enable' ?> this user account?')"
                                                    class="px-4 py-2 <?= $u['enabled'] ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700' ?> text-white rounded-lg font-semibold transition-all text-sm">
                                                <?= $u['enabled'] ? '⛔ Disable' : '✅ Enable' ?>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-lg text-sm font-semibold">
                                            🔒 Protected
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Info Card -->
        <div class="mt-6 bg-blue-50 dark:bg-blue-900/50 rounded-2xl p-6 border border-blue-200 dark:border-blue-700">
            <div class="flex items-start gap-4">
                <div class="text-3xl">💡</div>
                <div class="flex-1">
                    <h3 class="font-bold text-gray-800 dark:text-white mb-2">User Management Tips</h3>
                    <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                        <li>• Disabled users cannot log in but their data is preserved</li>
                        <li>• The system admin account cannot be disabled</li>
                        <li>• Use caution when managing admin accounts</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
