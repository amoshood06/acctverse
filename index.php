<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-20px); }
    }
    .animate-float { animation: float 4s ease-in-out infinite; }
    
    @keyframes moveStars {
      from { transform: translateY(-100vh); }
      to { transform: translateY(100vh); }
    }
    .star {
      position: absolute;
      background: #e2e8f0;
      border-radius: 50%;
      opacity: 0.5;
      animation: moveStars linear infinite;
    }
    .fade-in { animation: fadeIn 0.5s ease-out forwards; }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body class="bg-white text-gray-800 font-sans antialiased overflow-hidden relative">

  <div id="star-container" class="absolute inset-0 pointer-events-none z-0"></div>

  <div class="min-h-screen flex flex-col md:flex-row items-center justify-center p-6 md:p-20 max-w-7xl mx-auto relative z-10">
    
    <div class="w-full md:w-1/2 flex flex-col items-center md:items-start text-center md:text-left">
      <h2 class="text-gray-400 text-2xl md:text-3xl font-medium mb-2 tracking-tight">Lift off in</h2>
      
      <div id="countdown" class="text-[100px] sm:text-[150px] md:text-[220px] leading-none font-bold text-[#333] tracking-tighter mb-8 drop-shadow-sm">
        01:07
      </div>

      <div id="form-container" class="w-full max-w-md bg-white/80 backdrop-blur-sm p-2 rounded-xl h-32 flex flex-col justify-center">
        <div id="subscription-ui">
          <p class="text-gray-500 mb-4 font-medium px-2">Ready for the journey? Join the waitlist.</p>
          <form class="flex flex-col sm:flex-row gap-2" onsubmit="handleSubscribe(event)">
            <input type="email" id="email-input" placeholder="notify@acctverse.com" required
                   class="flex-1 px-4 py-3 rounded-lg bg-gray-100 border-2 border-transparent focus:border-indigo-300 focus:bg-white transition duration-200 outline-none">
            <button type="submit" 
                    class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg transition duration-200 shadow-lg active:scale-95">
              Notify Me
            </button>
          </form>
        </div>

        <div id="success-message" class="hidden flex flex-col items-center md:items-start fade-in">
          <div class="flex items-center text-green-600 font-bold text-xl">
            <svg class="w-8 h-8 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
            Transmission Received!
          </div>
          <p class="text-gray-500 mt-1 px-1">You're on the list for ignition.</p>
        </div>
      </div>
    </div>

    <div class="w-full md:w-1/2 relative flex justify-center items-center mt-20 md:mt-0">
      <div class="relative flex flex-col items-center animate-float">
        <div class="flex items-end space-x-2">
          <div class="w-12 h-40 bg-gray-200 rounded-t-full relative"><div class="absolute -bottom-6 left-1/2 -translate-x-1/2 w-4 h-12 bg-gradient-to-b from-orange-500 to-transparent rounded-b-full animate-pulse"></div></div>
          <div class="w-24 h-64 bg-gray-50 rounded-t-full relative shadow-inner">
            <div class="absolute top-12 left-1/2 -translate-x-1/2 w-10 h-6 bg-slate-900 rounded-md"></div>
            <div class="absolute -bottom-10 left-1/2 -translate-x-1/2 w-6 h-20 bg-gradient-to-b from-yellow-400 via-orange-500 to-transparent rounded-b-full animate-bounce"></div>
          </div>
          <div class="w-12 h-40 bg-gray-200 rounded-t-full relative"><div class="absolute -bottom-6 left-1/2 -translate-x-1/2 w-4 h-12 bg-gradient-to-b from-orange-500 to-transparent rounded-b-full animate-pulse delay-150"></div></div>
        </div>
        <div class="flex -mt-12 space-x-[-40px] z-30 opacity-90">
          <div class="w-24 h-24 bg-gray-100 rounded-full"></div>
          <div class="w-44 h-44 bg-gray-100 rounded-full shadow-lg"></div>
          <div class="w-24 h-24 bg-gray-100 rounded-full"></div>
        </div>
      </div>
    </div>
  </div>

<script>
  // Countdown Timer
  let totalSeconds = 67;
  const display = document.getElementById('countdown');
  const timer = setInterval(() => {
    const min = Math.floor(totalSeconds / 60);
    const sec = totalSeconds % 60;
    display.textContent = `${min}:${sec.toString().padStart(2, '0')}`;
    if (totalSeconds-- <= 0) clearInterval(timer);
  }, 1000);

  // Background Stars
  const container = document.getElementById('star-container');
  for (let i = 0; i < 60; i++) {
    const star = document.createElement('div');
    star.className = 'star';
    const size = Math.random() * 3 + 'px';
    star.style.width = size; star.style.height = size;
    star.style.left = Math.random() * 100 + 'vw';
    star.style.top = Math.random() * 100 + 'vh';
    star.style.animationDuration = (Math.random() * 15 + 10) + 's';
    container.appendChild(star);
  }

  // Handle Form Submission
  function handleSubscribe(e) {
    e.preventDefault();
    document.getElementById('subscription-ui').classList.add('hidden');
    document.getElementById('success-message').classList.remove('hidden');
  }
</script>
</body>
</html>