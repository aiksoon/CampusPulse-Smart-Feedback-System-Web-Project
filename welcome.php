<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - CampusPulse</title>
    
    <!-- Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* ============================================ */
        /* WELCOME PAGE STYLES */
        /* ============================================ */
        
        /* Import Inter font from Google Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        /* Reset default margins and paddings */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* Set Inter as default font */
        body {
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }
        
        /* ============================================ */
        /* WELCOME SCREEN ANIMATION */
        /* ============================================ */
        
        /* Full-screen welcome overlay */
        /* Full-screen welcome overlay */
        .welcome-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 1s cubic-bezier(0.86, 0, 0.07, 1);
        }
        
        /* Slide up animation when dismissed */
        .welcome-screen.slide-up {
            transform: translateY(-100%);
        }
        
        /* Welcome content container with fade-in */
        .welcome-content {
            text-align: center;
            color: white;
            animation: fadeIn 1s ease-out;
        }
        
        /* ============================================ */
        /* KEYFRAME ANIMATIONS */
        /* ============================================ */
        
        /* Fade in animation for welcome content */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-animation {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            position: relative;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        .logo-circle {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 3px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 800;
        }
        
        .start-button {
            margin-top: 3rem;
            padding: 1rem 3rem;
            font-size: 1.25rem;
            font-weight: 600;
            border: 2px solid white;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            color: white;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .start-button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .start-button:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .start-button:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        
        .start-button span {
            position: relative;
            z-index: 1;
        }
        
        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
        }
        
        .floating-element {
            position: absolute;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 15s infinite;
        }
        
        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0);
            }
            50% {
                transform: translateY(-100px) translateX(100px);
            }
        }
        
        .floating-element:nth-child(1) { left: 10%; top: 20%; animation-delay: 0s; }
        .floating-element:nth-child(2) { left: 20%; top: 80%; animation-delay: 2s; }
        .floating-element:nth-child(3) { left: 80%; top: 30%; animation-delay: 4s; }
        .floating-element:nth-child(4) { left: 70%; top: 70%; animation-delay: 6s; }
        .floating-element:nth-child(5) { left: 40%; top: 10%; animation-delay: 8s; }
        
        .tech-grid {
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: gridMove 20s linear infinite;
        }
        
        @keyframes gridMove {
            0% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(50px, 50px);
            }
        }
    </style>
</head>
<body>
    <div class="welcome-screen" id="welcomeScreen">
        <div class="tech-grid"></div>
        <div class="floating-elements">
            <div class="floating-element"></div>
            <div class="floating-element"></div>
            <div class="floating-element"></div>
            <div class="floating-element"></div>
            <div class="floating-element"></div>
        </div>
        
        <div class="welcome-content">
            <div class="logo-animation">
                <div class="logo-circle">
                    <span>CP</span>
                </div>
            </div>
            <h1 class="text-6xl font-bold mb-4">CampusPulse</h1>
            <p class="text-xl text-white/80 mb-2">Your Voice, Our Priority</p>
            <p class="text-lg text-white/60">Intelligent Campus Feedback System</p>
            
            <button class="start-button" onclick="startExperience()">
                <span>START EXPERIENCE →</span>
            </button>
        </div>
    </div>
    
    <script>
        function startExperience() {
            const welcomeScreen = document.getElementById('welcomeScreen');
            welcomeScreen.classList.add('slide-up');
            
            setTimeout(() => {
                window.location.href = 'feed.php';
            }, 1000);
        }
        
        // Auto-start after 8 seconds if user doesn't click
        setTimeout(() => {
            if (!document.getElementById('welcomeScreen').classList.contains('slide-up')) {
                startExperience();
            }
        }, 8000);
    </script>
</body>
</html>
