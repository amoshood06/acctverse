<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Under Construction</title>
</head>
<body class="bg-slate-900 flex items-center justify-center min-h-screen px-4">

    <div class="max-w-2xl w-full text-center">
        <div class="relative flex justify-center mb-8">
            <div class="absolute animate-ping h-20 w-20 rounded-full bg-indigo-500 opacity-20"></div>
            <div class="relative rounded-full bg-indigo-600 p-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
            </div>
        </div>

        <h1 class="text-4xl md:text-6xl font-extrabold text-white tracking-tight mb-4">
            Something <span class="text-indigo-500">Great</span> is Brewing
        </h1>
        <p class="text-slate-400 text-lg md:text-xl mb-10 max-w-lg mx-auto">
            We're currently working hard to bring you a brand new experience. Stay tuned for our launch!
        </p>

        <div class="w-full bg-slate-800 rounded-full h-4 mb-12 overflow-hidden border border-slate-700">
            <div class="bg-indigo-500 h-full rounded-full animate-pulse" style="width: 75%"></div>
        </div>

        <form class="flex flex-col sm:flex-row gap-3 justify-center items-center">
            <input 
                type="email" 
                placeholder="Enter your email" 
                class="w-full sm:w-80 px-4 py-3 rounded-lg bg-slate-800 border border-slate-700 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all"
                required
            >
            <button 
                type="submit" 
                class="w-full sm:w-auto px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-lg shadow-indigo-500/30 transition-all"
            >
                Notify Me
            </button>
        </form>

        <div class="mt-12 flex justify-center space-x-6 text-slate-500">
            <a href="#" class="hover:text-indigo-400 transition-colors">Twitter</a>
            <a href="#" class="hover:text-indigo-400 transition-colors">Instagram</a>
            <a href="#" class="hover:text-indigo-400 transition-colors">LinkedIn</a>
        </div>
    </div>

</body>
</html>