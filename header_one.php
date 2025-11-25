<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']) || isset($_COOKIE['user_token']);
?>

<div class="header_one w-full bg-white justify-between flex items-center border-b border-gray-300 pl-[80px] pr-[80px] h-[80px]">
    <!--site-logo-->
    <img src="assets/image/acctverse.png" alt="" class="w-[150px]">

    <!-- Search Bar (hidden on mobile, visible on desktop) -->
    <div class="hidden md:flex items-center w-full max-w-md rounded-full border border-gray-300 overflow-hidden">
      <input type="text" placeholder="Search..." class="flex-grow px-4 py-2 text-gray-700 focus:outline-none"/>
      <button class="bg-red-500 hover:bg-green-500 text-white px-6 py-3 rounded-r-full flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z" />
        </svg>
      </button>
    </div>

    <!-- Auth Buttons for Desktop -->
    <div class="flex gap-3 md:flex">
        <?php if($isLoggedIn): ?>
            <a href="./user/index.php" class="bg-blue-500 hover:bg-blue-600 rounded-[5px] text-white font-bold py-[14px] px-[24px]">Dashboard</a>
            <a href="./user/profile.php" class="bg-purple-500 hover:bg-purple-600 rounded-[5px] text-white font-bold py-[14px] px-[24px]">Profile</a>
            <a href="./user/logout.php" class="bg-red-500 hover:bg-red-600 rounded-[5px] text-white font-bold py-[14px] px-[24px]">Logout</a>
        <?php else: ?>
            <a href="login.php" class="bg-red-500 hover:bg-green-600 rounded-[5px] text-white font-bold py-[14px] px-[24px]">Login</a>
            <a href="register.php" class="bg-green-500 hover:bg-red-600 rounded-[5px] text-white font-bold py-[14px] px-[24px]">Register</a>
        <?php endif; ?>
    </div>

    <!-- Mobile Nav Toggle -->
    <div class="relative md:hidden">
      <button id="mobileNavToggle" class="nav_image block" aria-expanded="false" aria-controls="mobileNav">
        <img src="assets/image/menu-navigation-grid-1528-svgrepo-com.svg" alt="menu icon" class="w-[40px]" />
      </button>

      <!-- Mobile dropdown -->
      <div id="mobileNav" class="mobile-nav hidden absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50" style="z-index:9999;">
        <div class="py-2 px-3">
          <div class="mt-2 pt-2">
            <a href="index" class="block px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">Home</a>
            <a href="about-us" class="block border-t border-gray-100 px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">About</a>
            <a href="./user/order-history" class="block border-t border-gray-100 px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">Orders</a>
            <a href="./user/product" class="block border-t border-gray-100 px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">Product</a>
            <a href="./user/sms-verification" class="block border-t border-gray-100 px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">SMS Verification</a>
            <a href="#" class="block border-t border-gray-100 px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">Add Fund</a>
            <a href="coming-soon" class="block border-t border-gray-100 px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">Boost Accounts</a>
            <a href="#" class="block border-t border-gray-100 px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">Contact</a>
          </div>
          <!-- Auth buttons for mobile -->
          <div class="mb-2 border-t border-gray-100 mt-2">
            <?php if(!$isLoggedIn): ?>
              <a href="login.php" class="block w-full text-center px-3 py-2 mb-2 bg-red-500 hover:bg-red-600 text-white rounded font-semibold">Login</a>
              <a href="register.php" class="block w-full text-center px-3 py-2 bg-green-500 hover:bg-green-600 text-white rounded font-semibold">Register</a>
            <?php else: ?>
              <a href="./user/index.php" class="block w-full text-center px-3 py-2 mb-2 bg-blue-500 hover:bg-blue-600 text-white rounded font-semibold">Dashboard</a>
              <a href="./user/profile.php" class="block w-full text-center px-3 py-2 mb-2 bg-purple-500 hover:bg-purple-600 text-white rounded font-semibold">Profile</a>
              <a href="logout.php" class="block w-full text-center px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded font-semibold">Logout</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
</div>
