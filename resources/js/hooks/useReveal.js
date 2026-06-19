import { useEffect } from 'react';

/**
 * Reveal-on-scroll. Adds `.in` to every `.reveal` element as it enters
 * the viewport; CSS handles the transition. Honors reduced-motion.
 */
export function useReveal() {
    useEffect(() => {
        const els = Array.from(document.querySelectorAll('.reveal'));
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (reduce) {
            els.forEach((el) => el.classList.add('in'));
            return;
        }

        const io = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('in');
                        io.unobserve(entry.target);
                    }
                });
            },
            { threshold: 0.12, rootMargin: '0px 0px -8% 0px' },
        );

        els.forEach((el) => io.observe(el));
        return () => io.disconnect();
    }, []);
}
