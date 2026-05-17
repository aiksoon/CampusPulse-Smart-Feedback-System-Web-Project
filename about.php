<?php
/**
 * About Page - CampusPulse
 * 
 * This page displays information about the CampusPulse feedback system,
 * including platform statistics, category breakdowns, ratings, and team information.
 * 
 * Features:
 * - Total users, feedback, and votes statistics
 * - Category distribution with percentages
 * - Website ratings and average scores
 * - Platform features showcase
 * - Team member profiles
 */

// Include required files for page header and database connection
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/inc/db.php';

// Get database connection
$pdo = getPDO();

// ============================================
// STATISTICS GATHERING
// ============================================

// Get total number of enabled (active) users
// Get total number of enabled (active) users
$stmt = $pdo->query('SELECT COUNT(*) FROM users WHERE enabled = 1');
$total_users = $stmt->fetchColumn();

// Get total number of approved feedback posts
$stmt = $pdo->query("SELECT COUNT(*) FROM feedback WHERE status = 'Approved'");
$total_feedback = $stmt->fetchColumn();

// Get total count of positive votes (upvotes)
$stmt = $pdo->query('SELECT COUNT(*) FROM votes WHERE vote = 1');
$total_upvotes = $stmt->fetchColumn();

// Get total count of negative votes (downvotes)
$stmt = $pdo->query('SELECT COUNT(*) FROM votes WHERE vote = -1');
$total_downvotes = $stmt->fetchColumn();

// ============================================
// CATEGORY STATISTICS
// ============================================

// Define available feedback categories
// Define available feedback categories
$categories = ['Course', 'Infrastructure', 'Cafeterias'];
$category_stats = [];

// Get feedback count for each category
$stmt = $pdo->query('SELECT category, COUNT(*) as count FROM feedback GROUP BY category');
$category_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Calculate total feedback across all categories
$total_category_feedback = array_sum($category_counts);

// Calculate count and percentage for each category
foreach ($categories as $cat) {
    $count = $category_counts[$cat] ?? 0;
    // Calculate percentage, avoiding division by zero
    $percentage = $total_category_feedback > 0 ? round(($count / $total_category_feedback) * 100, 1) : 0;
    $category_stats[$cat] = ['count' => $count, 'percentage' => $percentage];
}

// ============================================
// WEBSITE RATINGS
// ============================================

// Initialize rating variables
$avg_rating = 0;
$total_ratings = 0;

// Attempt to fetch website rating statistics
try {
    $stmt = $pdo->query('SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM website_ratings');
    $rating_data = $stmt->fetch(PDO::FETCH_ASSOC);
    // Round average rating to 1 decimal place
    $avg_rating = round($rating_data['avg_rating'] ?? 0, 1);
    $total_ratings = $rating_data['total'] ?? 0;
} catch (Exception $e) {
    // Table might not exist yet, use default values
}

// ============================================
// UI CONFIGURATION
// ============================================

// Define gradient colors for each category (for visual styling)
$category_colors = [
    'Course' => 'from-green-500 to-emerald-500',
    'Infrastructure' => 'from-blue-500 to-cyan-500',
    'Cafeterias' => 'from-orange-500 to-amber-500'
];

// Define emoji icons for each category
$category_icons = [
    'Course' => '📚',
    'Infrastructure' => '🏢',
    'Cafeterias' => '🍽️'
];
?>

