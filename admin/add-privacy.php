<?php
session_start();
require_once "../db/db.php";
require_once '../flash.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$content = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM privacy_policy WHERE id = 1");
    $stmt->execute();
    $content = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$content) $content = ['title' => '', 'content' => ''];
} catch (Exception $e) {
    set_flash('error', 'Error fetching content: ' . $e->getMessage());
    $content = ['title' => '', 'content' => ''];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $page_content = trim($_POST['content']);

    try {
        $stmt = $pdo->prepare("SELECT id FROM privacy_policy WHERE id = 1");
        $stmt->execute();
        $exists = $stmt->fetch();

        if ($exists) {
            $sql = "UPDATE privacy_policy SET title = ?, content = ? WHERE id = 1";
        } else {
            $sql = "INSERT INTO privacy_policy (id, title, content) VALUES (1, ?, ?)";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$title, $page_content]);

        set_flash('success', 'Privacy Policy updated successfully.');
        header("Location: add-privacy.php");
        exit;
    } catch (Exception $e) {
        set_flash('error', 'An error occurred: ' . $e->getMessage());
    }
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin: Edit Privacy Policy</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-8">
    <h1 class="text-3xl font-bold text-blue-900 mb-6">Edit Privacy Policy</h1>

    <form action="add-privacy.php" method="POST" class="bg-white p-8 rounded-lg shadow-md space-y-6">
        <div>
            <label class="block text-sm font-medium">Page Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($content['title']) ?>" class="w-full px-3 py-2 border rounded" required>
        </div>
        <div>
            <label class="block text-sm font-medium">Content</label>
            <textarea name="content" rows="15" class="w-full px-3 py-2 border rounded" required><?= htmlspecialchars($content['content']) ?></textarea>
        </div>
        <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 rounded hover:bg-green-700">Save Changes</button>
    </form>
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