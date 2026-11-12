import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-carousel]').forEach((carousel) => {
        const track = carousel.querySelector('[data-carousel-track]');
        const slides = Array.from(carousel.querySelectorAll('[data-carousel-slide]'));
        const dots = Array.from(carousel.querySelectorAll('[data-carousel-dot]'));
        const previous = carousel.querySelector('[data-carousel-prev]');
        const next = carousel.querySelector('[data-carousel-next]');

        if (!track || slides.length < 2) return;

        let activeIndex = 0;
        let intervalId = null;
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const showSlide = (index) => {
            activeIndex = (index + slides.length) % slides.length;
            track.style.transform = `translateX(-${activeIndex * 100}%)`;

            slides.forEach((slide, slideIndex) => {
                slide.setAttribute('aria-hidden', slideIndex === activeIndex ? 'false' : 'true');
            });

            dots.forEach((dot, dotIndex) => {
                const active = dotIndex === activeIndex;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-current', active ? 'true' : 'false');
            });
        };

        const stopAutoplay = () => {
            if (intervalId) window.clearInterval(intervalId);
            intervalId = null;
        };

        const startAutoplay = () => {
            stopAutoplay();
            if (!reducedMotion && !document.hidden) {
                intervalId = window.setInterval(() => showSlide(activeIndex + 1), 5000);
            }
        };

        previous?.addEventListener('click', () => {
            showSlide(activeIndex - 1);
            startAutoplay();
        });

        next?.addEventListener('click', () => {
            showSlide(activeIndex + 1);
            startAutoplay();
        });

        dots.forEach((dot) => {
            dot.addEventListener('click', () => {
                showSlide(Number(dot.dataset.carouselDot));
                startAutoplay();
            });
        });

        carousel.addEventListener('mouseenter', stopAutoplay);
        carousel.addEventListener('mouseleave', startAutoplay);
        carousel.addEventListener('focusin', stopAutoplay);
        carousel.addEventListener('focusout', (event) => {
            if (!carousel.contains(event.relatedTarget)) startAutoplay();
        });

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) stopAutoplay();
            else startAutoplay();
        });

        showSlide(0);
        startAutoplay();
    });
});

import './scan';