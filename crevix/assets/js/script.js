// ─── GLOBAL SCRIPT ───

// Video thumbnails: set time on preload
document.addEventListener('DOMContentLoaded', function () {
    // Auto-seek videos used as thumbnails to 2s for a good frame
    document.querySelectorAll('.media-card-thumb video, .related-thumb video').forEach(v => {
        v.addEventListener('loadedmetadata', function () {
            v.currentTime = Math.min(2, v.duration * 0.25);
        });
    });

    // Search bar live redirect
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                window.location.href = 'explore.php?q=' + encodeURIComponent(this.value);
            }
        });
    }

    // Smooth scroll to top on logo click
    document.querySelectorAll('.logo a').forEach(el => {
        el.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    });

    // Fade-in cards on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.media-card, .stat-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        observer.observe(card);
    });
});
