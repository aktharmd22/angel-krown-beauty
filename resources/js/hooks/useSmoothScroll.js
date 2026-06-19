import { useEffect } from 'react';
import Lenis from 'lenis';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

/**
 * Lenis smooth scrolling, synced with GSAP ScrollTrigger.
 * Disabled when the user prefers reduced motion.
 */
export function useSmoothScroll() {
    useEffect(() => {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduce) return;

        const lenis = new Lenis({
            lerp: 0.09,
            smoothWheel: true,
            wheelMultiplier: 1,
            touchMultiplier: 1.4,
        });

        lenis.on('scroll', ScrollTrigger.update);
        window.lenis = lenis;

        const raf = (time) => lenis.raf(time * 1000);
        gsap.ticker.add(raf);
        gsap.ticker.lagSmoothing(0);

        // Smooth-scroll for in-page anchor links
        const onClick = (e) => {
            const a = e.target.closest('a[href^="#"]');
            if (!a) return;
            const id = a.getAttribute('href');
            if (id.length < 2) return;
            const target = document.querySelector(id);
            if (!target) return;
            e.preventDefault();
            lenis.scrollTo(target, { offset: -10, duration: 1.2 });
        };
        document.addEventListener('click', onClick);

        return () => {
            document.removeEventListener('click', onClick);
            gsap.ticker.remove(raf);
            delete window.lenis;
            lenis.destroy();
            ScrollTrigger.getAll().forEach((t) => t.kill());
        };
    }, []);
}
