<?php
require_once "../db/db.php";
require_once '../flash.php';
include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add or Update Article
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category = trim($_POST['category']);
    $article_id = filter_input(INPUT_POST, 'article_id', FILTER_VALIDATE_INT);

    try {
        if ($article_id) {
            $sql = "UPDATE help_articles SET title = ?, content = ?, category = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $content, $category, $article_id]);
            set_flash('success', 'Help article updated successfully.');
        } else {
            $sql = "INSERT INTO help_articles (title, content, category) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $content, $category]);
            set_flash('success', 'New help article added successfully.');
        }
    } catch (Exception $e) {
        set_flash('error', 'Database error: ' . $e->getMessage());
    }
    header("Location: help-center.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Delete Article
    $article_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    try {
        $stmt = $pdo->prepare("DELETE FROM help_articles WHERE id = ?");
        $stmt->execute([$article_id]);
        set_flash('success', 'Help article deleted successfully.');
    } catch (Exception $e) {
        set_flash('error', 'Failed to delete article.');
    }
    header("Location: help-center.php");
    exit;
}

$articles = [];
try {
    $articles = $pdo->query("SELECT * FROM help_articles ORDER BY category, id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    set_flash('error', 'Could not fetch help articles.');
}

$flash = get_flash();
?>    
<div class="container mx-auto">
    <h1 class="text-3xl font-bold text-blue-900 mb-6">Manage Help Center</h1>

    <!-- Add/Edit Form -->
    <form action="help-center.php" method="POST" class="bg-white p-8 rounded-lg shadow-md space-y-6 mb-8">
        <h2 class="text-xl font-semibold">Add New Article</h2>
        <div>
            <label class="block text-sm font-medium">Title</label>
            <input type="text" name="title" class="w-full px-3 py-2 border rounded" required>
        </div>
        <div>
            <label class="block text-sm font-medium">Content</label>
            <textarea name="content" rows="6" class="w-full px-3 py-2 border rounded" required></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium">Category</label>
            <input type="text" name="category" placeholder="e.g., Getting Started" class="w-full px-3 py-2 border rounded" value="General">
        </div>
        <button type="submit" class="bg-green-600 text-white font-bold py-2 px-6 rounded hover:bg-green-700">Add Article</button>
    </form>

    <!-- Existing Articles -->
    <div class="bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">Existing Articles</h2>
        <div class="space-y-4">
            <?php if (empty($articles)): ?>
                <p class="text-gray-500">No articles found.</p>
            <?php else: ?>
                <?php foreach ($articles as $article): ?>
                    <div class="border p-4 rounded-md">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-bold text-blue-800"><?= htmlspecialchars($article['title']) ?></p>
                                <p class="text-gray-600 mt-1 truncate"><?= htmlspecialchars(substr($article['content'], 0, 150)) ?>...</p>
                                <p class="text-xs text-gray-500 mt-2">Category: <?= htmlspecialchars($article['category']) ?></p>
                            </div>
                            <div class="flex gap-2 flex-shrink-0 ml-4">
                                <a href="help-center.php?action=delete&id=<?= $article['id'] ?>" onclick="return confirm('Are you sure?')" class="text-red-500 hover:underline text-sm">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($flash): ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
  Toastify({
    text: <?= json_encode($flash['message']) ?>,
    duration: 4000, gravity: 'top', position: 'right', close: true,
    backgroundColor: <?= json_encode($flash['type']==='success' ? 'linear-gradient(to right, #00b09b, #96c93d)' : 'linear-gradient(to right, #ff5f6d, #ffc371)') ?>
  }).showToast();
});
</script>
<?php endif; ?>
</body>
</html>
