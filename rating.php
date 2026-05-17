<?php
/**
 * Website Rating - CampusPulse
 * 
 * Page for users to rate and review the CampusPulse platform.
 * Displays average rating, user reviews, and allows submissions.
 * 
 * Features:
 * - 5-star rating system
 * - Optional text review
 * - View all ratings from other users
 * - See average rating and total count
 * - Update existing rating (one per user)
 * - Display recent reviews with user info
 * 
 * Rating System:
 * - Scale: 1-5 stars
 * - One rating per user (can update)
 * - Optional review text
 * - Public display with username
 * 
 * Security:
 * - Requires authentication to submit
 * - Users can only have one rating
 * - Validation for rating range
 */

// Include required dependencies
require_once __DIR__ . '/inc/header.php';
require_once __DIR__ . '/inc/db.php';

// Get current user (if logged in)
$me = current_user();

// Initialize status variables
$success = '';
$error = '';
$existing_rating = null;

// ============================================
// BASE URL CONFIGURATION
// ============================================

// Get base URL for proper link generation
if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    $base_url = '/smart_feedback';
} else {
    $base_url = '';
}

// ============================================
// CHECK EXISTING USER RATING
// ============================================

// If user is logged in, check if they've already rated
if ($me) {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT * FROM website_ratings WHERE user_id = ?');
        $stmt->execute([$me['id']]);
        $existing_rating = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Table might not exist if setup hasn't been run
    }
}

// ============================================
// HANDLE RATING SUBMISSION
// ============================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $me) {
    // Get and validate form data
    $rating = intval($_POST['rating'] ?? 0);
    $review = trim($_POST['review'] ?? '');
    
    // Validate rating is within valid range
    if ($rating < 1 || $rating > 5) {
        $error = 'Please select a rating between 1 and 5 stars.';
    } else {
        try {
            $pdo = getPDO();
            
            if ($existing_rating) {
                // Update existing rating
                $stmt = $pdo->prepare('UPDATE website_ratings SET rating = ?, review = ?, updated_at = ? WHERE user_id = ?');
                $stmt->execute([$rating, $review, date('Y-m-d H:i:s'), $me['id']]);
                $success = 'Your rating has been updated. Thank you for your feedback!';
            } else {
                // Insert new rating
                $stmt = $pdo->prepare('INSERT INTO website_ratings (user_id, rating, review, created_at) VALUES (?, ?, ?, ?)');
                $stmt->execute([$me['id'], $rating, $review, date('Y-m-d H:i:s')]);
                $success = 'Thank you for rating CampusPulse!';
            }
            
            // Refresh existing rating
            $stmt = $pdo->prepare('SELECT * FROM website_ratings WHERE user_id = ?');
            $stmt->execute([$me['id']]);
            $existing_rating = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            $error = 'Failed to save rating. Please try again. Error: ' . $e->getMessage();
        }
    }
}

// Get all ratings for display
$all_ratings = [];
$avg_rating = 0;
$total_ratings = 0;
try {
    $pdo = getPDO();
    $stmt = $pdo->query('SELECT r.*, u.username, u.profile_picture FROM website_ratings r LEFT JOIN users u ON r.user_id = u.id ORDER BY r.created_at DESC LIMIT 10');
    $all_ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->query('SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM website_ratings');
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    $avg_rating = round($stats['avg_rating'] ?? 0, 1);
    $total_ratings = $stats['total'] ?? 0;
} catch (Exception $e) {
    // Table might not exist
}
?>

