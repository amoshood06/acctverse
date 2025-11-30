<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    // set_flash("error", "Unauthorized access. Please login as admin.");
    header("Location: ../login.php");
    exit;
}

$current_page = basename($_SERVER['PHP_SELF']);

$pages = [
    'index.php' => 'Dashboard',
    'admin-users.php' => 'Users',
    'manage-products.php' => 'Products',
    'admin-orders.php' => 'Product Orders',
    'admin-sms-verification.php' => 'SMS Services',
    'admin-sms-orders.php' => 'SMS Orders',
    'admin-transactions.php' => 'Transactions',
    'referral-settings.php' => 'Referral Settings',
    'add-slider.php' => 'Slider',
    'add-about-us.php' => 'About Us',
    'add-faq.php' => 'FAQs',
    'help-center.php' => 'Help Center',
    'add-privacy.php' => 'Privacy Policy',
    'add-terms.php' => 'Terms',
    'payment-settings.php' => 'payment',
    'add-cookie-policy.php' => 'Cookie Policy',
    'site-settings.php' => 'Site Settings',
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pages[$current_page]) ? $pages[$current_page] : 'Admin' ?> - Acctverse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="shortcut icon" href="assets/image/a.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>
<body class="bg-gray-50 font-sans">
    <nav class="bg-blue-900 shadow-lg" x-data="{ open: false }">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <a href="index.php" class="font-bold text-lg text-white">Acctverse Admin</a>
                </div>
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-4 flex-wrap">
                    <?php foreach ($pages as $file => $title): ?>
                        <a href="<?= $file ?>" class="<?= $current_page === $file ? 'text-orange-500 font-medium' : 'text-gray-300' ?> hover:text-orange-500 text-sm"><?= $title ?></a>
                    <?php endforeach; ?>
                    <a href="logout.php" class="bg-orange-500 text-white px-4 py-2 rounded font-medium hover:bg-orange-600 text-sm">Logout</a>
                </div>
                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button @click="open = !open" class="text-white hover:text-orange-500 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            <path x-show="open" style="display: none;" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div x-show="open" style="display: none;" class="md:hidden" @click.away="open = false">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <?php foreach ($pages as $file => $title): ?>
                    <a href="<?= $file ?>" class="block px-3 py-2 rounded-md text-base font-medium <?= $current_page === $file ? 'bg-orange-500 text-white' : 'text-gray-300 hover:bg-blue-800 hover:text-white' ?>"><?= $title ?></a>
                <?php endforeach; ?>
                <a href="logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:bg-blue-800 hover:text-white">Logout</a>
            </div>
        </div>
    </nav>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <main class="max-w-7xl mx-auto px-4 py-8">