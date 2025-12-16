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

<?php
include 'main_header.php';
include 'header.php';
?>
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
<?php
include 'footer.php';
?>
