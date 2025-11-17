<?php
require_once "flash.php";
session_start();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Registration</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script>

<!-- Toastify -->
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

<div class="w-full max-w-lg bg-white p-8 rounded-lg shadow">
    <h2 class="text-2xl font-bold text-center mb-6">Create Account</h2>

    <form action="./function/register_function.php" method="POST" class="space-y-4">

        <!-- Full Name -->
        <div>
            <label class="block text-sm font-medium">Full Name</label>
            <input name="full_name" type="text" required class="w-full px-3 py-2 border rounded">
        </div>

        <!-- Username -->
        <div>
            <label class="block text-sm font-medium">Username</label>
            <input name="username" type="text" required class="w-full px-3 py-2 border rounded">
        </div>

        <!-- Email -->
        <div>
            <label class="block text-sm font-medium">Email Address</label>
            <input name="email" type="email" required class="w-full px-3 py-2 border rounded">
        </div>

        <!-- Country Dropdown -->
        <div>
            <label class="block text-sm font-medium">Select Country</label>
            <select name="country" id="countrySelect" required class="w-full border px-3 py-2 rounded">
                <option value="">-- Select Country --</option>
                <option value="Nigeria" data-dial="234">Nigeria (+234)</option>
                <option value="Ghana" data-dial="233">Ghana (+233)</option>
                <option value="Kenya" data-dial="254">Kenya (+254)</option>
                <option value="South Africa" data-dial="27">South Africa (+27)</option>
                <option value="USA" data-dial="1">USA (+1)</option>
                <option value="UK" data-dial="44">UK (+44)</option>
            </select>
        </div>

        <!-- Phone -->
        <div>
            <label class="block text-sm font-medium">Mobile Number</label>
            <div class="flex">
                <span id="mobileCode" class="px-4 py-2 bg-gray-200 border rounded-l">+</span>
                <input type="hidden" name="mobile_code" id="mobileCodeInput">
                <input type="text" name="mobile" required class="w-full px-3 py-2 border rounded-r">
            </div>
        </div>

        <!-- Password -->
        <div>
            <label class="block text-sm font-medium">Password</label>
            <input name="password" type="password" required class="w-full px-3 py-2 border rounded">
        </div>

        <!-- Confirm Password -->
        <div>
            <label class="block text-sm font-medium">Confirm Password</label>
            <input name="confirm_password" type="password" required class="w-full px-3 py-2 border rounded">
        </div>

        <!-- Submit Button -->
        <button class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700">
            Register
        </button>
    </form>

    <p class="text-center text-sm mt-4">
        Already have an account?
        <a href="login.php" class="text-blue-600">Login</a>
    </p>
</div>

<?php if ($flash): ?>
<script>
document.addEventListener('DOMContentLoaded', function(){
  Toastify({
    text: <?= json_encode($flash['message']) ?>,
    duration: 4000,
    gravity: "top",
    position: "right",
    close: true,
    backgroundColor: <?= json_encode($flash['type'] === 'success'
        ? "linear-gradient(to right, #00b09b, #96c93d)"
        : "linear-gradient(to right, #ff5f6d, #ffc371)"
    ) ?>
  }).showToast();
});
</script>
<?php endif; ?>

<script>
    (function(){
        function setMobileCode(){
            const select = document.getElementById('countrySelect');
            const codeSpan = document.getElementById('mobileCode');
            const codeInput = document.getElementById('mobileCodeInput');

            const opt = select.options[select.selectedIndex];
            const dial = opt.dataset.dial ? opt.dataset.dial : '';
            const display = dial ? "+" + dial : "+";

            codeSpan.textContent = display;
            codeInput.value = display;
        }

        document.addEventListener('DOMContentLoaded', function(){
            const select = document.getElementById('countrySelect');
            if (!select) return;

            setMobileCode();

            select.addEventListener('change', function(){
                setMobileCode();
            });
        });
    })();
</script>

</body>
</html>
