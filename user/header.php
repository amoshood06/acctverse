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
<body class="bg-gray-50">
    <nav class="bg-white shadow-sm sticky top-0 z-20">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <a href="index.php" class="font-bold text-lg text-blue-900">
                        <img src="../assets/image/acctverse.png" alt="Acctverse" class="w-[150px]">
                    </a>
                </div>
                <div class="hidden md:flex items-center gap-6 flex-wrap">
                    <?php foreach ($pages as $file => $title): ?>
                        <a href="<?= $file ?>" class="<?= $current_page === $file ? 'text-orange-500 font-medium' : 'text-gray-600' ?> hover:text-orange-500 text-sm"><?= $title ?></a>
                    <?php endforeach; ?>
                </div>
                <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded font-medium hover:bg-red-600">Logout</a>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-4 py-8">