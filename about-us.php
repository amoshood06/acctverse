<?php
require_once "./db/db.php";

$content = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM about_us WHERE id = 1");
    $stmt->execute();
    $content = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Handle error, maybe log it or show a default message
    error_log("Could not fetch about_us content: " . $e->getMessage());
}

// Helper to avoid errors if content is not set
function e_about($field, $default = '') {
    global $content;
    return htmlspecialchars($content[$field] ?? $default);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Acctglobe</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-purple-600 to-orange-500 rounded-lg"></div>
                    <span class="font-bold text-lg">Acctglobe</span>
                </div>
                <div class="hidden md:flex gap-8">
                    <a href="index" class="text-gray-700 hover:text-orange-500">Home</a>
                    <a href="about-us" class="text-orange-500 font-semibold">About Us</a>
                    <a href="#" class="text-gray-700 hover:text-orange-500">Services</a>
                    <a href="#" class="text-gray-700 hover:text-orange-500">Contact</a>
                </div>
                <button class="bg-orange-500 text-white px-6 py-2 rounded hover:bg-orange-600 transition">
                    <?= e_about('cta_button_text', 'Get Started') ?>
                </button>
            </div>
        </div>
    </nav>

    <!-- About Section -->
    <section class="py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-4">
                <p class="text-orange-500 font-semibold text-sm">About Us</p>
            </div>
            
            <!-- Main Content -->
            <div class="grid md:grid-cols-2 gap-12 items-center mb-16">
                <!-- Illustration -->
                <div class="flex justify-center">
                    <img src="<?= e_about('image_url', 'https://hebbkx1anhila5yf.public.blob.vercel-storage.com/net.PNG-dEKGOHoP58y9Xwd9kGzIZrRMxaCTCL.png') ?>" 
                         alt="Social Media Illustration" 
                         class="w-full max-w-md h-auto">
                </div>
                
                <!-- Content -->
                <div>
                    <h1 class="text-4xl md:text-5xl font-bold text-blue-900 mb-6 text-balance">
                        <?= e_about('main_heading', 'Unlock the Power of Established Social Media Presence') ?>
                    </h1>
                    <p class="text-gray-600 leading-relaxed mb-8">
                        <?= e_about('main_paragraph', 'Default paragraph text about social media presence.') ?>
                    </p>
                    
                    <!-- Features List -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0 mt-1">
                                <svg class="w-4 h-4 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700 font-medium"><?= e_about('feature_1', 'Instant Credibility') ?></span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0 mt-1">
                                <svg class="w-4 h-4 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700 font-medium"><?= e_about('feature_2', 'Save Time and Effort') ?></span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0 mt-1">
                                <svg class="w-4 h-4 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700 font-medium"><?= e_about('feature_3', 'Targeted Audience') ?></span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-6 h-6 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0 mt-1">
                                <svg class="w-4 h-4 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <span class="text-gray-700 font-medium"><?= e_about('feature_4', 'Strategic Expansion') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="bg-gradient-to-r from-blue-900 to-blue-800 py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl md:text-5xl font-bold text-white mb-2"><?= e_about('stat_1_value', '45') ?></div>
                    <p class="text-blue-100"><?= e_about('stat_1_label', 'Team members') ?></p>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-bold text-white mb-2"><?= e_about('stat_2_value', '60') ?></div>
                    <p class="text-blue-100"><?= e_about('stat_2_label', 'Winning awards') ?></p>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-bold text-white mb-2"><?= e_about('stat_3_value', '25') ?></div>
                    <p class="text-blue-100"><?= e_about('stat_3_label', 'Completed project') ?></p>
                </div>
                <div>
                    <div class="text-4xl md:text-5xl font-bold text-white mb-2"><?= e_about('stat_4_value', '359') ?></div>
                    <p class="text-blue-100"><?= e_about('stat_4_label', 'Happy Clients') ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Where Access Unfolds Section -->
    <section class="py-16 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <p class="text-orange-500 font-semibold text-sm mb-2">Acctglobe</p>
                <h2 class="text-3xl md:text-4xl font-bold text-blue-900 mb-8">
                    <?= e_about('sub_heading', 'Where Access Unfolds') ?>
                </h2>
                <p class="text-gray-600 max-w-3xl mx-auto leading-relaxed text-balance">
                    <?= e_about('sub_paragraph', 'Default paragraph for the "Where Access Unfolds" section.') ?>
                </p>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center">
            <h3 class="text-2xl md:text-3xl font-bold text-blue-900 mb-4">
                <?= e_about('cta_heading', 'Ready to elevate your social media presence?') ?>
            </h3>
            <p class="text-gray-600 mb-8">
                <?= e_about('cta_paragraph', 'Join thousands of satisfied clients who have transformed their online presence.') ?>
            </p>
            <button class="bg-orange-500 text-white px-8 py-3 rounded font-semibold hover:bg-orange-600 transition">
                <?= e_about('cta_button_text', 'Start Your Journey Today') ?>
            </button>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <h4 class="text-white font-semibold mb-4">Company</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-white transition">About</a></li>
                        <li><a href="#" class="hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="hover:text-white transition">Careers</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Product</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-white transition">Features</a></li>
                        <li><a href="#" class="hover:text-white transition">Pricing</a></li>
                        <li><a href="#" class="hover:text-white transition">Security</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Support</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-white transition">Help Center</a></li>
                        <li><a href="#" class="hover:text-white transition">Contact</a></li>
                        <li><a href="#" class="hover:text-white transition">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-semibold mb-4">Legal</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-white transition">Privacy</a></li>
                        <li><a href="#" class="hover:text-white transition">Terms</a></li>
                        <li><a href="#" class="hover:text-white transition">Cookie</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center">
                <p>&copy; 2025 Acctglobe. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
