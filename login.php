<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-sm p-8 w-full max-w-md">
            <!-- Header -->
            <h1 class="text-4xl font-bold text-blue-900 text-center mb-2">Login</h1>
            <p class="text-center text-gray-600 mb-8">You are welcome back!</p>

            <form class="space-y-6">
                <!-- Username or Email Row -->
                <div>
                    <label class="block text-sm font-semibold text-blue-900 mb-2">
                        Username or Email <span class="text-red-500">*</span>
                    </label>
                    <input type="text" placeholder="" class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>

                <!-- Password Row -->
                <div>
                    <label class="block text-sm font-semibold text-blue-900 mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" placeholder="" class="w-full px-4 py-3 border border-gray-300 rounded focus:outline-none focus:border-blue-500">
                </div>

                <!-- Remember Me Checkbox and Forgot Password Link -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="remember" class="w-4 h-4 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                        <label for="remember" class="text-sm text-gray-600">Remember Me</label>
                    </div>
                    <a href="#" class="text-sm text-orange-500 font-semibold hover:underline">Forgot your password?</a>
                </div>

                <!-- Login Button -->
                <button type="submit" class="w-full bg-red-500 text-white font-bold py-3 rounded hover:bg-orange-600 transition duration-200">
                    Login
                </button>

                <!-- Register Link -->
                <p class="text-center text-gray-600">
                    Haven't an account?
                    <a href="#" class="text-red-500 font-semibold hover:underline">Register</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>
