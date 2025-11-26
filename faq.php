<?php
require_once "./db/db.php";
include 'main_header.php';
include 'header.php';

$faqs = [];
try {
    $faqs = $pdo->query("SELECT * FROM faqs ORDER BY category, id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Could not fetch FAQs: " . $e->getMessage());
}

$grouped_faqs = [];
foreach ($faqs as $faq) {
    $grouped_faqs[$faq['category']][] = $faq;
}
?>

<main class="bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-blue-900">Frequently Asked Questions</h1>
            <p class="mt-4 text-lg text-gray-600">Find answers to common questions about our services.</p>
        </div>

        <div class="mt-12 space-y-10">
            <?php if (empty($grouped_faqs)): ?>
                <p class="text-center text-gray-500">No FAQs available at the moment. Please check back later.</p>
            <?php else: ?>
                <?php foreach ($grouped_faqs as $category => $faq_list): ?>
                    <div class="bg-white p-8 rounded-lg shadow-md">
                        <h2 class="text-2xl font-semibold text-orange-600 mb-6"><?= htmlspecialchars($category) ?></h2>
                        <div class="space-y-6">
                            <?php foreach ($faq_list as $faq): ?>
                                <details class="group">
                                    <summary class="flex justify-between items-center font-medium cursor-pointer list-none">
                                        <span class="text-lg text-blue-800"><?= htmlspecialchars($faq['question']) ?></span>
                                        <span class="transition group-open:rotate-180">
                                            <svg fill="none" height="24" shape-rendering="geometricPrecision" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24" width="24"><path d="M6 9l6 6 6-6"></path></svg>
                                        </span>
                                    </summary>
                                    <p class="text-gray-700 mt-3 group-open:animate-fadeIn">
                                        <?= nl2br(htmlspecialchars($faq['answer'])) ?>
                                    </p>
                                </details>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
    .group-open\:animate-fadeIn {
        animation: fadeIn 0.5s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<?php
include 'footer.php';
?>