<div class="min-h-[calc(100vh-20rem)] py-12">
  <div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-12 text-center">
      <h1 class="text-5xl font-bold gradient-text mb-4 animate-float">About CampusPulse</h1>
      <p class="text-xl text-gray-600 dark:text-gray-400">Empowering students through transparent feedback</p>
    </div>
    
    <!-- Stats Grid -->
    <div class="grid md:grid-cols-4 gap-6 mb-12">
      <div class="glass rounded-2xl p-6 text-center hover:scale-105 transition-all">
        <div class="text-4xl font-bold gradient-text mb-2"><?= $total_users ?></div>
        <div class="text-gray-600 dark:text-gray-400">Registered Users</div>
      </div>
      <div class="glass rounded-2xl p-6 text-center hover:scale-105 transition-all">
        <div class="text-4xl font-bold gradient-text mb-2"><?= $total_feedback ?></div>
        <div class="text-gray-600 dark:text-gray-400">Approved Feedback</div>
      </div>
      <div class="glass rounded-2xl p-6 text-center hover:scale-105 transition-all">
        <div class="text-4xl font-bold gradient-text mb-2"><?= $total_upvotes + $total_downvotes ?></div>
        <div class="text-gray-600 dark:text-gray-400">Total Votes</div>
      </div>
      <div class="glass rounded-2xl p-6 text-center hover:scale-105 transition-all">
        <div class="text-4xl font-bold gradient-text mb-2"><?= $avg_rating ?> ⭐</div>
        <div class="text-gray-600 dark:text-gray-400">Average Rating</div>
      </div>
    </div>
    
    <!-- Overview Section -->
    <div class="glass rounded-2xl p-8 mb-8">
      <h2 class="text-3xl font-bold text-gray-800 dark:text-white mb-4 flex items-center">
        <span class="text-4xl mr-3">📊</span>
        Platform Overview
      </h2>
      <p class="text-lg text-gray-700 dark:text-gray-300 leading-relaxed">
        CampusPulse is your voice on campus! Our platform allows students to share feedback about various aspects of campus life, helping to create a better educational environment for everyone. We believe in transparency, constructive dialogue, and continuous improvement.
      </p>
    </div>
    
    <!-- Category Distribution -->
    <div class="glass rounded-2xl p-8 mb-8">
      <h2 class="text-3xl font-bold text-gray-800 dark:text-white mb-6">Feedback Distribution</h2>
      <div class="space-y-6">
        <?php foreach ($categories as $cat):
          $stats = $category_stats[$cat];
          $color = $category_colors[$cat];
          $icon = $category_icons[$cat];
        ?>
          <div>
            <div class="flex justify-between items-center mb-2">
              <span class="font-semibold text-gray-800 dark:text-white flex items-center">
                <span class="text-2xl mr-2"><?= $icon ?></span>
                <?= $cat ?>
              </span>
              <span class="text-gray-600 dark:text-gray-400"><?= $stats['count'] ?> (<?= $stats['percentage'] ?>%)</span>
            </div>
            <div class="w-full h-4 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r <?= $color ?> rounded-full transition-all duration-1000" style="width: <?= $stats['percentage'] ?>%"></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    
    <!-- Features -->
    <div class="grid md:grid-cols-3 gap-6 mb-8">
      <div class="glass rounded-2xl p-6 text-center">
        <div class="w-16 h-16 rounded-xl bg-gradient-to-r from-tech-blue to-tech-purple flex items-center justify-center text-3xl mb-4 mx-auto">
          🔒
        </div>
        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Anonymous Posting</h3>
        <p class="text-gray-600 dark:text-gray-400">Share your honest feedback without revealing your identity</p>
      </div>
      
      <div class="glass rounded-2xl p-6 text-center">
        <div class="w-16 h-16 rounded-xl bg-gradient-to-r from-tech-purple to-tech-pink flex items-center justify-center text-3xl mb-4 mx-auto">
          ⚡
        </div>
        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Real-time Voting</h3>
        <p class="text-gray-600 dark:text-gray-400">Vote on feedback to show what matters most to the community</p>
      </div>
      
      <div class="glass rounded-2xl p-6 text-center">
        <div class="w-16 h-16 rounded-xl bg-gradient-to-r from-tech-pink to-tech-blue flex items-center justify-center text-3xl mb-4 mx-auto">
          🎯
        </div>
        <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Categorized Feedback</h3>
        <p class="text-gray-600 dark:text-gray-400">Organize feedback by categories for better management</p>
      </div>
    </div>
    
    <!-- Mission & Vision -->
    <div class="grid md:grid-cols-2 gap-6">
      <div class="glass rounded-2xl p-8">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4 flex items-center">
          <span class="text-3xl mr-3">🎯</span>
          Our Mission
        </h2>
        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
          To create a transparent and engaging platform where every student's voice is heard, valued, and contributes to making our campus better for everyone.
        </p>
      </div>
      
      <div class="glass rounded-2xl p-8">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-4 flex items-center">
          <span class="text-3xl mr-3">🚀</span>
          Our Vision
        </h2>
        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
          To be the leading student feedback platform that drives positive change and continuous improvement in campus life across all universities.
        </p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
