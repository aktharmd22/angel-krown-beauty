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
        setUseVideo(!reduce); // looping muted video on all devices (poster image only for reduced-motion)
    }, []);

    useEffect(() => {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduce || !mediaRef.current) return;

        const ctx = gsap.context(() => {
            gsap.fromTo(
                mediaRef.current,
                { scale: 1.08 },
                {
                    scale: 1.22,
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
