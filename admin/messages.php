<?php
/**
 * Contact Messages Management - CampusPulse
 * 
 * Administrative interface for viewing and managing contact form submissions.
 * Handles messages from users who used the contact form.
 * 
 * Features:
 * - View all contact messages
 * - Update message status (New/Read/Replied)
 * - Delete messages permanently
 * - View individual message details
 * - Auto-mark messages as read when opened
 * - Display sender information
 * 
 * Status Types:
 * - New: Unread message requiring attention
 * - Read: Message has been viewed by admin
 * - Replied: Admin has responded to sender
 * 
 * Security:
 * - Admin-only access required
 * - XSS protection via htmlspecialchars
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

// Handle message status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = intval($_POST['message_id']);
    $status = $_POST['status'];
    try {
        // Update message status in database
        $stmt = $pdo->prepare('UPDATE contact_messages SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);
        $msg = 'Status updated successfully!';
        $msg_type = 'success';
    } catch (Exception $e) {
        $msg = 'Failed to update status.';
        $msg_type = 'error';
    }
}

// Handle message deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message'])) {
    $id = intval($_POST['message_id']);
    try {
        // Permanently delete message from database
        $stmt = $pdo->prepare('DELETE FROM contact_messages WHERE id = ?');
        $stmt->execute([$id]);
        $msg = 'Message deleted successfully!';
        $msg_type = 'success';
    } catch (Exception $e) {
        $msg = 'Failed to delete message.';
        $msg_type = 'error';
    }
}

// ============================================
// MESSAGE RETRIEVAL
// ============================================

// Get single message if ID provided in URL parameter
$singleMessage = null;
if (isset($_GET['id'])) {
    // Fetch specific message by ID
    $stmt = $pdo->prepare('SELECT * FROM contact_messages WHERE id = ?');
    $stmt->execute([intval($_GET['id'])]);
    $singleMessage = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Auto-mark as read when message is opened
    if ($singleMessage && $singleMessage['status'] === 'New') {
        $stmt = $pdo->prepare('UPDATE contact_messages SET status = ? WHERE id = ?');
        $stmt->execute(['Read', $singleMessage['id']]);
        $singleMessage['status'] = 'Read';
    }
}

// Get all messages ordered by newest first
$stmt = $pdo->query('SELECT * FROM contact_messages ORDER BY created_at DESC');
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-purple-900/20 dark:to-gray-900 py-8">
    <div class="container mx-auto px-4 max-w-7xl">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-pink-600 via-purple-600 to-blue-600 bg-clip-text text-transparent mb-2">
                        📧 Contact Messages
                    </h1>
                    <p class="text-gray-600 dark:text-gray-300">View and manage all contact form submissions</p>
                </div>
                <a href="dashboard.php" 
                   class="px-6 py-3 bg-white/95 dark:bg-gray-800/95 text-gray-700 dark:text-gray-300 rounded-xl font-semibold hover:shadow-lg transition-shadow border border-white/20">
                    ← Back to Dashboard
                </a>
            </div>
        </div>

        <?php if ($msg): ?>
            <div class="mb-6 p-4 <?= $msg_type === 'success' ? 'bg-green-50 dark:bg-green-900/30 border-green-500' : 'bg-red-50 dark:bg-red-900/30 border-red-500' ?> border-l-4 rounded-lg">
                <div class="flex items-center gap-3">
                    <span class="text-2xl"><?= $msg_type === 'success' ? '✅' : '❌' ?></span>
                    <p class="<?= $msg_type === 'success' ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' ?> font-medium">
                        <?= htmlspecialchars($msg) ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid <?= $singleMessage ? 'md:grid-cols-3' : 'md:grid-cols-1' ?> gap-6">
            
            <!-- Message List -->
            <div class="<?= $singleMessage ? 'md:col-span-1' : 'md:col-span-1' ?>">
                <div class="bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-xl overflow-hidden border border-white/20">
                    <div class="bg-gradient-to-r from-pink-600 to-purple-600 p-4 text-white">
                        <h2 class="text-xl font-bold">All Messages (<?= count($messages) ?>)</h2>
                    </div>
                    <div class="max-h-[calc(100vh-16rem)] overflow-y-auto">
                        <?php if (empty($messages)): ?>
                            <div class="p-8 text-center">
                                <div class="text-5xl mb-3">📭</div>
                                <p class="text-gray-500 dark:text-gray-400">No messages</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $m): 
                                $status_colors = [
                                    'New' => 'bg-blue-500',
                                    'Read' => 'bg-gray-400',
                                    'Replied' => 'bg-green-500'
                                ];
                                $status_color = $status_colors[$m['status']] ?? 'bg-gray-400';
                            ?>
                                <a href="?id=<?= $m['id'] ?>" 
                                   class="block p-4 border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors <?= $singleMessage && $singleMessage['id'] == $m['id'] ? 'bg-purple-50 dark:bg-purple-900/20' : '' ?>">
                                    <div class="flex items-start gap-3">
                                        <div class="w-3 h-3 rounded-full <?= $status_color ?> mt-1 flex-shrink-0"></div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-gray-800 dark:text-white truncate">
                                                <?= htmlspecialchars($m['name']) ?>
                                            </p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 truncate">
                                                <?= htmlspecialchars($m['subject']) ?>
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
                                                <?= date('M j, Y • g:i A', strtotime($m['created_at'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Message Detail -->
            <?php if ($singleMessage): ?>
                <div class="md:col-span-2">
                    <div class="bg-white/95 dark:bg-gray-800/95 rounded-2xl shadow-xl overflow-hidden border border-white/20">
                        <div class="bg-gradient-to-r from-purple-600 to-blue-600 p-6 text-white">
                            <h2 class="text-2xl font-bold mb-2"><?= htmlspecialchars($singleMessage['subject']) ?></h2>
                            <div class="flex items-center gap-4 text-sm opacity-90">
                                <span>📅 <?= date('M j, Y • g:i A', strtotime($singleMessage['created_at'])) ?></span>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <!-- Sender Info -->
                            <div class="mb-6 p-4 bg-gradient-to-r from-purple-50 to-blue-50 dark:from-purple-900/30 dark:to-blue-900/30 rounded-xl">
                                <div class="grid md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">👤 Name</p>
                                        <p class="font-semibold text-gray-800 dark:text-white"><?= htmlspecialchars($singleMessage['name']) ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">📧 Email</p>
                                        <p class="font-semibold text-gray-800 dark:text-white">
                                            <a href="mailto:<?= htmlspecialchars($singleMessage['email']) ?>" class="text-blue-600 hover:underline">
                                                <?= htmlspecialchars($singleMessage['email']) ?>
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Message Content -->
                            <div class="mb-6">
                                <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-3">Message</h3>
                                <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                                    <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">
                                        <?= htmlspecialchars($singleMessage['message']) ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex flex-wrap gap-3">
                                <form method="post" onsubmit="return confirm('Delete this message?')" class="inline">
                                    <input type="hidden" name="message_id" value="<?= $singleMessage['id'] ?>">
                                    <input type="hidden" name="delete_message" value="1">
                                    <button type="submit" 
                                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-semibold transition-all">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
