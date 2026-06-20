import { useEffect, useRef } from 'react';
import { WhatsAppIcon } from './icons';

/**
 * Scroll-scrubbed cinematic hero.
 * A full-screen background video whose playback position is driven by scroll —
 * scrolling "plays" the footage frame-by-frame (desktop). On mobile / reduced-motion
 * the video simply autoplay-loops, while the text chapters still cross-fade on scroll.
 */
export default function CinematicHero() {
    const stageRef = useRef(null);
    const videoRef = useRef(null);
    const c0 = useRef(null);
    const c1 = useRef(null);
    const c2 = useRef(null);
    const barRef = useRef(null);

    useEffect(() => {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const desktop = window.matchMedia('(min-width: 921px)').matches;
        const video = videoRef.current;
        const stage = stageRef.current;
        if (!video || !stage) return;

        const scrub = desktop && !reduce;
        let duration = 0;
        let target = 0;
        let current = 0;
        let raf = 0;
        let active = true;

        const onMeta = () => { duration = video.duration || 0; };
        video.addEventListener('loadedmetadata', onMeta);
        if (video.readyState >= 1) onMeta();

        if (scrub) {
            video.pause();
        } else {
            // mobile / reduced-motion: gentle autoplay loop using the lighter (non-intra) file
            video.muted = true;
            video.defaultMuted = true;
            video.loop = true;
            video.setAttribute('muted', '');
            video.src = '/assets/video/hero.mp4';
            video.load();
            const p = video.play();
            if (p && p.catch) p.catch(() => {});
        }

        // opacity over progress p: ramp up a→b, hold b→c, ramp down c→d
        const seg = (p, a, b, c, d) => {
            if (p <= a) return a <= 0 ? 1 : 0;
            if (p < b) return (p - a) / (b - a);
            if (p <= c) return 1;
            if (p < d) return 1 - (p - c) / (d - c);
            return d >= 1 ? 1 : 0;
        };

        const onScroll = () => {
            const rect = stage.getBoundingClientRect();
            const scrollable = Math.max(1, rect.height - window.innerHeight);
            const p = Math.min(1, Math.max(0, -rect.top / scrollable));
            target = p * duration;

            if (c0.current) c0.current.style.opacity = seg(p, 0, 0, 0.18, 0.30).toFixed(3);
            if (c1.current) c1.current.style.opacity = seg(p, 0.34, 0.42, 0.56, 0.64).toFixed(3);
            if (c2.current) c2.current.style.opacity = seg(p, 0.70, 0.78, 1.0, 1.0).toFixed(3);
            if (barRef.current) barRef.current.style.transform = `scaleX(${p.toFixed(4)})`;
        };

        const tick = () => {
            if (!active) return;
            if (scrub && duration) {
                current += (target - current) * 0.1;
                if (Math.abs(target - current) > 0.002) {
                    try { video.currentTime = current; } catch (e) { /* seeking */ }
                }
            }
            raf = requestAnimationFrame(tick);
        };

        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll);
        onScroll();
        if (scrub) tick();

        return () => {
            active = false;
            window.removeEventListener('scroll', onScroll);
            window.removeEventListener('resize', onScroll);
            video.removeEventListener('loadedmetadata', onMeta);
            cancelAnimationFrame(raf);
        };
    }, []);

    return (
        <section className="cine-stage" ref={stageRef}>
            <div className="cine-sticky">
                <video
                    ref={videoRef}
                    className="cine-video"
                    src="/assets/video/hero-scrub.mp4"
                    muted
                    playsInline
                    preload="auto"
                    poster="/assets/img/hero-3.jpg"
                />
                <div className="cine-scrim" />

                <div className="cine-chapters">
                    <div className="cine-chapter" ref={c0}>
                        <span className="cine-eyebrow">Angel Krown · Galaxy Ampang</span>
                        <h1>The Art of<br /><em>Effortless Beauty</em></h1>
                        <p>A cinematic escape for nails, skin &amp; hair.</p>
                    </div>

                    <div className="cine-chapter" ref={c1}>
                        <h2>Crafted by<br /><em>expert hands</em></h2>
                        <p>Every detail considered. Every visit, a ritual.</p>
                    </div>

                    <div className="cine-chapter" ref={c2}>
                        <h2>Your moment of<br /><em>quiet luxury</em></h2>
                        <a href="#book" className="btn hero-book">
                            <WhatsAppIcon /> Book on WhatsApp
                        </a>
                    </div>
                </div>

                <div className="cine-progress"><i ref={barRef} /></div>
                <div className="scroll-hint"><span className="line" />Scroll</div>
            </div>
        </section>
    );
}
