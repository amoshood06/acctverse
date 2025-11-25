<?php
require_once "./db/db.php";
include 'main_header.php';
include 'header.php';

$content = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM terms_and_conditions WHERE id = 1");
    $stmt->execute();
    $content = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Could not fetch terms_and_conditions content: " . $e->getMessage());
}

function e_policy($field, $default = '') {
    global $content;
    return $content[$field] ?? $default;
}
?>

<main class="bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-lg shadow-md">
        <h1 class="text-3xl sm:text-4xl font-bold text-blue-900 mb-6 border-b pb-4">
            <?= htmlspecialchars(e_policy('title', 'Terms and Conditions')) ?>
        </h1>
        <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
            <?php if (!empty(e_policy('content'))): ?>
                <?= nl2br(htmlspecialchars(e_policy('content'))) ?>
            <?php else: ?>
                <p>The terms and conditions have not been set yet. Please check back later.</p>
            <?php endif; ?>
        </div>
        <p class="text-sm text-gray-500 mt-8 pt-4 border-t">
            Last updated: <?= !empty($content['updated_at']) ? date('F j, Y', strtotime($content['updated_at'])) : 'N/A' ?>
        </p>
    </div>
</main>

<?php
include 'footer.php';
?>
