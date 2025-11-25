<?php
session_start();
require_once "../db/db.php";
require_once '../flash.php';

// Check if user is admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Fetch existing data to pre-fill the form
$about_content = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM about_us WHERE id = 1");
    $stmt->execute();
    $about_content = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$about_content) {
        $about_content = []; // Ensure it's an array even if no record exists
    }
} catch (Exception $e) {
    set_flash('error', 'Error fetching about us content: ' . $e->getMessage());
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check if a record with ID 1 exists
        $stmt = $pdo->prepare("SELECT id FROM about_us WHERE id = 1");
        $stmt->execute();
        $exists = $stmt->fetch();

        if ($exists) {
            // Update existing record
            $sql = "UPDATE about_us SET 
                        main_heading = :main_heading, main_paragraph = :main_paragraph, feature_1 = :feature_1, feature_2 = :feature_2, feature_3 = :feature_3, feature_4 = :feature_4, image_url = :image_url, 
                        stat_1_value = :stat_1_value, stat_1_label = :stat_1_label, stat_2_value = :stat_2_value, stat_2_label = :stat_2_label, stat_3_value = :stat_3_value, stat_3_label = :stat_3_label, stat_4_value = :stat_4_value, stat_4_label = :stat_4_label, 
                        sub_heading = :sub_heading, sub_paragraph = :sub_paragraph, cta_heading = :cta_heading, cta_paragraph = :cta_paragraph, cta_button_text = :cta_button_text
                    WHERE id = 1";
        } else {
            // Insert new record
            $sql = "INSERT INTO about_us (id, main_heading, main_paragraph, feature_1, feature_2, feature_3, feature_4, image_url, stat_1_value, stat_1_label, stat_2_value, stat_2_label, stat_3_value, stat_3_label, stat_4_value, stat_4_label, sub_heading, sub_paragraph, cta_heading, cta_paragraph, cta_button_text) 
                    VALUES (1, :main_heading, :main_paragraph, :feature_1, :feature_2, :feature_3, :feature_4, :image_url, :stat_1_value, :stat_1_label, :stat_2_value, :stat_2_label, :stat_3_value, :stat_3_label, :stat_4_value, :stat_4_label, :sub_heading, :sub_paragraph, :cta_heading, :cta_paragraph, :cta_button_text)";
        }

        $stmt = $pdo->prepare($sql);

        $params = [
            ':main_heading' => $_POST['main_heading'],
            ':main_paragraph' => $_POST['main_paragraph'],
            ':feature_1' => $_POST['feature_1'],
            ':feature_2' => $_POST['feature_2'],
            ':feature_3' => $_POST['feature_3'],
            ':feature_4' => $_POST['feature_4'],
            ':image_url' => $_POST['image_url'],
            ':stat_1_value' => $_POST['stat_1_value'],
            ':stat_1_label' => $_POST['stat_1_label'],
            ':stat_2_value' => $_POST['stat_2_value'],
            ':stat_2_label' => $_POST['stat_2_label'],
            ':stat_3_value' => $_POST['stat_3_value'],
            ':stat_3_label' => $_POST['stat_3_label'],
            ':stat_4_value' => $_POST['stat_4_value'],
            ':stat_4_label' => $_POST['stat_4_label'],
            ':sub_heading' => $_POST['sub_heading'],
            ':sub_paragraph' => $_POST['sub_paragraph'],
            ':cta_heading' => $_POST['cta_heading'],
            ':cta_paragraph' => $_POST['cta_paragraph'],
            ':cta_button_text' => $_POST['cta_button_text']
        ];

        $stmt->execute($params);

        set_flash('success', 'About Us page content has been updated successfully.');
        header("Location: add-about-us.php");
        exit;

    } catch (Exception $e) {
        set_flash('error', 'An error occurred: ' . $e->getMessage());
    }
}

$flash = get_flash();