<div class="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 dark:from-gray-900 dark:via-purple-900/20 dark:to-gray-900 py-8">
    <div class="container mx-auto px-4 max-w-6xl">
        
        <!-- Hero Section -->
        <div class="text-center mb-12 animate-fade-in">
            <div class="inline-block mb-4">
                <div class="text-6xl animate-float">⭐</div>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent mb-4">
                Rate CampusPulse
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                Your feedback helps us improve and serve you better 🚀
            </p>
        </div>

        <!-- Overall Rating Stats -->
        <div class="bg-white/95 dark:bg-gray-800/95 rounded-3xl shadow-2xl p-8 mb-8 border border-white/20">
            <div class="grid md:grid-cols-3 gap-6 text-center">
                <div>
                    <div class="text-5xl font-bold bg-gradient-to-r from-yellow-400 to-orange-500 bg-clip-text text-transparent mb-2">
                        <?php echo number_format($avg_rating, 1); ?>
                    </div>
                    <div class="text-2xl mb-2">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <span class="<?php echo $i <= round($avg_rating) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 font-medium">Average Rating</p>
                </div>
                <div>
                    <div class="text-5xl font-bold text-purple-600 dark:text-purple-400 mb-2">
                        <?php echo $total_ratings; ?>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 font-medium">Total Reviews</p>
                </div>
                <div>
                    <div class="text-5xl mb-2">🎯</div>
                    <p class="text-gray-600 dark:text-gray-400 font-medium">Your Voice Matters</p>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            
            <!-- Rating Form -->
            <div class="bg-white/95 dark:bg-gray-800/95 rounded-3xl shadow-2xl overflow-hidden border border-white/20">
                <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 p-6 text-white">
                    <h2 class="text-2xl font-bold flex items-center gap-3">
                        <span class="text-3xl">📝</span>
                        <?php echo $existing_rating ? 'Update Your Rating' : 'Share Your Experience'; ?>
                    </h2>
                    <p class="text-blue-100 mt-2">
                        <?php echo $existing_rating ? 'Modify your previous feedback' : 'Help us improve by rating our platform'; ?>
                    </p>
                </div>

                <div class="p-6">
                    <?php if (!$me): ?>
                        <div class="text-center py-8">
                            <div class="text-6xl mb-4">🔒</div>
                            <p class="text-gray-600 dark:text-gray-400 mb-6">Please log in to rate CampusPulse</p>
                            <a href="login.php" class="inline-block px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                                Login Now
                            </a>
                        </div>
                    <?php else: ?>
                        
                        <?php if ($success): ?>
                            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 rounded-lg animate-slide-in">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">✅</span>
                                    <p class="text-green-700 dark:text-green-300"><?php echo htmlspecialchars($success); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 rounded-lg animate-slide-in">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">❌</span>
                                    <p class="text-red-700 dark:text-red-300"><?php echo htmlspecialchars($error); ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="space-y-6">
                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-3 text-center">
                                    How would you rate your experience? ⭐
                                </label>
                                <div class="flex justify-center gap-2 mb-2" id="star-rating">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <button type="button" 
                                                class="star-btn text-5xl transition-all duration-200 hover:scale-110 focus:outline-none <?php echo ($existing_rating && $existing_rating['rating'] >= $i) ? 'active' : ''; ?>" 
                                                data-rating="<?php echo $i; ?>">
                                            <span class="<?php echo ($existing_rating && $existing_rating['rating'] >= $i) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'; ?>">★</span>
                                        </button>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="rating-value" value="<?php echo $existing_rating['rating'] ?? ''; ?>" required>
                                <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-2" id="rating-text">
                                    <?php 
                                    if ($existing_rating) {
                                        $texts = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
                                        echo $texts[$existing_rating['rating']] ?? 'Select your rating';
                                    } else {
                                        echo 'Select your rating';
                                    }
                                    ?>
                                </p>
                            </div>

                            <div>
                                <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-2">
                                    Share Your Thoughts (Optional) 💭
                                </label>
                                <textarea name="review" 
                                          rows="5" 
                                          class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-purple-500 dark:focus:border-purple-400 focus:ring-2 focus:ring-purple-500/20 transition-all duration-200 resize-none"
                                          placeholder="Tell us what you think about CampusPulse..."><?php echo htmlspecialchars($existing_rating['review'] ?? ''); ?></textarea>
                            </div>

                            <button type="submit" 
                                    class="w-full py-4 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 text-white rounded-xl font-bold text-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
                                <?php echo $existing_rating ? '✨ Update Rating' : '🚀 Submit Rating'; ?>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Reviews -->
            <div class="bg-white/95 dark:bg-gray-800/95 rounded-3xl shadow-2xl p-6 border border-white/20">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6 flex items-center gap-3">
                    <span class="text-3xl">💬</span>
                    Recent Reviews
                </h2>

                <?php if (empty($all_ratings)): ?>
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">📭</div>
                        <p class="text-gray-500 dark:text-gray-400 text-lg">No reviews yet</p>
                        <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">Be the first to share your experience!</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4 max-h-[600px] overflow-y-auto pr-2">
                        <?php foreach($all_ratings as $idx => $r): ?>
                            <div class="bg-white/95 dark:bg-gray-700/95 rounded-xl p-4 border border-gray-200/50 dark:border-gray-600/50 hover:shadow-lg transition-shadow duration-200">
                                <div class="flex items-start gap-3">
                                    <img src="<?php echo htmlspecialchars($r['profile_picture'] ? $base_url . '/storage/profile_pics/' . $r['profile_picture'] : 'https://ui-avatars.com/api/?name=' . urlencode($r['username']) . '&background=667eea&color=fff'); ?>" 
                                         alt="<?php echo htmlspecialchars($r['username']); ?>"
                                         class="w-12 h-12 rounded-full object-cover ring-2 ring-purple-500/50">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="font-semibold text-gray-800 dark:text-white">
                                                <?php echo htmlspecialchars($r['username']); ?>
                                            </span>
                                            <div class="text-yellow-400 text-lg">
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <span class="<?php echo $i <= $r['rating'] ? '' : 'opacity-30'; ?>">★</span>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <?php if ($r['review']): ?>
                                            <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed mb-2">
                                                <?php echo nl2br(htmlspecialchars($r['review'])); ?>
                                            </p>
                                        <?php endif; ?>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">
                                            <?php echo date('M j, Y', strtotime($r['created_at'])); ?>
                                        </p>
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

