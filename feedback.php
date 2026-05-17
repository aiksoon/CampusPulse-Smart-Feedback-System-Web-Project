<?php
/**
 * Feedback Detail View - CampusPulse
 * 
 * Displays complete details of a single feedback submission.
 * Shows full content, metadata, voting stats, and allows interactions.
 * 
 * Features:
 * - Display full feedback title and description
 * - Show author information (unless anonymous)
 * - Display category with icon and color
 * - Show status badge
 * - Display vote counts (upvotes/downvotes)
 * - Creation date and time
 * - Profile picture support
 * - Responsive layout
 * 
 * URL Parameter:
 * - id: Required feedback ID to display
 * 
 * Error Handling:
 * - Missing ID: Shows error message
 * - Invalid ID: Shows 'not found' message
 * - Graceful error display with navigation
 */

// Include required dependencies
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/inc/db.php';

// ============================================
// VALIDATE FEEDBACK ID
// ============================================

// Get and validate feedback ID from URL
$id = intval($_GET['id'] ?? 0);
if (!$id) { 
    echo '<div class="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-purple-900/20 dark:to-gray-900 flex items-center justify-center">
            <div class="text-center">
                <div class="text-6xl mb-4">❌</div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Missing Feedback ID</h2>
                <a href="index.php" class="inline-block px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all">
                    ← Back to Feedbacks
                </a>
            </div>
          </div>';
    require_once __DIR__ . '/inc/footer.php'; 
    exit; 
}

// Get database connection
$pdo = getPDO();

// ============================================
// FETCH FEEDBACK DATA
// ============================================

// Get feedback with user information via LEFT JOIN
$stmt = $pdo->prepare('SELECT f.*, u.username, u.profile_picture FROM feedback f LEFT JOIN users u ON f.user_id = u.id WHERE f.id = ?');
$stmt->execute([$id]);
$it = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if feedback exists
if (!$it) { 
    echo '<div class="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-purple-900/20 dark:to-gray-900 flex items-center justify-center">
            <div class="text-center">
                <div class="text-6xl mb-4">🔍</div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Feedback Not Found</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6">This feedback may have been removed</p>
                <a href="index.php" class="inline-block px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all">
                    ← Back to Feedbacks
                </a>
            </div>
          </div>';
    require_once __DIR__ . '/inc/footer.php'; 
    exit; 
}

// ============================================
// CALCULATE VOTE STATISTICS
// ============================================

// Get vote counts from feedback record
$upvotes = $it['upvotes'] ?? 0;
$downvotes = $it['downvotes'] ?? 0;
$score = $upvotes - $downvotes; // Net score

// ============================================
// UI CONFIGURATION - ICONS AND COLORS
// ============================================

// Define emoji icons for each feedback category
$category_icons = [
    'Academics' => '📚',
    'Facilities' => '🏢',
    'Campus Life' => '🎉',
    'Food Services' => '🍽️',
    'Technology' => '💻',
    'Support Services' => '🤝',
    'Other' => '💡'
];

// Define gradient colors for category badges
$category_colors = [
    'Academics' => 'from-blue-500 to-blue-600',
    'Facilities' => 'from-green-500 to-green-600',
    'Campus Life' => 'from-purple-500 to-purple-600',
    'Food Services' => 'from-orange-500 to-orange-600',
    'Technology' => 'from-cyan-500 to-cyan-600',
    'Support Services' => 'from-pink-500 to-pink-600',
    'Other' => 'from-gray-500 to-gray-600'
];

// Get icon and color for current feedback category
$icon = $category_icons[$it['category']] ?? '💡';
$color_gradient = $category_colors[$it['category']] ?? 'from-gray-500 to-gray-600';

// ============================================
// STATUS BADGE CONFIGURATION
// ============================================

