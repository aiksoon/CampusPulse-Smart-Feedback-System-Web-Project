<?php
/**
 * Feedback Feed - CampusPulse
 * 
 * Main feed displaying all approved feedback submissions.
 * This is the primary view where users browse and interact with feedback.
 * 
 * Features:
 * - Display all approved feedback
 * - Category filtering
 * - Voting system (upvote/downvote)
 * - Anonymous user support
 * - Profile pictures
 * - Login/Logout success messages
 * - Responsive card layout
 * 
 * Display Logic:
 * - Shows only feedback with status 'Approved'
 * - Orders by creation date (newest first)
 * - Can filter by category via URL parameter
 * - Displays author info (unless anonymous)
 * 
 * Interaction:
 * - Users can click to view full feedback details
 * - Vote buttons (requires login)
 * - Category badges for filtering
 */

// Include required dependencies
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/inc/db.php';

// Get database connection
$pdo = getPDO();

// ============================================
// SUCCESS MESSAGE HANDLING
// ============================================

// Check for login success message from session
$login_success = false;
$logout_success = false;
if (isset($_SESSION['login_success']) && $_SESSION['login_success']) {
    $login_success = true;
    unset($_SESSION['login_success']); // Clear message after displaying
}
// Check for logout success message from URL
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $logout_success = true;
}

// ============================================
// CATEGORY FILTERING
// ============================================

// Get category filter from URL parameter
$cat = isset($_GET['category']) ? $_GET['category'] : '';
$params = [];

// Build SQL query with optional category filter
$sql = "SELECT f.*, u.username, u.profile_picture FROM feedback f LEFT JOIN users u ON f.user_id = u.id WHERE f.status = 'Approved' ";
if ($cat) {
    $sql .= " AND f.category = ?";
    $params[] = $cat;
}
$sql .= " ORDER BY f.created_at DESC";

// Execute query and fetch all results
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- Success Messages -->
<?php if ($login_success): ?>
  <div class="mb-6 p-4 rounded-xl glass border-l-4 border-green-500 animate-fade-in">
    <div class="flex items-center">
      <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      <span class="text-gray-800 dark:text-white font-medium">Login successful! Welcome back.</span>
    </div>
  </div>
<?php endif; ?>

<?php if ($logout_success): ?>
  <div class="mb-6 p-4 rounded-xl glass border-l-4 border-blue-500 animate-fade-in">
    <div class="flex items-center">
      <svg class="w-6 h-6 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
      </svg>
      <span class="text-gray-800 dark:text-white font-medium">You have been logged out successfully.</span>
    </div>
  </div>
<?php endif; ?>

<!-- Header Section -->
<div class="max-w-4xl mx-auto mb-8">
  <h1 class="text-5xl font-bold gradient-text mb-4 animate-float">Pulse Feed</h1>
  <p class="text-xl text-gray-600 dark:text-gray-400">Discover what your campus community is thinking</p>
</div>

<!-- Filter Section -->
<div class="max-w-4xl mx-auto mb-8">
  <form method="get" class="flex flex-col sm:flex-row gap-4">
    <div class="flex-1">
      <select name="category" class="w-full px-4 py-3 rounded-xl glass border-2 border-transparent focus:border-tech-blue dark:focus:border-tech-pink transition-all outline-none text-gray-700 dark:text-gray-200">
        <option value="">🌐 All Categories</option>
        <?php foreach(['Course','Infrastructure','Cafeterias'] as $c): ?>
          <option value="<?=$c?>" <?=($cat==$c)?'selected':''?>><?=$c?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-tech-blue via-tech-purple to-tech-pink text-white font-semibold hover:shadow-lg hover:scale-105 transition-all">
      <span class="flex items-center justify-center">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
        </svg>
        Filter
      </span>
    </button>
  </form>
</div>

