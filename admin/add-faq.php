<?php
session_start();
require_once "../db/db.php";
require_once '../flash.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add or Update FAQ
    $question = trim($_POST['question']);
    $answer = trim($_POST['answer']);
    $category = trim($_POST['category']);
    $faq_id = filter_input(INPUT_POST, 'faq_id', FILTER_VALIDATE_INT);

    try {
        if ($faq_id) {
            $sql = "UPDATE faqs SET question = ?, answer = ?, category = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$question, $answer, $category, $faq_id]);
            set_flash('success', 'FAQ updated successfully.');
        } else {
            $sql = "INSERT INTO faqs (question, answer, category) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$question, $answer, $category]);
            set_flash('success', 'New FAQ added successfully.');
        }
    } catch (Exception $e) {
        set_flash('error', 'Database error: ' . $e->getMessage());
    }
    header("Location: faq.php");
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    // Delete FAQ
    $faq_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    try {
        $stmt = $pdo->prepare("DELETE FROM faqs WHERE id = ?");
        $stmt->execute([$faq_id]);
        set_flash('success', 'FAQ deleted successfully.');
    } catch (Exception $e) {
        set_flash('error', 'Failed to delete FAQ.');
    }
    header("Location: faq.php");
    exit;
}

$faqs = [];
try {
    $faqs = $pdo->query("SELECT * FROM faqs ORDER BY category, id DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    set_flash('error', 'Could not fetch FAQs.');
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin: Manage FAQs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-8">
    <h1 class="text-3xl font-bold text-blue-900 mb-6">Manage FAQs</h1>

    <!-- Add/Edit Form -->
    <form action="faq.php" method="POST" class="bg-white p-8 rounded-lg shadow-md space-y-6 mb-8">
        <h2 class="text-xl font-semibold">Add New FAQ</h2>
        <div>
            <label class="block text-sm font-medium">Question</label>
            <input type="text" name="question" class="w-full px-3 py-2 border rounded" required>
        </div>
        <div>
            <label class="block text-sm font-medium">Answer</label>
            <textarea name="answer" rows="4" class="w-full px-3 py-2 border rounded" required></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium">Category</label>
            <input type="text" name="category" placeholder="e.g., General, Billing" class="w-full px-3 py-2 border rounded" value="General">
        </div>
        <button type="submit" class="bg-green-600 text-white font-bold py-2 px-6 rounded hover:bg-green-700">Add FAQ</button>
    </form>

    <!-- Existing FAQs -->
    <div class="bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">Existing FAQs</h2>
        <div class="space-y-4">
            <?php if (empty($faqs)): ?>
                <p class="text-gray-500">No FAQs found.</p>
            <?php else: ?>
                <?php foreach ($faqs as $faq): ?>
                    <div class="border p-4 rounded-md">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-bold text-blue-800"><?= htmlspecialchars($faq['question']) ?></p>
                                <p class="text-gray-600 mt-1"><?= nl2br(htmlspecialchars($faq['answer'])) ?></p>
                                <p class="text-xs text-gray-500 mt-2">Category: <?= htmlspecialchars($faq['category']) ?></p>
                            </div>
                            <div class="flex gap-2 flex-shrink-0 ml-4">
                                <!-- Edit functionality can be added here with a separate edit form -->
                                <a href="faq.php?action=delete&id=<?= $faq['id'] ?>" onclick="return confirm('Are you sure?')" class="text-red-500 hover:underline text-sm">Delete</a>
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

