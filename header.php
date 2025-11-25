
<?php
// Fetch the site logo from settings
$logo_path = 'assets/image/acctverse.png'; // Default logo
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_name = 'site_logo'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result && !empty($result['setting_value'])) {
        $logo_path = 'assets/image/' . htmlspecialchars($result['setting_value']);
    }
} catch (Exception $e) {
    // On error, the default logo will be used. You could log the error here.
}
?>
<div class="header_one w-full bg-white justify-between flex items-center border-b border-gray-300 pl-[80px] pr-[80px] h-[80px]">
    <!--site-logo-->
    <img src="<?= $logo_path ?>" alt="Site Logo" class="w-[150px]">
    <!-- Search Bar (hidden on mobile, visible on desktop) -->
    <div class="hidden md:flex items-center w-full max-w-md rounded-full border border-gray-300 overflow-hidden">
      <input 
        type="text" 
        placeholder="Search..." 
        class="flex-grow px-4 py-2 text-gray-700 focus:outline-none"
      />
      <button class="bg-red-500 hover:bg-green-500 text-white px-6 py-3 rounded-r-full flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z" />
        </svg>
      </button>
    </div>
    <!--login-and-register-->
    <div class="login-and-register flex gap-3 <?php echo $isLoggedIn ? 'hidden md:hidden' : 'hidden md:flex'; ?>">
      <a href="login" class="bg-red-500 hover:bg-green-600 rounded-[5px] text-white font-bold py-[14px] px-[24px]">
        Login
      </a>
      <a href="register" class="bg-green-500 hover:bg-red-600 rounded-[5px] text-white font-bold py-[14px] px-[24px]">
        Register
      </a>
    </div>

    <!--dashboard-buttons-->
    <?php if($isLoggedIn): ?>
    <div class="dashboard-buttons hidden md:flex gap-3">
      <a href="./user/index" class="bg-blue-500 hover:bg-blue-600 rounded-[5px] text-white font-bold py-[14px] px-[24px]">
        Dashboard
      </a>
      <a href="./user/profile" class="bg-purple-500 hover:bg-purple-600 rounded-[5px] text-white font-bold py-[14px] px-[24px]">
        Profile
      </a>
      <a href="./user/logout" class="bg-red-500 hover:bg-red-600 rounded-[5px] text-white font-bold py-[14px] px-[24px]">
        Logout
      </a>
    </div>
    <?php endif; ?>

    <!-- Mobile Nav -->
    <div class="relative md:hidden">
      <button id="mobileNavToggle" class="nav_image block" aria-expanded="false" aria-controls="mobileNav">
        <img src="assets/image/menu-navigation-grid-1528-svgrepo-com.svg" 
            alt="menu icon" 
            class="w-[40px]" />
      </button>

      <!-- Mobile dropdown (hidden by default) -->
      <div id="mobileNav" class="mobile-nav hidden absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50" style="z-index:9999;">
        <div class="py-2 px-3">
          <div class="mt-2 pt-2">
            <a href="index" class="block px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">Home</a>
            <a href="about-us" class="block border-t border-gray-100 px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">About</a>
            <a href="<?php echo $isLoggedIn ? './user/order-history' : 'login'; ?>" class="block border-t border-gray-100 px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">Orders</a>
            <a href="<?php echo $isLoggedIn ? './user/product' : 'login'; ?>" class="block border-t border-gray-100 px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">Product</a>
            <a href="<?php echo $isLoggedIn ? './user/sms-verification' : 'login'; ?>" class="block border-t border-gray-100 px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">SMS Verification</a>
            <a href="<?php echo $isLoggedIn ? '' : 'login'; ?>" class="block border-t border-gray-100 px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">Add Fund</a>
            <a href="coming-soon" class="block border-t border-gray-100 px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">Boost Accounts</a>
            <a href="" class="block border-t border-gray-100 px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">Contact</a>
          </div>
          <!-- auth buttons -->
          <div class="mb-2 border-t border-gray-100">
            <?php if(!$isLoggedIn): ?>
            <a href="login" class="block w-full text-center px-3 py-2 mb-2 bg-red-500 hover:bg-red-600 text-white rounded font-semibold">Login</a>
            <a href="register" class="block w-full text-center px-3 py-2 bg-orange-500 hover:bg-green-600 text-white rounded font-semibold">Register</a>
            <?php else: ?>
            <a href="./user/index" class="block w-full text-center px-3 py-2 mb-2 bg-blue-500 hover:bg-blue-600 text-white rounded font-semibold">Dashboard</a>
            <a href="./user/profile" class="block w-full text-center px-3 py-2 mb-2 bg-purple-500 hover:bg-purple-600 text-white rounded font-semibold">Profile</a>
            <a href="logout" class="block w-full text-center px-3 py-2 bg-red-500 hover:bg-red-600 text-white rounded font-semibold">Logout</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
</div>


