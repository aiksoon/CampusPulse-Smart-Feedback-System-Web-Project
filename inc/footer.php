    </div> <!-- Close max-w-7xl -->
  </div> <!-- Close pt-24 -->
  
  <!-- ============================================ -->
  <!-- FOOTER SECTION -->
  <!-- ============================================ -->
  
  <!-- Main Footer Container -->
  <footer class="mt-20 glass border-t border-white/20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      
      <!-- Footer Grid Layout -->
      <!-- 3 columns on desktop, 1 column on mobile -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- ============================================ -->
        <!-- ABOUT SECTION -->
        <!-- ============================================ -->
        <div>
          <h3 class="text-lg font-bold gradient-text mb-4">CampusPulse</h3>
          <p class="text-gray-600 dark:text-gray-400 text-sm">
            Your voice, our priority. Empowering students to shape their campus experience through intelligent feedback.
          </p>
        </div>
        
        <!-- ============================================ -->
        <!-- QUICK LINKS SECTION -->
        <!-- ============================================ -->
        <div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Quick Links</h3>
          <ul class="space-y-2 text-sm">
            <li><a href="<?= $base_url ?>/feed.php" class="text-gray-600 dark:text-gray-400 hover:text-tech-blue dark:hover:text-tech-pink transition-colors">Home</a></li>
            <li><a href="<?= $base_url ?>/submit.php" class="text-gray-600 dark:text-gray-400 hover:text-tech-blue dark:hover:text-tech-pink transition-colors">Submit Feedback</a></li>
            <li><a href="<?= $base_url ?>/about.php" class="text-gray-600 dark:text-gray-400 hover:text-tech-blue dark:hover:text-tech-pink transition-colors">About Us</a></li>
            <li><a href="<?= $base_url ?>/contact.php" class="text-gray-600 dark:text-gray-400 hover:text-tech-blue dark:hover:text-tech-pink transition-colors">Contact</a></li>
          </ul>
        </div>
        
        <!-- ============================================ -->
        <!-- CONTACT INFO SECTION -->
        <!-- ============================================ -->
        <div>
          <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Get In Touch</h3>
          <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
            <li class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
              </svg>
              info@campuspulse.edu
            </li>
            <li class="flex items-center">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
              Campus Administration Building
            </li>
          </ul>
        </div>
      </div>
      
      <!-- ============================================ -->
      <!-- COPYRIGHT SECTION -->
      <!-- ============================================ -->
      <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700 text-center">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          &copy; <?= date('Y') ?> CampusPulse. All rights reserved. | Made with 💜 for students
        </p>
      </div>
    </div>
  </footer>
  
  </body>
  </html>
