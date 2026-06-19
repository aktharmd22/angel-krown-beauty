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
        const desktop = window.matchMedia('(min-width: 921px)').matches;
        // Video on desktop only. On mobile the heavy file can't autoplay reliably and
        // its decode causes scroll jank, so phones/tablets get a crisp poster image.
        setUseVideo(!reduce && desktop);
    }, []);

    // Force inline muted autoplay. React doesn't reliably reflect `muted` to the DOM
    // property, so iOS/Safari treats the video as unmuted and blocks autoplay (blank hero).
    useEffect(() => {
        const v = mediaRef.current;
        if (!useVideo || !v || v.tagName !== 'VIDEO') return;

        v.muted = true;
        v.defaultMuted = true;
        const play = () => { const p = v.play(); if (p) p.catch(() => {}); };
        play();
        v.addEventListener('loadeddata', play, { once: true });
        const onFirstTouch = () => play();
        document.addEventListener('touchstart', onFirstTouch, { once: true, passive: true });
        return () => document.removeEventListener('touchstart', onFirstTouch);
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
