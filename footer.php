<!--footer-->
<div class="footer_container pt-[16px] pb-[16px] pl-[30px] pr-[30px] text-white w-ful bg-red-900 flex justify-between">
  <div class="footer_section_one">
   <p>© Copyright 2025 . All rights reserved.</p>
  </div>
  <div class="footer_section_two flex gap-[30px]">
    <a href="">Privacy Policy</a>
    <a href="">Rules</a>
    <a href="">Terms and conditions</a>
    <a href="">FAQs</a>
  </div>
</div>

<!--end footer-->

<!-- Floating Dark/Light Mode Toggle -->
  <button 
    id="theme-toggle"
    class="fixed bottom-6 right-6 bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-yellow-300
           p-4 rounded-full shadow-lg hover:scale-110 transition-transform duration-300"
  >
    <svg id="sun-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M12 3v1m0 16v1m8.66-9H21m-18 0H3m15.36 6.36l.7.7m-12.02-.7l-.7.7m0-12.02l.7-.7m12.02.7l.7-.7M12 8a4 4 0 100 8 4 4 0 000-8z"/>
    </svg>
    <svg id="moon-icon" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
        d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z"/>
    </svg>
  </button>

  <script>
    const html = document.documentElement;
    const toggle = document.getElementById('theme-toggle');

    if (localStorage.getItem('theme') === 'dark') {
      html.classList.add('dark');
    }

    toggle.addEventListener('click', () => {
      html.classList.toggle('dark');
      localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
    });
  </script>
</body>