<!-- Feedback Cards -->
<div class="max-w-4xl mx-auto space-y-6">
  <?php foreach($items as $it): ?>
    <?php
      // Get vote counts for this feedback
      $stmt_likes = $pdo->prepare('SELECT COUNT(*) FROM votes WHERE feedback_id = ? AND vote = 1');
      $stmt_likes->execute([$it['id']]);
      $likes = $stmt_likes->fetchColumn();
      
      $stmt_dislikes = $pdo->prepare('SELECT COUNT(*) FROM votes WHERE feedback_id = ? AND vote = -1');
      $stmt_dislikes->execute([$it['id']]);
      $dislikes = $stmt_dislikes->fetchColumn();
      
      // Check current user's vote
      $current_vote = 0;
      $me = current_user();
      if ($me) {
        $stmt_my_vote = $pdo->prepare('SELECT vote FROM votes WHERE feedback_id = ? AND user_id = ?');
        $stmt_my_vote->execute([$it['id'], $me['id']]);
        $my_vote_row = $stmt_my_vote->fetch(PDO::FETCH_ASSOC);
        $current_vote = $my_vote_row ? intval($my_vote_row['vote']) : 0;
      }
      
      // Get profile picture URL for the feedback author
      if ($it['anonymous']) {
        $author_profile_pic = base_url('assets/anonymous.png');
      } elseif (!empty($it['profile_picture'])) {
        $author_profile_pic = base_url('storage/profile_pics/' . $it['profile_picture']);
      } else {
        $author_profile_pic = base_url('assets/default-avatar.png');
      }
    ?>
    
    <div class="glass rounded-2xl p-4 hover:shadow-2xl transition-all duration-300 group">
      <!-- Author Info -->
      <div class="flex items-start mb-3">
        <img src="<?= htmlspecialchars($author_profile_pic) ?>" 
             alt="<?= $it['anonymous'] ? 'Anonymous' : htmlspecialchars($it['username']) ?>" 
             class="w-10 h-10 rounded-full border-2 border-white/30 object-cover mr-3"
             onerror="this.src='<?= base_url('assets/default-avatar.png') ?>'">
        <div class="flex-1">
          <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-1">
            <?= htmlspecialchars($it['title']) ?>
          </h3>
          <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
            <span class="flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
              <?php if ($it['anonymous']): ?>
                Anonymous
              <?php else: ?>
                <?= htmlspecialchars($it['username'] ?: 'Unknown') ?>
              <?php endif; ?>
            </span>
            <span class="flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
              </svg>
              <?= htmlspecialchars($it['category']) ?>
            </span>
            <span class="flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <?= date('M d, Y', strtotime($it['created_at'])) ?>
            </span>
          </div>
        </div>
      </div>
      
      <!-- Description -->
      <p class="text-sm text-gray-700 dark:text-gray-300 leading-normal mb-4">
        <?= nl2br(htmlspecialchars($it['description'])) ?>
      </p>
      
      <!-- Action Buttons -->
      <div class="flex items-center space-x-3">
        <!-- Like Button -->
        <button class="vote-btn flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-sm transition-all <?= ($current_vote === 1) ? 'bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-lg scale-105' : 'glass hover:bg-white/50 dark:hover:bg-white/10 text-gray-700 dark:text-gray-200' ?>" 
                data-feedback-id="<?= $it['id'] ?>" 
                data-vote="1">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
          </svg>
          <span class="font-semibold like-count" data-feedback-id="<?= $it['id'] ?>"><?= $likes ?></span>
        </button>
        
        <!-- Dislike Button -->
        <button class="vote-btn flex items-center space-x-1.5 px-3 py-1.5 rounded-lg text-sm transition-all <?= ($current_vote === -1) ? 'bg-gradient-to-r from-red-500 to-rose-500 text-white shadow-lg scale-105' : 'glass hover:bg-white/50 dark:hover:bg-white/10 text-gray-700 dark:text-gray-200' ?>" 
                data-feedback-id="<?= $it['id'] ?>" 
                data-vote="-1">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"></path>
          </svg>
          <span class="font-semibold dislike-count" data-feedback-id="<?= $it['id'] ?>"><?= $dislikes ?></span>
        </button>
      </div>
    </div>
  <?php endforeach; ?>
  
  <?php if (empty($items)): ?>
    <div class="glass rounded-2xl p-12 text-center">
      <svg class="w-20 h-20 mx-auto mb-4 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
      </svg>
      <h3 class="text-2xl font-bold text-gray-700 dark:text-gray-300 mb-2">No Feedback Yet</h3>
      <p class="text-gray-600 dark:text-gray-400 mb-6">Be the first to share your thoughts!</p>
      <a href="<?= $base_url ?>/submit.php" class="inline-block px-8 py-3 rounded-xl bg-gradient-to-r from-tech-blue via-tech-purple to-tech-pink text-white font-semibold hover:shadow-lg hover:scale-105 transition-all">
        Submit Feedback
      </a>
    </div>
  <?php endif; ?>
</div>

<script>
document.querySelectorAll('.vote-btn').forEach(btn => {
  btn.addEventListener('click', async function(e) {
    e.preventDefault();
    const feedbackId = this.dataset.feedbackId;
    const vote = parseInt(this.dataset.vote);
    
    try {
      const response = await fetch('vote.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'feedback_id=' + feedbackId + '&vote=' + vote
      });
      
      const data = await response.json();
      if (data.success) {
        // Update vote counts
        const likeCount = document.querySelector('.like-count[data-feedback-id="' + feedbackId + '"]');
        const dislikeCount = document.querySelector('.dislike-count[data-feedback-id="' + feedbackId + '"]');
        const likeBtns = document.querySelectorAll('.vote-btn[data-feedback-id="' + feedbackId + '"][data-vote="1"]');
        const dislikeBtns = document.querySelectorAll('.vote-btn[data-feedback-id="' + feedbackId + '"][data-vote="-1"]');
        
        if (likeCount) likeCount.textContent = data.likes;
        if (dislikeCount) dislikeCount.textContent = data.dislikes;
        
        // Update button states
        likeBtns.forEach(likeBtn => {
          if (data.current_vote === 1) {
            likeBtn.className = 'vote-btn flex items-center space-x-2 px-4 py-2 rounded-lg transition-all bg-gradient-to-r from-green-500 to-emerald-500 text-white shadow-lg scale-105';
          } else {
            likeBtn.className = 'vote-btn flex items-center space-x-2 px-4 py-2 rounded-lg transition-all glass hover:bg-white/50 dark:hover:bg-white/10 text-gray-700 dark:text-gray-200';
          }
        });
        
        dislikeBtns.forEach(dislikeBtn => {
          if (data.current_vote === -1) {
            dislikeBtn.className = 'vote-btn flex items-center space-x-2 px-4 py-2 rounded-lg transition-all bg-gradient-to-r from-red-500 to-rose-500 text-white shadow-lg scale-105';
          } else {
            dislikeBtn.className = 'vote-btn flex items-center space-x-2 px-4 py-2 rounded-lg transition-all glass hover:bg-white/50 dark:hover:bg-white/10 text-gray-700 dark:text-gray-200';
          }
        });
      } else if (data.error) {
        alert(data.error);
      }
    } catch (err) {
      console.error('Vote error:', err);
      alert('An error occurred. Please try again.');
    }
  });
});
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
