<?php
/**
 * Admin Dashboard - CampusPulse
 * 
 * Main administrative control panel displaying system statistics and recent activity.
 * Provides overview of feedback submissions, user counts, and system health.
 * 
 * Features:
 * - System statistics (users, feedback, votes)
 * - Today's new feedback count
 * - Category distribution analytics
 * - Recent feedback list
 * - Recent contact messages
 * - Quick access links to management pages
 * 
 * Security:
 * - Admin-only access required
 * - Session-based authentication
 * - Redirects non-admin users to homepage
 */

// Include required files for page functionality
require_once __DIR__ . '/../inc/header.php';
require_once __DIR__ . '/../inc/db.php';

// ============================================
// ADMIN ACCESS CONTROL
// ============================================

// Verify user has admin privileges, show error if not
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

// Get PDO database connection instance
$pdo = getPDO();

// ============================================
// STATISTICS GATHERING
// ============================================

// Get today's date in Y-m-d format for filtering
$today = date('Y-m-d');

// Count feedback submissions created today
$stmt = $pdo->prepare("SELECT COUNT(*) FROM feedback WHERE date(created_at) = ?");
$stmt->execute([$today]);
$newToday = $stmt->fetchColumn();

// Count feedback with 'Open' status (pending review)
$stmt = $pdo->query("SELECT COUNT(*) FROM feedback WHERE status = 'Open'");
$pending = $stmt->fetchColumn();

// Count total registered users in system
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmt->fetchColumn();

// Count total feedback submissions (all time)
$stmt = $pdo->query("SELECT COUNT(*) FROM feedback");
$totalFeedback = $stmt->fetchColumn();

// ============================================
// CATEGORY STATISTICS
// ============================================

// Get feedback count grouped by category, ordered by frequency
$stmt = $pdo->query("SELECT category, COUNT(*) as count FROM feedback GROUP BY category ORDER BY count DESC");
$categoryStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ============================================
// RECENT ACTIVITY
// ============================================

// Fetch 5 most recent feedback entries with user information
$stmt = $pdo->query("SELECT f.*, u.username FROM feedback f LEFT JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC LIMIT 5");
$recentFeedback = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ============================================
// CONTACT MESSAGES
// ============================================

// Retrieve contact form submissions with error handling
try {
    // Count unread messages (status = 'New')
    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'New'");
    $newMessages = $stmt->fetchColumn();
    
    // Get 5 most recent contact messages
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
    $recentMessages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Table might not exist if setup.php hasn't been run
    $newMessages = 0;
    $recentMessages = [];
}

