import { useEffect, useState, useRef } from 'react';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { WhatsAppIcon } from './icons';

export default function Hero() {
    const [useVideo, setUseVideo] = useState(false);
    const mediaRef = useRef(null);
    const innerRef = useRef(null);

    useEffect(() => {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        setUseVideo(!reduce); // looping muted video on all devices (poster only for reduced-motion)
    }, []);

    // Bulletproof inline muted autoplay. React doesn't reliably set the `muted` DOM
    // property (iOS then blocks autoplay), and mobile policies may defer the first play
    // until a user gesture — so we set muted imperatively and retry on every signal.
    useEffect(() => {
        const v = mediaRef.current;
        if (!useVideo || !v || v.tagName !== 'VIDEO') return;

        v.muted = true;
        v.defaultMuted = true;
        v.setAttribute('muted', '');

        let cancelled = false;
        const attempt = () => {
            if (cancelled || !v.paused) return;
            const p = v.play();
            if (p && p.catch) p.catch(() => {});
        };

        attempt();
        v.addEventListener('canplay', attempt);
        v.addEventListener('loadeddata', attempt);
        document.addEventListener('visibilitychange', attempt);
        // strict mobile policies: the first touch / scroll unlocks playback
        window.addEventListener('touchstart', attempt, { passive: true });
        window.addEventListener('scroll', attempt, { passive: true });

        const io = new IntersectionObserver(
            (entries) => entries.forEach((e) => e.isIntersecting && attempt()),
            { threshold: 0.1 },
        );
        io.observe(v);

        return () => {
            cancelled = true;
            v.removeEventListener('canplay', attempt);
            v.removeEventListener('loadeddata', attempt);
            document.removeEventListener('visibilitychange', attempt);
            window.removeEventListener('touchstart', attempt);
            window.removeEventListener('scroll', attempt);
            io.disconnect();
        };
    }, [useVideo]);

    // Scroll parallax — DESKTOP ONLY. Scrubbing a transform on a full-screen <video>
    // re-rasterizes it every frame, which is far too heavy for mobile GPUs.
    useEffect(() => {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const desktop = window.matchMedia('(min-width: 921px)').matches;
        if (reduce || !desktop || !mediaRef.current) return;

        const ctx = gsap.context(() => {
            gsap.fromTo(
                mediaRef.current,
                { scale: 1.08 },
                {
                    scale: 1.2,
                    ease: 'none',
                    scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true },
                },
            );
            gsap.to(innerRef.current, {
                yPercent: 10,
                opacity: 0.6,
                ease: 'none',
                scrollTrigger: { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true },
            });
        });
        return () => ctx.revert();
    }, [useVideo]);

    return (
        <header className="hero" id="top">
            <div className="hero-media" aria-hidden="true">
                {useVideo ? (
                    <video
                        ref={mediaRef}
                        className="hero-video"
                        autoPlay
                        muted
                        loop
                        playsInline
                        preload="auto"
                        poster="/assets/img/hero-3.jpg"
                    >
                        <source src="/assets/video/hero.mp4" type="video/mp4" />
                    </video>
                ) : (
                    <img ref={mediaRef} src="/assets/img/hero-3.jpg" alt="" loading="eager" />
                )}
                <div className="hero-scrim" />
            </div>

            <div className="hero-inner wrap" ref={innerRef}>
                <span className="eyebrow">Luxury Nail &amp; Beauty Lounge · Ampang</span>
                <h1 className="hero-title">
                    <span className="line"><i>Art of</i></span>
                    <span className="line"><i><em>Effortless</em> Beauty</i></span>
                </h1>
                <p className="lead">
                    Beauty that feels as good as it looks. Step into Angel Krown — where expert hands and
                    quiet luxury turn self-care into an art form.
                </p>
                <div className="cta-row">
                    <a href="#book" className="btn hero-book">
                        <WhatsAppIcon /> Book on WhatsApp
                    </a>
                    <a href="#services" className="btn ghost hero-ghost">Explore Services</a>
                </div>
            </div>

            <div className="scroll-hint">
                <span className="line" />
                Scroll
            </div>
        </header>
    );
}
