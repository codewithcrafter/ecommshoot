document.addEventListener('DOMContentLoaded', function () {
    const cardSelectors = [
        '.luxury-card',
        '.service-editorial-card',
        '.editorial-pillar-card',
        '.process-step-item',
        '.industry-box',
        '.testimonial-box',
        '.about-tool-card',
        '.services-style-card',
        '.services-enquiry-card',
        '.home-hero-enquiry-card',
        '.contact-info-panel',
        '.contact-form-card',
        '.blog-card',
        '.blog-featured-article',
        '.editorial-consultation-card',
        '.benefit-card',
        '.feature-box',
        '.portfolio-card',
        '.industry-card',
        '.process-card',
        '.service-card'
    ].join(', ');

    document.querySelectorAll(cardSelectors).forEach(function (card) {
        card.addEventListener('mousemove', function (e) {
            const rect = card.getBoundingClientRect();
            card.style.setProperty('--mouse-x', (e.clientX - rect.left) + 'px');
            card.style.setProperty('--mouse-y', (e.clientY - rect.top) + 'px');
        });
    });

    const reveals = document.querySelectorAll('.studio-reveal, .reveal-on-scroll');
    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry, idx) {
            if (entry.isIntersecting) {
                setTimeout(function () {
                    entry.target.classList.add('is-visible');
                    entry.target.classList.add('is-revealed');
                }, (idx % 4) * 75);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    reveals.forEach(function (el) { observer.observe(el); });
});