?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-purple-900/20 dark:to-gray-900 py-8">
    <div class="container mx-auto px-4 max-w-7xl">
        
        <!-- Hero Header -->
        <div class="mb-8 animate-fade-in">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
                        🛠️ Admin Dashboard
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-300">
                        Manage feedback and users • Welcome back! 👋
                    </p>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            
            <div class="bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-xl p-6 border border-white/20 hover:shadow-2xl transition-shadow duration-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-4xl">📬</div>
                    <div class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                        <span class="text-blue-600 dark:text-blue-400 font-bold text-sm">Today</span>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-800 dark:text-white mb-1">
                    <?= $newToday ?>
                </div>
                <p class="text-gray-600 dark:text-gray-400 text-sm">New Feedback Today</p>
            </div>

            <div class="bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-xl p-6 border border-white/20 hover:shadow-2xl transition-shadow duration-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-4xl">👥</div>
                    <div class="px-3 py-1 bg-purple-100 dark:bg-purple-900/30 rounded-full">
                        <span class="text-purple-600 dark:text-purple-400 font-bold text-sm">Total</span>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-800 dark:text-white mb-1">
                    <?= $totalUsers ?>
                </div>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Registered Users</p>
            </div>

            <div class="bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-xl p-6 border border-white/20 hover:shadow-2xl transition-shadow duration-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-4xl">📊</div>
                    <div class="px-3 py-1 bg-green-100 dark:bg-green-900/30 rounded-full">
                        <span class="text-green-600 dark:text-green-400 font-bold text-sm">All Time</span>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-800 dark:text-white mb-1">
                    <?= $totalFeedback ?>
                </div>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Total Feedback</p>
            </div>

            <div class="bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-xl p-6 border border-white/20 hover:shadow-2xl transition-shadow duration-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-4xl">📧</div>
                    <div class="px-3 py-1 bg-pink-100 dark:bg-pink-900/30 rounded-full">
                        <span class="text-pink-600 dark:text-pink-400 font-bold text-sm">New</span>
                    </div>
                </div>
                <div class="text-3xl font-bold text-gray-800 dark:text-white mb-1">
                    <?= $newMessages ?>
                </div>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Contact Messages</p>
            </div>
        </div>

        <!-- Quick Actions & Category Stats -->
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            
            <!-- Quick Actions -->
            <div class="bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-xl p-6 border border-white/20">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                    <span>⚡</span> Quick Actions
                </h2>
                <div class="space-y-3">
                    <a href="feedbacks.php" 
                       class="block px-4 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all transform hover:scale-[1.02]">
                        📋 Manage Feedbacks
                    </a>
                    <a href="users.php" 
                       class="block px-4 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all transform hover:scale-[1.02]">
                        👥 Manage Users
                    </a>
                    <a href="messages.php" 
                       class="block px-4 py-3 bg-gradient-to-r from-pink-500 to-purple-500 text-white rounded-xl font-semibold hover:shadow-lg transition-all transform hover:scale-[1.02]">
                        📧 Contact Messages
                    </a>
                </div>
            </div>

            <!-- Category Statistics -->
            <div class="md:col-span-2 bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-xl p-6 border border-white/20">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                    <span>📈</span> Category Breakdown
                </h2>
                <div class="space-y-3">
                    <?php 
                    $category_colors = [
                        'Academics' => 'bg-blue-500',
                        'Facilities' => 'bg-green-500',
                        'Campus Life' => 'bg-purple-500',
                        'Food Services' => 'bg-orange-500',
                        'Technology' => 'bg-cyan-500',
                        'Support Services' => 'bg-pink-500',
                        'Other' => 'bg-gray-500'
                    ];
                    foreach ($categoryStats as $cat): 
                        $color = $category_colors[$cat['category']] ?? 'bg-gray-500';
                        $percentage = $totalFeedback > 0 ? round(($cat['count'] / $totalFeedback) * 100) : 0;
                    ?>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300"><?= htmlspecialchars($cat['category']) ?></span>
                                <span class="text-sm font-bold text-gray-800 dark:text-white"><?= $cat['count'] ?></span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                <div class="<?= $color ?> h-2.5 rounded-full transition-all duration-500" style="width: <?= $percentage ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Recent Feedback -->
        <div class="bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-xl overflow-hidden border border-white/20">
            <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 p-6 text-white">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold flex items-center gap-3">
                        <span>📬</span> Recent Feedback
                    </h2>
                    <span class="px-3 py-1 bg-white/50 rounded-full text-sm font-semibold">
                        Latest 5
                    </span>
                </div>
            </div>

            <div class="p-6">
                <?php if (empty($recentFeedback)): ?>
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">📭</div>
                        <p class="text-gray-500 dark:text-gray-400 text-lg">No feedback yet</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recentFeedback as $idx => $fb): 
                            $status_colors = [
                                'Open' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                'In Review' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                                'In Progress' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                                'Resolved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                                'Closed' => 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-300'
                            ];
                            $status_class = $status_colors[$fb['status']] ?? $status_colors['Open'];
                        ?>
                            <div class="bg-white/95 dark:bg-gray-700/95 rounded-xl p-4 border border-gray-200 dark:border-gray-600 hover:shadow-lg transition-shadow duration-200">
                                <div class="flex items-start gap-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <h3 class="font-bold text-gray-800 dark:text-white text-lg">
                                                <?= htmlspecialchars($fb['title']) ?>
                                            </h3>
                                            <span class="<?= $status_class ?> px-3 py-1 rounded-full text-xs font-bold">
                                                <?= htmlspecialchars($fb['status']) ?>
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                            <?= htmlspecialchars(substr($fb['description'], 0, 150)) ?><?= strlen($fb['description']) > 150 ? '...' : '' ?>
                                        </p>
                                        <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-500">
                                            <span>📁 <?= htmlspecialchars($fb['category']) ?></span>
                                            <span>👤 <?= $fb['anonymous'] ? 'Anonymous' : htmlspecialchars($fb['username'] ?? 'Unknown') ?></span>
                                            <span>📅 <?= date('M j, Y', strtotime($fb['created_at'])) ?></span>
                                        </div>
                                    </div>
                                    <a href="../feedback.php?id=<?= $fb['id'] ?>" 
                                       class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-semibold transition-all">
                                        View
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-6 text-center">
                        <a href="feedbacks.php" 
                           class="inline-block px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-bold hover:shadow-lg transition-all">
                            View All Feedbacks →
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Contact Messages -->
        <div class="bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-xl overflow-hidden border border-white/20 mt-8">
            <div class="bg-gradient-to-r from-pink-600 via-purple-600 to-blue-600 p-6 text-white">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold flex items-center gap-3">
                        <span>📧</span> Contact Messages
                    </h2>
                    <span class="px-3 py-1 bg-white/50 rounded-full text-sm font-semibold">
                        Latest 5
                    </span>
                </div>
            </div>

            <div class="p-6">
                <?php if (empty($recentMessages)): ?>
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">📭</div>
                        <p class="text-gray-500 dark:text-gray-400 text-lg">No contact messages yet</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($recentMessages as $idx => $msg): 
                            $status_colors = [
                                'New' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                                'Read' => 'bg-gray-100 text-gray-700 dark:bg-gray-900/30 dark:text-gray-300',
                                'Replied' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                            ];
                            $status_class = $status_colors[$msg['status']] ?? $status_colors['New'];
                        ?>
                            <div class="bg-white/95 dark:bg-gray-700/95 rounded-xl p-4 border border-gray-200 dark:border-gray-600 hover:shadow-lg transition-shadow duration-200">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-r from-pink-500 to-purple-500 flex items-center justify-center text-white font-bold text-lg">
                                            <?= strtoupper(substr($msg['name'], 0, 1)) ?>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between mb-2 gap-2">
                                            <div>
                                                <h3 class="font-bold text-gray-800 dark:text-white text-lg">
                                                    <?= htmlspecialchars($msg['subject']) ?>
                                                </h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                                    From: <?= htmlspecialchars($msg['name']) ?> (<?= htmlspecialchars($msg['email']) ?>)
                                                </p>
                                            </div>
                                            <span class="<?= $status_class ?> px-3 py-1 rounded-full text-xs font-bold whitespace-nowrap">
                                                <?= htmlspecialchars($msg['status']) ?>
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2 line-clamp-2">
                                            <?= htmlspecialchars(substr($msg['message'], 0, 200)) ?><?= strlen($msg['message']) > 200 ? '...' : '' ?>
                                        </p>
                                        <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-500">
                                            <span>📅 <?= date('M j, Y • g:i A', strtotime($msg['created_at'])) ?></span>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <button onclick="viewMessage(<?= $msg['id'] ?>)" 
                                                class="px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg text-sm font-semibold transition-all">
                                            View
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function viewMessage(id) {
    // Open message in a modal or redirect to a dedicated page
    window.location.href = 'messages.php?id=' + id;
}
</script>

<style>
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
