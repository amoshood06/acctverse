<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coming Soon - Acctvarse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@400;500;600;700&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        h1, h2, h3 {
            font-family: 'Playfair Display', serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(255, 127, 0, 0.5); }
            50% { box-shadow: 0 0 40px rgba(255, 127, 0, 0.8); }
        }

        .text-gradient {
            background: linear-gradient(135deg, #ff7f00 0%, #ff9933 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-text {
            font-size: clamp(2rem, 8vw, 5rem);
            line-height: 1.1;
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .glass-effect {
            background: rgba(255, 127, 0, 0.1);
            border: 1px solid rgba(255, 127, 0, 0.3);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="gradient-bg text-white overflow-x-hidden">
    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 bg-black/30 backdrop-blur-md border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-orange-400 to-orange-600 rounded-lg"></div>
                    <span class="font-bold text-lg">Acctvarse</span>
                </div>
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#" class="text-gray-300 hover:text-white transition">About</a>
                    <a href="#" class="text-gray-300 hover:text-white transition">Features</a>
                    <a href="#" class="text-gray-300 hover:text-white transition">Contact</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="min-h-screen flex items-center justify-center px-4 pt-20 pb-10">
        <div class="max-w-6xl w-full">
            <!-- Main Content -->
            <div class="text-center mb-16">
                <div class="mb-8">
                    <span class="inline-block px-4 py-2 bg-white/10 rounded-full text-sm text-orange-400 mb-6 backdrop-blur-sm border border-white/10">
                        ✨ Something Amazing is Coming
                    </span>
                </div>

                <h1 class="hero-text font-black mb-6">
                    Get Ready for the Future of
                    <span class="text-gradient"> Social Media Management</span>
                </h1>

                <p class="text-xl text-gray-300 max-w-2xl mx-auto mb-12">
                    We're building something extraordinary. Get early access and be among the first to experience the revolution in account management.
                </p>

                <!-- Email Signup -->
                <div class="max-w-md mx-auto mb-12">
                    <form class="flex gap-2 mb-4">
                        <input 
                            type="email" 
                            placeholder="Enter your email" 
                            class="flex-1 px-6 py-4 bg-white/10 rounded-lg border border-white/20 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition"
                            required
                        >
                        <button 
                            type="submit" 
                            class="px-8 py-4 bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg font-semibold hover:from-orange-600 hover:to-orange-700 transition transform hover:scale-105 pulse-glow"
                        >
                            Notify Me
                        </button>
                    </form>
                    <p class="text-sm text-gray-400">No spam, just updates about launch</p>
                </div>

                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-16">
                    <div class="glass-effect p-6 rounded-lg">
                        <p class="text-3xl font-bold text-orange-400 mb-1">500K+</p>
                        <p class="text-sm text-gray-400">Waiting</p>
                    </div>
                    <div class="glass-effect p-6 rounded-lg">
                        <p class="text-3xl font-bold text-orange-400 mb-1">150+</p>
                        <p class="text-sm text-gray-400">Countries</p>
                    </div>
                    <div class="glass-effect p-6 rounded-lg">
                        <p class="text-3xl font-bold text-orange-400 mb-1">24/7</p>
                        <p class="text-sm text-gray-400">Support</p>
                    </div>
                    <div class="glass-effect p-6 rounded-lg">
                        <p class="text-3xl font-bold text-orange-400 mb-1">100%</p>
                        <p class="text-sm text-gray-400">Secure</p>
                    </div>
                </div>
            </div>

            <!-- Animated Illustration -->
            <div class="relative h-64 md:h-96 flex items-center justify-center">
                <div class="absolute w-40 h-40 md:w-64 md:h-64 bg-gradient-to-br from-orange-500/20 to-orange-600/10 rounded-full blur-3xl floating"></div>
                <div class="relative z-10 text-center">
                    <div class="text-6xl md:text-8xl mb-4">🚀</div>
                    <p class="text-lg text-orange-400 font-semibold">Launching Soon</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Preview Section -->
    <section class="py-20 px-4 border-t border-white/10">
        <div class="max-w-6xl mx-auto">
            <h2 class="text-4xl md:text-5xl font-black text-center mb-16">
                What's Coming
            </h2>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass-effect p-8 rounded-xl group hover:bg-white/20 transition">
                    <div class="text-4xl mb-4">⚡</div>
                    <h3 class="text-xl font-bold mb-3">Lightning Fast</h3>
                    <p class="text-gray-400">Experience unprecedented speed in account management with our optimized platform.</p>
                </div>

                <!-- Feature 2 -->
                <div class="glass-effect p-8 rounded-xl group hover:bg-white/20 transition">
                    <div class="text-4xl mb-4">🔒</div>
                    <h3 class="text-xl font-bold mb-3">Bank-Level Security</h3>
                    <p class="text-gray-400">Your data is protected with enterprise-grade encryption and security protocols.</p>
                </div>

                <!-- Feature 3 -->
                <div class="glass-effect p-8 rounded-xl group hover:bg-white/20 transition">
                    <div class="text-4xl mb-4">🎯</div>
                    <h3 class="text-xl font-bold mb-3">Smart Analytics</h3>
                    <p class="text-gray-400">Make data-driven decisions with advanced insights and real-time reporting.</p>
                </div>

                <!-- Feature 4 -->
                <div class="glass-effect p-8 rounded-xl group hover:bg-white/20 transition">
                    <div class="text-4xl mb-4">🌍</div>
                    <h3 class="text-xl font-bold mb-3">Global Reach</h3>
                    <p class="text-gray-400">Connect with audiences worldwide with localized support for 150+ countries.</p>
                </div>

                <!-- Feature 5 -->
                <div class="glass-effect p-8 rounded-xl group hover:bg-white/20 transition">
                    <div class="text-4xl mb-4">🤖</div>
                    <h3 class="text-xl font-bold mb-3">AI-Powered</h3>
                    <p class="text-gray-400">Leverage artificial intelligence for smarter automation and recommendations.</p>
                </div>

                <!-- Feature 6 -->
                <div class="glass-effect p-8 rounded-xl group hover:bg-white/20 transition">
                    <div class="text-4xl mb-4">💬</div>
                    <h3 class="text-xl font-bold mb-3">24/7 Support</h3>
                    <p class="text-gray-400">Get instant help from our dedicated support team whenever you need it.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Countdown Section -->
    <section class="py-20 px-4 border-t border-white/10">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-4xl md:text-5xl font-black mb-8">
                Don't Miss Out
            </h2>
            <p class="text-xl text-gray-300 mb-12">
                Join thousands of users getting ready for the revolution in account management.
            </p>
            
            <div class="glass-effect p-8 rounded-xl border border-white/20">
                <div class="grid grid-cols-4 gap-4 mb-8">
                    <div>
                        <p class="text-3xl md:text-4xl font-bold text-orange-400" id="days">00</p>
                        <p class="text-sm text-gray-400 mt-2">Days</p>
                    </div>
                    <div>
                        <p class="text-3xl md:text-4xl font-bold text-orange-400" id="hours">00</p>
                        <p class="text-sm text-gray-400 mt-2">Hours</p>
                    </div>
                    <div>
                        <p class="text-3xl md:text-4xl font-bold text-orange-400" id="minutes">00</p>
                        <p class="text-sm text-gray-400 mt-2">Minutes</p>
                    </div>
                    <div>
                        <p class="text-3xl md:text-4xl font-bold text-orange-400" id="seconds">00</p>
                        <p class="text-sm text-gray-400 mt-2">Seconds</p>
                    </div>
                </div>
                <p class="text-gray-400">Until our official launch</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-white/10 py-8 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h4 class="font-semibold mb-4">Company</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="#" class="hover:text-white transition">About</a></li>
                        <li><a href="#" class="hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="hover:text-white transition">Careers</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Product</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="#" class="hover:text-white transition">Features</a></li>
                        <li><a href="#" class="hover:text-white transition">Pricing</a></li>
                        <li><a href="#" class="hover:text-white transition">Security</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Resources</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="#" class="hover:text-white transition">Docs</a></li>
                        <li><a href="#" class="hover:text-white transition">API</a></li>
                        <li><a href="#" class="hover:text-white transition">Support</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2 text-gray-400 text-sm">
                        <li><a href="#" class="hover:text-white transition">Privacy</a></li>
                        <li><a href="#" class="hover:text-white transition">Terms</a></li>
                        <li><a href="#" class="hover:text-white transition">Contact</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-white/10 pt-8 text-center text-gray-400 text-sm">
                <p>&copy; 2025 AcctGlobe. All rights reserved.</p>
                <div class="flex justify-center gap-6 mt-4">
                    <a href="#" class="hover:text-white transition">Twitter</a>
                    <a href="#" class="hover:text-white transition">LinkedIn</a>
                    <a href="#" class="hover:text-white transition">Instagram</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Countdown timer
        function updateCountdown() {
            const launchDate = new Date('2025-12-31T23:59:59').getTime();
            const now = new Date().getTime();
            const distance = launchDate - now;

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById('days').textContent = String(days).padStart(2, '0');
            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);

        // Email form submission
        document.querySelector('form').addEventListener('submit', (e) => {
            e.preventDefault();
            alert('Thanks for signing up! Check your email for updates.');
            e.target.reset();
        });
    </script>
</body>
</html>
