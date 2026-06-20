import { useEffect, useRef } from 'react';

/**
 * Light-pink background video for /home3. The frames are driven by scroll —
 * scrolling advances the footage, and it holds still when you stop ("otherwise not").
 * Honors reduced-motion by freezing on the first frame.
 */
export default function Home3Background() {
    const videoRef = useRef(null);

    useEffect(() => {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const video = videoRef.current;
        if (!video) return;

        let duration = 0;
        let target = 0;
        let current = 0;
        let raf = 0;
        let active = true;

        const onMeta = () => { duration = video.duration || 0; };
        video.addEventListener('loadedmetadata', onMeta);
        if (video.readyState >= 1) onMeta();
        video.pause();

        const onScroll = () => {
            const max = document.documentElement.scrollHeight - window.innerHeight;
            const p = max > 0 ? Math.min(1, Math.max(0, window.scrollY / max)) : 0;
            target = p * duration;
        };

        const tick = () => {
            if (!active) return;
            if (duration) {
                current += (target - current) * 0.08;
                if (Math.abs(target - current) > 0.002) {
                    try { video.currentTime = current; } catch (e) { /* seeking */ }
                }
            }
            raf = requestAnimationFrame(tick);
        };

        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll);
        onScroll();
        if (!reduce) tick();

        return () => {
            active = false;
            window.removeEventListener('scroll', onScroll);
            window.removeEventListener('resize', onScroll);
            video.removeEventListener('loadedmetadata', onMeta);
            cancelAnimationFrame(raf);
        };
    }, []);

    return (
        <div className="home3-bg" aria-hidden="true">
            <video ref={videoRef} src="/assets/video/hero3-bg.mp4" muted playsInline preload="auto" />
        </div>
    );
}