function getValue($field) {
    global $about_content;
    return htmlspecialchars($about_content[$field] ?? '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin: Edit About Us</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>
<body class="bg-gray-100">

<div class="container mx-auto p-8">
    <h1 class="text-3xl font-bold text-blue-900 mb-6">Edit About Us Page</h1>

    <form action="add-about-us.php" method="POST" class="bg-white p-8 rounded-lg shadow-md space-y-6">

        <!-- Main Section -->
        <fieldset class="border p-4 rounded-md">
            <legend class="text-xl font-semibold px-2">Main Section</legend>
            <div class="space-y-4 mt-2">
                <div>
                    <label class="block text-sm font-medium">Main Heading</label>
                    <input type="text" name="main_heading" value="<?= getValue('main_heading') ?>" class="w-full px-3 py-2 border rounded" required>
                </div>
                <div>
                    <label class="block text-sm font-medium">Main Paragraph</label>
                    <textarea name="main_paragraph" rows="4" class="w-full px-3 py-2 border rounded" required><?= getValue('main_paragraph') ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium">Image URL</label>
                    <input type="text" name="image_url" value="<?= getValue('image_url') ?>" class="w-full px-3 py-2 border rounded" required>
                </div>
            </div>
        </fieldset>

        <!-- Features -->
        <fieldset class="border p-4 rounded-md">
            <legend class="text-xl font-semibold px-2">Features</legend>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                <input type="text" name="feature_1" placeholder="Feature 1" value="<?= getValue('feature_1') ?>" class="w-full px-3 py-2 border rounded">
                <input type="text" name="feature_2" placeholder="Feature 2" value="<?= getValue('feature_2') ?>" class="w-full px-3 py-2 border rounded">
                <input type="text" name="feature_3" placeholder="Feature 3" value="<?= getValue('feature_3') ?>" class="w-full px-3 py-2 border rounded">
                <input type="text" name="feature_4" placeholder="Feature 4" value="<?= getValue('feature_4') ?>" class="w-full px-3 py-2 border rounded">
            </div>
        </fieldset>

        <!-- Statistics -->
        <fieldset class="border p-4 rounded-md">
            <legend class="text-xl font-semibold px-2">Statistics</legend>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-2">
                <div>
                    <label>Stat 1 Value</label><input type="text" name="stat_1_value" value="<?= getValue('stat_1_value') ?>" class="w-full px-3 py-2 border rounded">
                    <label>Stat 1 Label</label><input type="text" name="stat_1_label" value="<?= getValue('stat_1_label') ?>" class="w-full px-3 py-2 border rounded mt-1">
                </div>
                <div>
                    <label>Stat 2 Value</label><input type="text" name="stat_2_value" value="<?= getValue('stat_2_value') ?>" class="w-full px-3 py-2 border rounded">
                    <label>Stat 2 Label</label><input type="text" name="stat_2_label" value="<?= getValue('stat_2_label') ?>" class="w-full px-3 py-2 border rounded mt-1">
                </div>
                <div>
                    <label>Stat 3 Value</label><input type="text" name="stat_3_value" value="<?= getValue('stat_3_value') ?>" class="w-full px-3 py-2 border rounded">
                    <label>Stat 3 Label</label><input type="text" name="stat_3_label" value="<?= getValue('stat_3_label') ?>" class="w-full px-3 py-2 border rounded mt-1">
                </div>
                <div>
                    <label>Stat 4 Value</label><input type="text" name="stat_4_value" value="<?= getValue('stat_4_value') ?>" class="w-full px-3 py-2 border rounded">
                    <label>Stat 4 Label</label><input type="text" name="stat_4_label" value="<?= getValue('stat_4_label') ?>" class="w-full px-3 py-2 border rounded mt-1">
                </div>
            </div>
        </fieldset>

        <!-- Sub Section -->
        <fieldset class="border p-4 rounded-md">
            <legend class="text-xl font-semibold px-2">"Where Access Unfolds" Section</legend>
            <div class="space-y-4 mt-2">
                <div>
                    <label class="block text-sm font-medium">Sub Heading</label>
                    <input type="text" name="sub_heading" value="<?= getValue('sub_heading') ?>" class="w-full px-3 py-2 border rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium">Sub Paragraph</label>
                    <textarea name="sub_paragraph" rows="4" class="w-full px-3 py-2 border rounded"><?= getValue('sub_paragraph') ?></textarea>
                </div>
            </div>
        </fieldset>

        <!-- CTA Section -->
        <fieldset class="border p-4 rounded-md">
            <legend class="text-xl font-semibold px-2">Call-to-Action Section</legend>
            <div class="space-y-4 mt-2">
                <div>
                    <label class="block text-sm font-medium">CTA Heading</label>
                    <input type="text" name="cta_heading" value="<?= getValue('cta_heading') ?>" class="w-full px-3 py-2 border rounded">
                </div>
                <div>
                    <label class="block text-sm font-medium">CTA Paragraph</label>
                    <textarea name="cta_paragraph" rows="2" class="w-full px-3 py-2 border rounded"><?= getValue('cta_paragraph') ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium">CTA Button Text</label>
                    <input type="text" name="cta_button_text" value="<?= getValue('cta_button_text') ?>" class="w-full px-3 py-2 border rounded">
                </div>
            </div>
        </fieldset>

        <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 rounded hover:bg-green-700 transition duration-200">
            Save Changes
        </button>
    </form>
</div>

<?php if ($flash): ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
  Toastify({
    text: <?= json_encode($flash['message']) ?>,
    duration: 4000,
    gravity: 'top',
    position: 'right',
    close: true,
    backgroundColor: <?= json_encode($flash['type']==='success' ? 'linear-gradient(to right, #00b09b, #96c93d)' : 'linear-gradient(to right, #ff5f6d, #ffc371)') ?>
  }).showToast();
});
</script>
<?php endif; ?>

</body>
</html>