// Define styling for different feedback statuses
$status_config = [
    'Open' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-300', 'icon' => '📭'],
    'In Review' => ['bg' => 'bg-yellow-100 dark:bg-yellow-900/30', 'text' => 'text-yellow-700 dark:text-yellow-300', 'icon' => '👀'],
    'In Progress' => ['bg' => 'bg-purple-100 dark:bg-purple-900/30', 'text' => 'text-purple-700 dark:text-purple-300', 'icon' => '⚙️'],
    'Resolved' => ['bg' => 'bg-green-100 dark:bg-green-900/30', 'text' => 'text-green-700 dark:text-green-300', 'icon' => '✅'],
    'Closed' => ['bg' => 'bg-gray-100 dark:bg-gray-900/30', 'text' => 'text-gray-700 dark:text-gray-300', 'icon' => '🔒']
];

$status = $status_config[$it['status']] ?? $status_config['Open'];

// Get base URL
if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    $base_url = '/smart_feedback';
} else {
    $base_url = '';
}
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-purple-900/20 dark:to-gray-900 py-8">
    <div class="container mx-auto px-4 max-w-4xl">
        
        <!-- Back Button -->
        <div class="mb-6 animate-fade-in">
            <a href="<?php echo is_admin() ? $base_url . '/admin/feedbacks.php' : 'index.php'; ?>" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-white/95 dark:bg-gray-800/95 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-white dark:hover:bg-gray-800 transition-colors border border-white/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back
            </a>
        </div>

        <!-- Main Feedback Card -->
        <div class="bg-white/95 dark:bg-gray-800/95 rounded-3xl shadow-2xl overflow-hidden border border-white/20">
            
            <!-- Header -->
            <div class="bg-gradient-to-r <?php echo $color_gradient; ?> p-6 text-white">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-4xl"><?php echo $icon; ?></span>
                            <span class="px-3 py-1 bg-white/50 rounded-full text-sm font-semibold">
                                <?php echo htmlspecialchars($it['category']); ?>
                            </span>
                        </div>
                        <h1 class="text-3xl font-bold mb-2">
                            <?php echo htmlspecialchars($it['title']); ?>
                        </h1>
                        <div class="flex items-center gap-4 text-sm text-white/90">
                            <span>📅 <?php echo date('M j, Y • g:i A', strtotime($it['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8 space-y-6">
                
                <!-- Description -->
                <div class="bg-white/95 dark:bg-gray-700/95 rounded-2xl p-6">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                        <span>📝</span> Feedback Details
                    </h2>
                    <div class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">
                        <?php echo nl2br(htmlspecialchars($it['description'])); ?>
                    </div>
                </div>

                <!-- Author Section -->
                <div>
                    <!-- Author Info -->
                    <div class="bg-gradient-to-br from-pink-50 to-orange-50 dark:from-pink-900/50 dark:to-orange-900/50 rounded-2xl p-6 border border-pink-200 dark:border-pink-700">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                            <span>👤</span> Submitted By
                        </h3>
                        <?php if ($it['anonymous']): ?>
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-full bg-gradient-to-r from-gray-400 to-gray-500 flex items-center justify-center text-3xl">
                                    🕶️
                                </div>
                                <div>
                                    <p class="text-xl font-bold text-gray-800 dark:text-white">Anonymous</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Identity Protected</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="flex items-center gap-4">
                                <img src="<?php echo htmlspecialchars($it['profile_picture'] ? $base_url . '/storage/profile_pics/' . $it['profile_picture'] : 'https://ui-avatars.com/api/?name=' . urlencode($it['username'] ?? 'User') . '&background=667eea&color=fff'); ?>" 
                                     alt="<?php echo htmlspecialchars($it['username'] ?? 'User'); ?>"
                                     class="w-16 h-16 rounded-full object-cover ring-4 ring-purple-500/50">
                                <div>
                                    <p class="text-xl font-bold text-gray-800 dark:text-white">
                                        <?php echo htmlspecialchars($it['username'] ?? 'Unknown'); ?>
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Community Member</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
