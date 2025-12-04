<?php
$current_page = basename($_SERVER['PHP_SELF']);

$pages = [
    'index.php' => 'Dashboard',
    'products.php' => 'Products',
    'order-history.php' => 'Orders',
    'sms-verification.php' => 'SMS Services',
    'referral.php' => 'Refer & Earn',
    'transactions.php' => 'Transactions',
    'profile.php' => 'Profile',
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pages[$current_page]) ? $pages[$current_page] : 'User' ?> - Acctverse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link rel="shortcut icon" href="assets/image/a.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>
<body class="bg-gray-50" x-data="{ open: false }">
    <nav class="bg-white shadow-sm sticky top-0 z-20" @click.away="open = false">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <a href="index.php" class="font-bold text-lg text-blue-900">
                        <img src="assets/image/acctverse.png" alt="Acctverse" class="w-[150px]">
                    </a>
                </div>
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-6 flex-wrap">
                    <?php foreach ($pages as $file => $title): ?>
                        <a href="<?= $file ?>" class="<?= $current_page === $file ? 'text-orange-500 font-medium' : 'text-gray-600' ?> hover:text-orange-500 text-sm"><?= $title ?></a>
                    <?php endforeach; ?>
                    <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded font-medium hover:bg-red-600 text-sm">Logout</a>
                </div>
                <!-- Mobile Menu Button -->
                <div class="md:hidden">
                    <button @click="open = !open" class="text-gray-600 hover:text-orange-500 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            <path x-show="open" style="display: none;" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div x-show="open" style="display: none;" class="md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <?php foreach ($pages as $file => $title): ?>
                    <a href="<?= $file ?>" class="block px-3 py-2 rounded-md text-base font-medium <?= $current_page === $file ? 'bg-orange-500 text-white' : 'text-gray-600 hover:bg-gray-100' ?>"><?= $title ?></a>
                <?php endforeach; ?>
                <a href="logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-red-500 hover:bg-gray-100">Logout</a>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-4 py-8">