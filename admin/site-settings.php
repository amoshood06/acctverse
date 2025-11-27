<?php
require_once "../db/db.php";
require_once '../flash.php';
include 'header.php';

// Fetch current logo
$current_logo_filename = 'acctverse.png'; // Default logo
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_name = 'site_logo'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && !empty($result['setting_value'])) {
        $current_logo_filename = $result['setting_value'];
    }
} catch (Exception $e) {
    set_flash('error', 'Could not fetch current logo settings.');
}
$current_logo_path = '../assets/image/' . $current_logo_filename;

// Handle form submission for logo upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['site_logo'])) {
    if ($_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['site_logo'];
        $uploadDir = '../assets/image/';
        $fileName = 'logo_' . time() . '_' . basename($file['name']);
        $targetFile = $uploadDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Basic validation
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            set_flash('error', 'File is not a valid image.');
        } elseif (!in_array($imageFileType, ['png', 'jpg', 'jpeg', 'svg', 'gif'])) {
            set_flash('error', 'Sorry, only JPG, JPEG, PNG, SVG & GIF files are allowed.');
        } elseif (move_uploaded_file($file['tmp_name'], $targetFile)) {
            // Update database
            try {
                $stmt = $pdo->prepare("INSERT INTO site_settings (setting_name, setting_value) VALUES ('site_logo', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
                $stmt->execute([$fileName, $fileName]);
                set_flash('success', 'Logo updated successfully.');
            } catch (Exception $e) {
                set_flash('error', 'Database error: ' . $e->getMessage());
            }
        } else {
            set_flash('error', 'Sorry, there was an error uploading your file.');
        }
    } else {
        set_flash('error', 'No file was uploaded or an error occurred.');
    }
    header("Location: site-settings.php");
    exit;
}

$flash = get_flash();
?>    
    <div class="container mx-auto">
    <h1 class="text-3xl font-bold text-blue-900 mb-6">Site Settings</h1>

    <div class="bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">Site Logo</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
            <div>
                <p class="font-medium mb-2">Current Logo:</p>
                <div class="border p-4 rounded-md bg-gray-50 inline-block">
                    <img src="<?= htmlspecialchars($current_logo_path) ?>" alt="Current Site Logo" class="max-h-20">
                </div>
            </div>
            <form action="site-settings.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Upload New Logo</label>
                    <input type="file" name="site_logo" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                <button type="submit" class="bg-green-600 text-white font-bold py-2 px-6 rounded hover:bg-green-700">Upload & Save</button>
            </form>
        </div>
    </div>
</div>

<?php if ($flash): ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
  Toastify({ text: <?= json_encode($flash['message']) ?>, duration: 4000, gravity: 'top', position: 'right', close: true, backgroundColor: <?= json_encode($flash['type']==='success' ? 'linear-gradient(to right, #00b09b, #96c93d)' : 'linear-gradient(to right, #ff5f6d, #ffc371)') ?> }).showToast();
});
</script>
<?php endif; ?>
</body>
</html>