<?php
require_once "../db/db.php";
require_once '../flash.php';
include 'header.php';

$uploadDir = '../assets/image/sliders/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Handle slide deletion
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])) {
    $id_to_delete = filter_var($_GET['delete_id'], FILTER_VALIDATE_INT);
    if ($id_to_delete) {
        try {
            // First, get the image path to delete the file
            $stmt = $pdo->prepare("SELECT image_path FROM sliders WHERE id = ?");
            $stmt->execute([$id_to_delete]);
            $slide = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($slide && file_exists($slide['image_path'])) {
                unlink($slide['image_path']); // Delete the image file
            }

            // Then, delete the record from the database
            $deleteStmt = $pdo->prepare("DELETE FROM sliders WHERE id = ?");
            $deleteStmt->execute([$id_to_delete]);

            set_flash('success', 'Slider image deleted successfully.');
        } catch (Exception $e) {
            set_flash('error', 'Failed to delete slider: ' . $e->getMessage());
        }
    } else {
        set_flash('error', 'Invalid ID for deletion.');
    }
    header("Location: add-slider.php");
    exit;
}

// Handle new slide upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['slider_image'])) {
    $alt_text = trim($_POST['alt_text'] ?? 'Slider Image');

    if ($_FILES['slider_image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['slider_image'];
        $fileName = 'slider_' . time() . '_' . basename($file['name']);
        $targetFile = $uploadDir . $fileName;
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Basic validation
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            set_flash('error', 'File is not a valid image.');
        } elseif (!in_array($imageFileType, ['png', 'jpg', 'jpeg', 'gif', 'webp'])) {
            set_flash('error', 'Sorry, only JPG, JPEG, PNG, WEBP & GIF files are allowed.');
        } elseif (move_uploaded_file($file['tmp_name'], $targetFile)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO sliders (image_path, alt_text) VALUES (?, ?)");
                $stmt->execute([$targetFile, $alt_text]);
                set_flash('success', 'New slider image uploaded successfully.');
            } catch (Exception $e) {
                set_flash('error', 'Database error: ' . $e->getMessage());
            }
        } else {
            set_flash('error', 'Sorry, there was an error uploading your file.');
        }
    } else {
        set_flash('error', 'No file was uploaded or an error occurred.');
    }
    header("Location: add-slider.php");
    exit;
}

// Fetch existing slides to display
$slides = [];
try {
    $stmt = $pdo->query("SELECT id, image_path, alt_text FROM sliders ORDER BY created_at DESC");
    $slides = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    set_flash('error', 'Could not fetch slider images.');
}

$flash = get_flash();
?>    
<div class="container mx-auto">
    <h1 class="text-3xl font-bold text-blue-900 mb-6">Manage Homepage Slider</h1>

    <!-- Add New Slide Form -->
    <form action="add-slider.php" method="POST" enctype="multipart/form-data" class="bg-white p-8 rounded-lg shadow-md space-y-6 mb-8">
        <h2 class="text-xl font-semibold">Add New Slide</h2>
        <div>
            <label class="block text-sm font-medium">Slider Image (Recommended size: 1200x400px)</label>
            <input type="file" name="slider_image" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        </div>
        <div>
            <label class="block text-sm font-medium">Alt Text (for accessibility)</label>
            <input type="text" name="alt_text" placeholder="e.g., Special promotion on all products" class="w-full px-3 py-2 border rounded" required>
        </div>
        <button type="submit" class="bg-green-600 text-white font-bold py-2 px-6 rounded hover:bg-green-700">Upload Slide</button>
    </form>

    <!-- Existing Slides -->
    <div class="bg-white p-8 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">Existing Slides</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (empty($slides)): ?>
                <p class="text-gray-500 col-span-full">No slider images have been uploaded yet.</p>
            <?php else: ?>
                <?php foreach ($slides as $slide): ?>
                    <div class="border p-4 rounded-md text-center">
                        <img src="<?= htmlspecialchars($slide['image_path']) ?>" alt="<?= htmlspecialchars($slide['alt_text']) ?>" class="w-full h-32 object-cover rounded-md mb-4">
                        <p class="text-sm text-gray-600 mb-4 truncate" title="<?= htmlspecialchars($slide['alt_text']) ?>"><?= htmlspecialchars($slide['alt_text']) ?></p>
                        <a href="add-slider.php?delete_id=<?= $slide['id'] ?>" onclick="return confirm('Are you sure you want to delete this slide?')" class="text-red-500 hover:underline text-sm font-semibold">Delete</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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