<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.1);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: linear-gradient(to bottom, #667eea, #764ba2);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(to bottom, #764ba2, #f093fb);
}
</style>

<script>
// Star rating interaction
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-btn');
    const ratingInput = document.getElementById('rating-value');
    const ratingText = document.getElementById('rating-text');
    
    const ratingLabels = ['', 'Poor 😞', 'Fair 😐', 'Good 😊', 'Very Good 😃', 'Excellent 🤩'];
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.dataset.rating;
            ratingInput.value = rating;
            ratingText.textContent = ratingLabels[rating];
            
            stars.forEach(s => {
                const starRating = s.dataset.rating;
                const starIcon = s.querySelector('span');
                if (starRating <= rating) {
                    s.classList.add('active');
                    starIcon.classList.remove('text-gray-300', 'dark:text-gray-600');
                    starIcon.classList.add('text-yellow-400');
                } else {
                    s.classList.remove('active');
                    starIcon.classList.remove('text-yellow-400');
                    starIcon.classList.add('text-gray-300', 'dark:text-gray-600');
                }
            });
        });
        
        star.addEventListener('mouseenter', function() {
            const rating = this.dataset.rating;
            stars.forEach(s => {
                const starRating = s.dataset.rating;
                const starIcon = s.querySelector('span');
                if (starRating <= rating) {
                    starIcon.classList.remove('text-gray-300', 'dark:text-gray-600');
                    starIcon.classList.add('text-yellow-400');
                }
            });
        });
    });
    
    const starContainer = document.getElementById('star-rating');
    starContainer.addEventListener('mouseleave', function() {
        const currentRating = ratingInput.value;
        stars.forEach(s => {
            const starRating = s.dataset.rating;
            const starIcon = s.querySelector('span');
            if (starRating <= currentRating) {
                starIcon.classList.remove('text-gray-300', 'dark:text-gray-600');
                starIcon.classList.add('text-yellow-400');
            } else {
                starIcon.classList.remove('text-yellow-400');
                starIcon.classList.add('text-gray-300', 'dark:text-gray-600');
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
