import { useEffect, useState, useRef } from 'react';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { WhatsAppIcon } from './icons';

export default function Hero() {
    const [useVideo, setUseVideo] = useState(false);
    const [playing, setPlaying] = useState(false);
    const videoRef = useRef(null);
    const fallbackRef = useRef(null);
    const innerRef = useRef(null);

    useEffect(() => {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        setUseVideo(!reduce); // try the video everywhere; the animated poster covers blocked autoplay
    }, []);

    // Bulletproof inline muted autoplay. React doesn't reliably set the `muted` DOM
    // property (iOS then blocks autoplay), and strict mobile policies defer the first
    // play until a gesture — so we set muted imperatively and retry on every signal.
    // If it's still blocked (Low Power / Data Saver), the Ken-Burns poster shows instead.
    useEffect(() => {
        const v = videoRef.current;
        if (!useVideo || !v) return;

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

    // Scroll parallax — DESKTOP ONLY. Scrubbing a transform on a full-screen video
    // re-rasterizes it every frame, which is far too heavy for mobile GPUs.
    useEffect(() => {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const desktop = window.matchMedia('(min-width: 921px)').matches;
        const target = videoRef.current || fallbackRef.current;
        if (reduce || !desktop || !target) return;

        const ctx = gsap.context(() => {
            gsap.fromTo(
                target,
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
                {/* Always-on animated poster — guarantees motion even if video autoplay is blocked */}
                <img ref={fallbackRef} className="hero-fallback" src="/assets/img/hero-3.jpg" alt="" loading="eager" />

                {useVideo && (
                    <video
                        ref={videoRef}
                        className={`hero-video${playing ? ' playing' : ''}`}
                        autoPlay
                        muted
                        loop
                        playsInline
                        preload="auto"
                        onPlaying={() => setPlaying(true)}
                    >
                        <source src="/assets/video/hero.mp4" type="video/mp4" />
                    </video>
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
