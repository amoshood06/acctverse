<?php
require_once __DIR__ . '/../db/db.php'; // Assumes db.php is in the root db folder

// Fetch active gift sliders from the database
$stmt = $pdo->query("SELECT image_url, title, description, link FROM gift_sliders WHERE status = 'active' ORDER BY order_num ASC, created_at DESC");
$slides_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="slider-container relative w-full">
	<div class="slider-viewport sliders  overflow-hidden m-[50px]">
		<div class="slider-track flex">
			<?php if (!empty($slides_data)): ?>
				<?php foreach ($slides_data as $slide): ?>
					<div class="slide flex-shrink-0 w-full">
						<?php if (!empty($slide['link'])): ?>
							<a href="<?= htmlspecialchars($slide['link']) ?>">
						<?php endif; ?>
						<img src="<?= htmlspecialchars('../' . $slide['image_url']) ?>" alt="<?= htmlspecialchars($slide['title'] ?? 'Gift Slider Image') ?>" class="w-full object-cover"/>
						<?php if (!empty($slide['title']) || !empty($slide['description'])): ?>
							<div class="slider-caption absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white p-4">
								<?php if (!empty($slide['title'])): ?>
									<h3 class="text-lg font-bold"><?= htmlspecialchars($slide['title']) ?></h3>
								<?php endif; ?>
								<?php if (!empty($slide['description'])): ?>
									<p class="text-sm"><?= htmlspecialchars($slide['description']) ?></p>
								<?php endif; ?>
							</div>
						<?php endif; ?>
						<?php if (!empty($slide['link'])): ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			<?php else: ?>
				<div class="slide flex-shrink-0 w-full"><img src="../assets/image/sliders_1.png" alt="Default slider image" class="w-full object-cover"/></div>
			<?php endif; ?>
		</div>
	</div>

	<!-- controls -->
	<button class="slider-prev absolute left-3 top-1/2 -translate-y-1/2 bg-white/90 p-2 rounded shadow" aria-label="Previous">‹</button>
	<button class="slider-next absolute right-3 top-1/2 -translate-y-1/2 bg-white/90 p-2 rounded shadow" aria-label="Next">›</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	const container = document.querySelector('.slider-container');
	if (!container) return;

	const viewport = container.querySelector('.slider-viewport');
	const track = container.querySelector('.slider-track');
	const slides = Array.from(container.querySelectorAll('.slide'));
	const btnNext = container.querySelector('.slider-next');
	const btnPrev = container.querySelector('.slider-prev');

	let index = 0;
	const intervalMs = 3000; // auto-advance interval
	let timer = null;

	// apply widths so each slide equals the viewport width
	function applyWidths() {
		const width = viewport.clientWidth;
		slides.forEach(slide => { slide.style.width = width + 'px'; });
		// update position after resizing
		update();
	}

	function update() {
		const width = viewport.clientWidth;
		// smooth transition
		track.style.transition = 'transform 600ms ease';
		track.style.transform = `translateX(${-index * width}px)`;
	}

	function goTo(i) {
		index = (i + slides.length) % slides.length;
		update();
	}

	function next() { goTo(index + 1); }
	function prev() { goTo(index - 1); }

	function start() {
		stop();
		timer = setInterval(next, intervalMs);
	}
	function stop() { if (timer) { clearInterval(timer); timer = null; } }

	// controls
	if (btnNext) btnNext.addEventListener('click', () => { stop(); next(); start(); });
	if (btnPrev) btnPrev.addEventListener('click', () => { stop(); prev(); start(); });

	// pause on hover/focus
	container.addEventListener('mouseenter', stop);
	container.addEventListener('mouseleave', start);
	container.addEventListener('focusin', stop);
	container.addEventListener('focusout', start);

	// initialize
	applyWidths();
	window.addEventListener('resize', applyWidths);
	start();
});
</script>
