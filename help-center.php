<?php
require_once "./db/db.php";
include 'main_header.php';
include 'header.php';

$articles = [];
try {
    $articles = $pdo->query("SELECT * FROM help_articles ORDER BY category, title ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Could not fetch help articles: " . $e->getMessage());
}

$grouped_articles = [];
foreach ($articles as $article) {
    $grouped_articles[$article['category']][] = $article;
}
?>

<main class="bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-blue-900">Help Center</h1>
            <p class="mt-4 text-lg text-gray-600">Find guides and articles to help you get the most out of our platform.</p>
        </div>

        <div class="mt-12 space-y-10">
            <?php if (empty($grouped_articles)): ?>
                <p class="text-center text-gray-500">No help articles available at the moment.</p>
            <?php else: ?>
                <?php foreach ($grouped_articles as $category => $article_list): ?>
                    <div class="bg-white p-8 rounded-lg shadow-md">
                        <h2 class="text-2xl font-semibold text-orange-600 mb-6"><?= htmlspecialchars($category) ?></h2>
                        <ul class="space-y-3">
                            <?php foreach ($article_list as $article): ?>
                                <li>
                                    <a href="#" class="text-blue-700 hover:underline hover:text-blue-900 text-lg">
                                        <?= htmlspecialchars($article['title']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
include 'footer.php';
?>
