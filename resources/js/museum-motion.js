import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);
const media = gsap.matchMedia();

media.add('(prefers-reduced-motion: no-preference)', () => {
    const heading = document.querySelector('.opening-title');
    if (!heading) return;

    if (window.scrollY < 80) {
        gsap.from(heading.children, {
            y: 12, opacity: 0.65, duration: 0.55, stagger: 0.065,
            ease: 'power2.out', clearProps: 'transform,opacity',
        });
    }
    gsap.utils.toArray('[data-reveal]').forEach((image) => {
        gsap.fromTo(image, { scale: 1.055 }, {
            scale: 1, ease: 'none',
            scrollTrigger: { trigger: image.parentElement, start: 'top bottom', end: 'bottom 35%', scrub: true },
        });
    });
});