<header class="header_two shadow-md flex justify-between items-center sticky top-0 bg-white z-50 pl-[80px] pr-[80px]">
  <!--category button-->
  <div class="relative inline-block">
    <button id="categoryToggle" class="cart_two font-bold text-white rounded-[5px] flex items-center bg-red-500 pr-[16px] pl-[16px] py-[5px] gap-[5px]" aria-expanded="false" aria-controls="categoryMenu">
      <img src="assets/image/icons8_circled_menu_24px_1.png" class="w-[100%] h-[30px]"/> 
      <p class="cart_two_text">Category</p>
    </button>

    <!-- Category dropdown -->
    <ul id="categoryMenu" class="dropdown--menu hidden absolute left-0 mt-2 w-56 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5" style="z-index:9999;">
      <li class="dropdown--menu__item"><a href="product.php?category=FACEBOOK" class="dropdown--menu__link block px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">FACEBOOK</a></li>

      <li class="dropdown--menu__item"><a href="product.php?category=INSTAGRAM" class="dropdown--menu__link block px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">INSTAGRAM</a></li>
      
      <li class="dropdown--menu__item"><a href="product.php?category=TIKTOK" class="dropdown--menu__link block px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">TIKTOK</a></li>
      <li class="dropdown--menu__item"><a href="product.php?category=SNAPCHAT" class="dropdown--menu__link block px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">SNAPCHAT</a></li>
      <li class="dropdown--menu__item"><a href="product.php?category=RADDIT" class="dropdown--menu__link block px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">RADDIT</a></li>
      <li class="dropdown--menu__item"><a href="product.php?category=LINKEDIN" class="dropdown--menu__link block px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">LINKEDIN</a></li>
      <li class="dropdown--menu__item"><a href="product.php?category=ENTERTAINMENT" class="dropdown--menu__link block px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">ENTERTAINMENT</a></li>
      <li class="dropdown--menu__item"><a href="product.php?category=TEXTING%20APPS" class="dropdown--menu__link block px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">TEXTING APPS</a></li>
      <li class="dropdown--menu__item"><a href="product.php?category=VPN/PROXY" class="dropdown--menu__link block px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">VPN/PROXY</a></li>
      <li class="dropdown--menu__item"><a href="product.php?category=ACCTVERSE%20GIVEAWAY" class="dropdown--menu__link block px-4 py-2 text-sm text-[#001957] hover:bg-gray-100">ACCTVERSE GIVEAWAY...</a></li>
    </ul>
  </div>

   <!-- Search Bar (hidden on desktop, visible on mobile) -->
<div class="flex md:hidden items-center w-full max-w-md mx-auto px-2">
  <div class="flex items-center w-full rounded-full border border-gray-300 overflow-hidden shadow-sm">
    <input 
      type="text" 
      placeholder="Search..." 
      class="flex-grow px-4 py-2 text-gray-700 text-sm sm:text-base focus:outline-none font-[system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue',Arial,'Noto Sans','Liberation Sans',sans-serif,'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol','Noto Color Emoji']"
    />
    <button class="bg-red-500 hover:bg-red-500 text-white px-4 sm:px-6 py-3 sm:py-3 rounded-r-full flex items-center justify-center transition-colors duration-200">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10 18a8 8 0 100-16 8 8 0 000 16z" />
      </svg>
    </button>
  </div>
</div>


  <!-- main Menu-->
    <div class="hidden md:flex text-[#001957] gap-[20px] font-bold justify-center items-center py-4 nav_menu">
      <a href="index">Home</a>
      <a href="about-us">About</a>
      <a href="./user/order-history">Orders</a>
       <a href="./user/product">Product</a>
      <a href="./user/sms-verification">SMS Verification</a>
      <a href="">Add Fund</a>
      <a href="coming-sson">Boost Accounts</a>
      <a href="">Contact</a>
    </div>
</header>
<script>
// Mobile nav toggle: open/close and close on outside click
document.addEventListener('DOMContentLoaded', function(){
  const toggle = document.getElementById('mobileNavToggle');
  const menu = document.getElementById('mobileNav');
  if (!toggle || !menu) return;

  function openMenu(){ menu.classList.remove('hidden'); toggle.setAttribute('aria-expanded','true'); }
  function closeMenu(){ menu.classList.add('hidden'); toggle.setAttribute('aria-expanded','false'); }

  toggle.addEventListener('click', function(e){
    e.stopPropagation();
    if(menu.classList.contains('hidden')) openMenu(); else closeMenu();
  });

  // close when clicking outside
  document.addEventListener('click', function(e){
    if(!menu.classList.contains('hidden') && !menu.contains(e.target) && e.target !== toggle){
      closeMenu();
    }
  });

  // close on escape
  document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeMenu(); });
});
</script>
<script>
// Category dropdown toggle
document.addEventListener('DOMContentLoaded', function(){
  const catToggle = document.getElementById('categoryToggle');
  const catMenu = document.getElementById('categoryMenu');
  if(!catToggle || !catMenu) return;

  function openCat(){ catMenu.classList.remove('hidden'); catToggle.setAttribute('aria-expanded','true'); }
  function closeCat(){ catMenu.classList.add('hidden'); catToggle.setAttribute('aria-expanded','false'); }

  catToggle.addEventListener('click', function(e){ e.stopPropagation(); if(catMenu.classList.contains('hidden')) openCat(); else closeCat(); });

  document.addEventListener('click', function(e){ if(!catMenu.classList.contains('hidden') && !catMenu.contains(e.target) && e.target !== catToggle) closeCat(); });
  document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeCat(); });
});
</script>