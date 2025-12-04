<?php
$pdo = require_once "../db/db.php";
require_once "../flash.php";

// Redirect if user not logged in
if (!isset($_SESSION['user'])) {
    header("Location: ../login.php");
    exit;
}

$flash = get_flash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');

    if (!$first || !$last) {
        set_flash("error", "All fields are required.");
        header("Location: user_data.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
        $stmt->execute([$first, $last, $_SESSION['user']['id']]);
        set_flash("success", "Profile completed successfully!");
        header("Location: dashboard.php");
        exit;
    } catch (Exception $e) {
        set_flash("error", "Error updating profile.");
        header("Location: user_data.php");
        exit;
    }
}
?>
<?php
require_once "header.php";
?>


<div class="max-w-2xl mx-auto px-4 py-8">
  <div class="bg-white rounded-lg shadow-sm p-6 md:p-8">
    <h1 class="text-3xl font-bold text-blue-900 text-center mb-8">User Data</h1>
    <form method="POST" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-semibold text-blue-900 mb-2">First Name <span class="text-red-500">*</span></label>
          <input name="first_name" type="text" required class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-orange-500">
        </div>
        <div>
          <label class="block text-sm font-semibold text-blue-900 mb-2">Last Name <span class="text-red-500">*</span></label>
          <input name="last_name" type="text" required class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-orange-500">
        </div>
      </div>
      <button type="submit" class="w-full bg-red-500 text-white font-bold py-3 rounded hover:bg-orange-600 transition">Submit</button>
    </form>
  </div>

<?php if ($flash): ?>
<script>
Toastify({
    text: <?= json_encode($flash['message']); ?>,
    duration: 3500,
    gravity: "top",
    position: "right",
    close: true,
    backgroundColor: <?= json_encode($flash['type']==='success' ? "linear-gradient(to right, #00b09b, #96c93d)" : "linear-gradient(to right, #ff5f6d, #ffc371)") ?>
}).showToast();
</script>
<?php endif; ?>

</main>
</body>
</html>
