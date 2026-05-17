<?php
/**
 * Feedback Management - CampusPulse
 * 
 * Administrative interface for comprehensive feedback management.
 * Allows admins to review, moderate, update status, and delete feedback.
 * 
 * Features:
 * - View all feedback submissions
 * - Update feedback status (Open/In Review/In Progress/Resolved/Closed)
 * - Delete individual feedback entries
 * - Batch delete multiple feedback items
 * - Filter and search capabilities
 * - User information display
 * 
 * Available Actions:
 * - delete: Remove single feedback entry
 * - batch_delete: Remove multiple feedback entries at once
 * - update_status: Change feedback status for workflow management
 * 
 * Security:
 * - Admin-only access required
 * - POST-based operations for state changes
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
// FORM SUBMISSION HANDLING
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Handle batch delete operation
    if (!empty($_POST['action']) && $_POST['action'] === 'batch_delete' && !empty($_POST['ids'])) {
        $ids = $_POST['ids'];
        // Build parameterized query with placeholders for safety
        $in = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("DELETE FROM feedback WHERE id IN ($in)");
        $stmt->execute($ids);
        $msg = 'Batch deleted ' . count($ids) . ' feedback(s)!';
        $msg_type = 'success';
    }
    
    // Handle single feedback deletion
    if (!empty($_POST['delete_id'])) {
        $stmt = $pdo->prepare('DELETE FROM feedback WHERE id = ?');
        $stmt->execute([intval($_POST['delete_id'])]);
        $msg = 'Feedback deleted!';
        $msg_type = 'success';
    }
    
    // Handle feedback status update
    if (!empty($_POST['update_status'])) {
        $stmt = $pdo->prepare('UPDATE feedback SET status = ? WHERE id = ?');
        $stmt->execute([$_POST['new_status'], intval($_POST['update_status'])]);
        $msg = 'Status updated!';
        $msg_type = 'success';
    }
}

// ============================================
// DATA RETRIEVAL
// ============================================

// Fetch all feedback with associated user information
// Uses LEFT JOIN to include feedback even if user is deleted
$stmt = $pdo->query('SELECT f.*, u.username, u.profile_picture FROM feedback f LEFT JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC');
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ============================================
// BASE URL CONFIGURATION
// ============================================

// Determine base URL for proper link generation
if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    $base_url = '/smart_feedback';
} else {
    $base_url = '';
}
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-purple-900/20 dark:to-gray-900 py-8">
    <div class="container mx-auto px-4 max-w-6xl">
        
        <!-- Header -->
        <div class="mb-8 animate-fade-in">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
                        📋 Feedback Management
                    </h1>
                    <p class="text-gray-600 dark:text-gray-300">Review and manage all feedback submissions</p>
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

        <!-- Batch Actions Card -->
        <form method="post" id="batchForm" class="bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-xl p-6 mb-6 border border-white/20">
            <input type="hidden" name="action" value="batch_delete">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <input type="checkbox" id="selectAll" class="w-5 h-5 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    <label for="selectAll" class="font-semibold text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                        Select All Feedback
                    </label>
                    <span id="selectedCount" class="text-sm text-gray-500 dark:text-gray-400">(0 selected)</span>
                </div>
                <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl font-bold hover:shadow-lg transition-all transform hover:scale-105">
                    🗑️ Delete Selected
                </button>
            </div>
        </form>

        <!-- Feedback Items -->
        <div class="space-y-4">
            <?php if (empty($items)): ?>
                <div class="bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-xl p-12 text-center border border-white/20">
                    <div class="text-6xl mb-4">📭</div>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">No Feedback Yet</h3>
                    <p class="text-gray-600 dark:text-gray-400">When users submit feedback, it will appear here.</p>
                </div>
            <?php else: ?>
                <?php foreach($items as $idx => $it): 
                    $status_colors = [
                        'Open' => ['bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-700 dark:text-blue-300', 'icon' => '📭'],
                        'In Review' => ['bg' => 'bg-yellow-100 dark:bg-yellow-900/30', 'text' => 'text-yellow-700 dark:text-yellow-300', 'icon' => '👀'],
                        'In Progress' => ['bg' => 'bg-purple-100 dark:bg-purple-900/30', 'text' => 'text-purple-700 dark:text-purple-300', 'icon' => '⚙️'],
                        'Resolved' => ['bg' => 'bg-green-100 dark:bg-green-900/30', 'text' => 'text-green-700 dark:text-green-300', 'icon' => '✅'],
                        'Closed' => ['bg' => 'bg-gray-100 dark:bg-gray-900/30', 'text' => 'text-gray-700 dark:text-gray-300', 'icon' => '🔒']
                    ];
                    $status_cfg = $status_colors[$it['status']] ?? $status_colors['Open'];
                ?>
                    <div class="bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-xl overflow-hidden border border-white/20">
                        <div class="p-6">
                            <!-- Checkbox & Title -->
                            <div class="flex items-start gap-4 mb-4">
                                <input class="feedback-checkbox w-5 h-5 mt-1 rounded border-gray-300 text-purple-600 focus:ring-purple-500" 
                                       type="checkbox" 
                                       name="ids[]" 
                                       value="<?= $it['id'] ?>" 
                                       form="batchForm"
                                       id="check<?= $it['id'] ?>">
                                <div class="flex-1">
                                    <label for="check<?= $it['id'] ?>" class="cursor-pointer">
                                        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2 hover:text-purple-600 dark:hover:text-purple-400 transition-colors">
                                            <?= htmlspecialchars($it['title']) ?>
                                        </h3>
                                    </label>
                                    
                                    <!-- Meta Info -->
                                    <div class="flex flex-wrap items-center gap-2 mb-3">
                                        <span class="<?= $status_cfg['bg'] ?> <?= $status_cfg['text'] ?> px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1">
                                            <span><?= $status_cfg['icon'] ?></span>
                                            <?= htmlspecialchars($it['status']) ?>
                                        </span>
                                        <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full text-xs font-semibold">
                                            <?= htmlspecialchars($it['category']) ?>
                                        </span>
                                        <?php if ($it['anonymous']): ?>
                                            <span class="px-3 py-1 bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300 rounded-full text-xs font-semibold">
                                                🔒 Anonymous
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Author & Date -->
                                    <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400 mb-4">
                                        <div class="flex items-center gap-2">
                                            <?php if (!$it['anonymous']): ?>
                                                <img src="<?= htmlspecialchars($it['profile_picture'] ? $base_url . '/storage/profile_pics/' . $it['profile_picture'] : 'https://ui-avatars.com/api/?name=' . urlencode($it['username'] ?? 'User') . '&background=667eea&color=fff') ?>" 
                                                     alt="<?= htmlspecialchars($it['username'] ?? 'User') ?>"
                                                     class="w-6 h-6 rounded-full">
                                            <?php endif; ?>
                                            <span><?= $it['anonymous'] ? 'Anonymous' : htmlspecialchars($it['username'] ?? 'Unknown') ?></span>
                                        </div>
                                        <span>•</span>
                                        <span>📅 <?= date('M j, Y • g:i A', strtotime($it['created_at'])) ?></span>
                                        <span>•</span>
                                        <span>👍 <?= $it['upvotes'] ?? 0 ?> / 👎 <?= $it['downvotes'] ?? 0 ?></span>
                                    </div>

                                    <!-- Description -->
                                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-4">
                                        <?= nl2br(htmlspecialchars(substr($it['description'], 0, 300))) ?><?= strlen($it['description']) > 300 ? '...' : '' ?>
                                    </p>

                                    <!-- Actions -->
                                    <div class="flex flex-wrap gap-2">
                                        <a href="<?= $base_url ?>/feedback.php?id=<?= $it['id'] ?>" 
                                           class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-semibold transition-all text-sm">
                                            👁️ View Details
                                        </a>
                                        
                                        <button type="button" 
                                                onclick="deleteSingle(<?= $it['id'] ?>)" 
                                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition-all text-sm">
                                            🗑️ Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Select all functionality
const selectAllCheckbox = document.getElementById('selectAll');
const feedbackCheckboxes = document.querySelectorAll('.feedback-checkbox');
const selectedCount = document.getElementById('selectedCount');

function updateSelectedCount() {
    const checked = document.querySelectorAll('.feedback-checkbox:checked').length;
    selectedCount.textContent = `(${checked} selected)`;
}

selectAllCheckbox.addEventListener('change', function() {
    feedbackCheckboxes.forEach(cb => cb.checked = this.checked);
    updateSelectedCount();
});

feedbackCheckboxes.forEach(cb => {
    cb.addEventListener('change', updateSelectedCount);
});

// Single delete
function deleteSingle(id) {
    if (confirm('Are you sure you want to delete this feedback? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = `<input type="hidden" name="delete_id" value="${id}">`;
        document.body.appendChild(form);
        form.submit();
    }
}

// Validate batch form
document.getElementById('batchForm').addEventListener('submit', function(e) {
    const checked = document.querySelectorAll('.feedback-checkbox:checked');
    if (checked.length === 0) {
        e.preventDefault();
        alert('Please select at least one feedback to delete.');
        return false;
    }
    return confirm(`Delete ${checked.length} feedback(s)? This action cannot be undone.`);
});
</script>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
