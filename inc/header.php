<?php
/**
 * Header Template - CampusPulse
 * 
 * Common header for all pages, includes navigation, styles, and scripts
 * 
 * Features:
 * - Responsive navigation bar
 * - Dark mode toggle
 * - User authentication menu
 * - Admin menu (for admin users)
 * - Mobile menu
 * - Tailwind CSS configuration
 * - Custom animations
 * - Performance optimizations
 * 
 * Dependencies:
 * - inc/db.php: User authentication functions
 * - Tailwind CSS CDN
 * - Google Fonts (Inter)
 */

// Include database functions and authentication
require_once __DIR__ . '/db.php';

// Get current user (if logged in)
$me = current_user();

// Get base URL from config using centralized function
$base_url = base_url();
?>
<!doctype html>
<html lang="en" class="scroll-smooth">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CampusPulse - Campus Feedback System</title>
    
    <!-- ============================================ -->
    <!-- TAILWIND CSS CONFIGURATION -->
    <!-- ============================================ -->
    
    <!-- Tailwind CSS CDN - Modern utility-first CSS framework -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      // ============================================
      // DARK MODE INITIALIZATION
      // ============================================
      
      // Prevent flash of wrong theme on page load 防止页面加载时主题闪烁
      // Check localStorage or system preference 检查本地存储或系统偏好
      if (localStorage.getItem('darkMode') === 'dark' || (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
      }
      
      // ============================================
      // TAILWIND CUSTOM CONFIGURATION
      // ============================================
      
      tailwind.config = {
        darkMode: 'class', // Enable class-based dark mode
        theme: {
          extend: {
            colors: {
              primary: {
                50: '#f0f4ff',
                100: '#e0e7ff',
                200: '#c7d2fe',
                300: '#a5b4fc',
                400: '#818cf8',
                500: '#6366f1',
                600: '#4f46e5',
                700: '#4338ca',
                800: '#3730a3',
                900: '#312e81',
              },
              tech: {
                dark: '#0a0e27',
                blue: '#667eea',
                purple: '#764ba2',
                pink: '#f093fb',
              }
            },
            fontFamily: {
              sans: ['Inter', 'sans-serif'],
            },
            animation: {
              'gradient': 'gradient 8s linear infinite',
              'float': 'float 6s ease-in-out infinite',
              'glow': 'glow 2s ease-in-out infinite alternate',
            },
            keyframes: {
              gradient: {
                '0%, 100%': {
                  'background-size': '200% 200%',
                  'background-position': 'left center'
                },
                '50%': {
                  'background-size': '200% 200%',
                  'background-position': 'right center'
                },
              },
              float: {
                '0%, 100%': { transform: 'translateY(0px)' },
                '50%': { transform: 'translateY(-20px)' },
              },
              glow: {
                'from': {
                  'box-shadow': '0 0 20px rgba(102, 126, 234, 0.5)',
                },
                'to': {
                  'box-shadow': '0 0 30px rgba(118, 75, 162, 0.8)',
                },
              }
            },
          }
        }
      }
    </script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
      /* Critical Performance Optimizations */
      * {
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
      }
      
      html {
        scroll-behavior: auto; /* Changed from smooth for better performance */
        overflow-x: hidden;
        /* Force hardware acceleration */
        transform: translateZ(0);
        -webkit-transform: translateZ(0);
      }
      
      body {
        overflow-x: hidden;
        /* Optimize rendering */
        -webkit-overflow-scrolling: touch;
      }
      
      /* Optimize all elements for GPU */
      * {
        -webkit-backface-visibility: hidden;
        backface-visibility: hidden;
        -webkit-perspective: 1000;
        perspective: 1000;
      }
      
      /* Reduce animations for better performance */
      @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
          animation-duration: 0.01ms !important;
          animation-iteration-count: 1 !important;
          transition-duration: 0.01ms !important;
        }
      }
      
      /* Optimized animations - use only transform and opacity */
      .animate-float,
      .animate-glow,
      .animate-fade-in,
      .animate-slide-in,
      .animate-scale-in {
        transform: translateZ(0);
        will-change: auto; /* Changed from 'transform, opacity' - only use when animating */
      }
      
      /* Optimize scrolling performance */
      .scroll-container {
        contain: layout style paint;
        content-visibility: auto;
      }
      
      /* Custom scrollbar - simplified */
      ::-webkit-scrollbar {
        width: 8px;
      }
      
      ::-webkit-scrollbar-track {
        background: #f1f1f1;
      }
      
      .dark ::-webkit-scrollbar-track {
        background: #1a1a2e;
      }
      
      ::-webkit-scrollbar-thumb {
        background: #667eea;
        border-radius: 4px;
      }
      
      ::-webkit-scrollbar-thumb:hover {
        background: #764ba2;
      }
      
      /* Optimized glass effect - removed expensive backdrop-filter */
      .glass {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.3);
        /* Removed backdrop-filter for better performance */
      }
      
      .dark .glass {
        background: rgba(10, 14, 39, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.1);
      }
      
      /* Optional: Add blur only on hover for specific elements */
      .glass-blur:hover {
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
      }
      
      /* Gradient text - optimized */
      .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        /* Add containment */
        contain: paint;
      }
      
      /* Mobile menu animation - simplified */
      #mobileMenu {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.2s ease-out; /* Faster transition */
      }
      
      #mobileMenu.show {
        max-height: 500px;
      }
      
      /* Optimize images */
      img {
        content-visibility: auto;
        contain: layout style paint;
      }
      
      /* Optimize cards and containers */
      .card,
      .feedback-card,
      [class*="rounded-"] {
        contain: layout style paint;
      }
    </style>
  </head>
  <body class="bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-tech-dark dark:via-gray-900 dark:to-purple-900 min-h-screen">
    
  <?php
  // Get current page for active nav state
  $current_page = basename($_SERVER['PHP_SELF']);
  ?>
  
  <!-- Navigation Bar -->
  <nav class="fixed top-0 left-0 right-0 z-50 glass">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-20">
        <!-- Logo -->
        <a href="<?= $base_url ?>/index.php" class="flex items-center space-x-3 group">
          <div class="w-10 h-10 rounded-full bg-gradient-to-r from-tech-blue via-tech-purple to-tech-pink flex items-center justify-center font-bold text-white text-lg shadow-lg group-hover:animate-glow transition-all">
            CP
          </div>
          <span class="text-2xl font-bold gradient-text">CampusPulse</span>
        </a>
        
        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-1">
          <a href="<?= $base_url ?>/feed.php" class="px-4 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/10 transition-all <?= ($current_page === 'feed.php' || $current_page === 'index.php') ? 'bg-white/70 dark:bg-white/20 font-semibold' : '' ?>">
            <span class="mr-2">🏠</span>Pulse Feed
          </a>
          <a href="<?= $base_url ?>/submit.php" class="px-4 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/10 transition-all <?= $current_page === 'submit.php' ? 'bg-white/70 dark:bg-white/20 font-semibold' : '' ?>">
            <span class="mr-2">✍️</span>Submit
          </a>
          <?php if ($me): ?>
            <a href="<?= $base_url ?>/my_feedback.php" class="px-4 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/10 transition-all <?= $current_page === 'my_feedback.php' ? 'bg-white/70 dark:bg-white/20 font-semibold' : '' ?>">
              <span class="mr-2">📋</span>My Feedback
            </a>
          <?php endif; ?>
          <a href="<?= $base_url ?>/about.php" class="px-4 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/10 transition-all <?= $current_page === 'about.php' ? 'bg-white/70 dark:bg-white/20 font-semibold' : '' ?>">
            <span class="mr-2">ℹ️</span>About
          </a>
          <a href="<?= $base_url ?>/contact.php" class="px-4 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/10 transition-all <?= $current_page === 'contact.php' ? 'bg-white/70 dark:bg-white/20 font-semibold' : '' ?>">
            <span class="mr-2">✉️</span>Contact
          </a>
          <a href="<?= $base_url ?>/rating.php" class="px-4 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/10 transition-all <?= $current_page === 'rating.php' ? 'bg-white/70 dark:bg-white/20 font-semibold' : '' ?>">
            <span class="mr-2">⭐</span>Rate Us
          </a>
        </div>
        
        <!-- Right side actions -->
        <div class="flex items-center space-x-4">
          <!-- Dark Mode Toggle -->
          <button id="darkModeToggle" class="p-2 rounded-lg hover:bg-white/50 dark:hover:bg-white/10 transition-all" aria-label="Toggle dark mode">
            <svg id="sunIcon" class="w-6 h-6 text-yellow-500 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <svg id="moonIcon" class="w-6 h-6 text-purple-500 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
            </svg>
          </button>
          
          <?php if (!$me): ?>
            <a href="<?= $base_url ?>/login.php" class="hidden md:block px-4 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/10 transition-all">
              Login
            </a>
            <a href="<?= $base_url ?>/register.php" class="hidden md:block px-6 py-2 rounded-lg bg-gradient-to-r from-tech-blue via-tech-purple to-tech-pink text-white font-semibold hover:shadow-lg hover:scale-105 transition-all">
              Register
            </a>
          <?php else: ?>
            <!-- Profile Dropdown -->
            <div class="relative">
              <button id="profileButton" class="flex items-center space-x-2 p-1 rounded-lg hover:bg-white/50 dark:hover:bg-white/10 transition-all">
                <?php
                $profile_pic = !empty($me['profile_picture']) ? $base_url . '/storage/profile_pics/' . $me['profile_picture'] : $base_url . '/assets/default-avatar.png';
                if (!empty($me['profile_picture'])) {
                    $profile_pic .= '?t=' . time();
                }
                ?>
                <img src="<?= htmlspecialchars($profile_pic) ?>" alt="Profile" class="w-10 h-10 rounded-full border-2 border-white/30 object-cover" onerror="this.src='<?= $base_url ?>/assets/default-avatar.png'">
              </button>
              
              <!-- Dropdown Menu -->
              <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-56 glass rounded-xl shadow-2xl overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                  <p class="font-semibold text-gray-800 dark:text-white"><?= htmlspecialchars($me['username']) ?></p>
                  <p class="text-sm text-gray-600 dark:text-gray-400"><?= htmlspecialchars($me['email'] ?? '') ?></p>
                </div>
                <div class="py-2">
                  <a href="<?= $base_url ?>/profile.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/10 transition-all">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Profile
                  </a>
                  <?php if ($me['is_admin']): ?>
                    <a href="<?= $base_url ?>/admin/dashboard.php" class="flex items-center px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/10 transition-all">
                      <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      </svg>
                      Admin Dashboard
                    </a>
                  <?php endif; ?>
                  <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
                  <a href="<?= $base_url ?>/logout.php" class="flex items-center px-4 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Logout
                  </a>
                </div>
              </div>
            </div>
          <?php endif; ?>
          
          <!-- Mobile menu button -->
          <button id="mobileMenuButton" class="md:hidden p-2 rounded-lg hover:bg-white/50 dark:hover:bg-white/10 transition-all">
            <svg class="w-6 h-6 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
          </button>
        </div>
      </div>
      
      <!-- Mobile Navigation -->
      <div id="mobileMenu" class="md:hidden">
        <div class="py-4 space-y-2">
          <a href="<?= $base_url ?>/index.php" class="block px-4 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/10 transition-all <?= $current_page === 'index.php' ? 'bg-white/70 dark:bg-white/20 font-semibold' : '' ?>">
            🏠 Pulse Feed
          </a>
          <a href="<?= $base_url ?>/submit.php" class="block px-4 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/10 transition-all <?= $current_page === 'submit.php' ? 'bg-white/70 dark:bg-white/20 font-semibold' : '' ?>">
            ✍️ Submit
          </a>
          <?php if ($me): ?>
            <a href="<?= $base_url ?>/my_feedback.php" class="block px-4 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/10 transition-all <?= $current_page === 'my_feedback.php' ? 'bg-white/70 dark:bg-white/20 font-semibold' : '' ?>">
              📋 My Feedback
            </a>
          <?php endif; ?>
          <a href="<?= $base_url ?>/about.php" class="block px-4 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/10 transition-all <?= $current_page === 'about.php' ? 'bg-white/70 dark:bg-white/20 font-semibold' : '' ?>">
            ℹ️ About
          </a>
          <a href="<?= $base_url ?>/contact.php" class="block px-4 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/10 transition-all <?= $current_page === 'contact.php' ? 'bg-white/70 dark:bg-white/20 font-semibold' : '' ?>">
            ✉️ Contact
          </a>
          <a href="<?= $base_url ?>/rating.php" class="block px-4 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/10 transition-all <?= $current_page === 'rating.php' ? 'bg-white/70 dark:bg-white/20 font-semibold' : '' ?>">
            ⭐ Rate Us
          </a>
          <?php if (!$me): ?>
            <a href="<?= $base_url ?>/login.php" class="block px-4 py-2 rounded-lg text-gray-700 dark:text-gray-200 hover:bg-white/50 dark:hover:bg-white/10 transition-all">
              Login
            </a>
            <a href="<?= $base_url ?>/register.php" class="block px-4 py-2 rounded-lg bg-gradient-to-r from-tech-blue via-tech-purple to-tech-pink text-white font-semibold text-center">
              Register
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>
  
  <!-- Add padding to account for fixed navbar -->
  <div class="pt-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
  
  <script>
    // Dark Mode Toggle
    const darkModeToggle = document.getElementById('darkModeToggle');
    const html = document.documentElement;
    
    darkModeToggle?.addEventListener('click', () => {
      html.classList.toggle('dark');
      localStorage.setItem('darkMode', html.classList.contains('dark') ? 'dark' : 'light');
    });
    
    // Profile Dropdown
    const profileButton = document.getElementById('profileButton');
    const profileDropdown = document.getElementById('profileDropdown');
    
    profileButton?.addEventListener('click', (e) => {
      e.stopPropagation();
      profileDropdown.classList.toggle('hidden');
    });
    
    document.addEventListener('click', (e) => {
      if (profileDropdown && !profileDropdown.contains(e.target) && !profileButton.contains(e.target)) {
        profileDropdown.classList.add('hidden');
      }
    });
    
    // Mobile Menu Toggle
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const mobileMenu = document.getElementById('mobileMenu');
    
    mobileMenuButton?.addEventListener('click', () => {
      mobileMenu.classList.toggle('show');
    });
  </script>
