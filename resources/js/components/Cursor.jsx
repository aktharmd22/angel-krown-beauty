import { useEffect, useRef } from 'react';

export default function Cursor() {
    const dotRef = useRef(null);
    const ringRef = useRef(null);

    useEffect(() => {
        const fine = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
        if (!fine) return;

        const dot = dotRef.current;
        const ring = ringRef.current;
        let mx = window.innerWidth / 2;
        let my = window.innerHeight / 2;
        let rx = mx;
        let ry = my;
        let raf;

        const move = (e) => {
            mx = e.clientX;
            my = e.clientY;
            dot.style.transform = `translate(${mx}px, ${my}px) translate(-50%, -50%)`;
        };
        const loop = () => {
            rx += (mx - rx) * 0.18;
            ry += (my - ry) * 0.18;
            ring.style.transform = `translate(${rx}px, ${ry}px) translate(-50%, -50%)`;
            raf = requestAnimationFrame(loop);
        };

        const isInteractive = (el) =>
            el && el.closest('a, button, .chip, .member, input, select, .fab');
        const over = (e) => ring.classList.toggle('hover', !!isInteractive(e.target));

        window.addEventListener('pointermove', move, { passive: true });
        window.addEventListener('pointerover', over, { passive: true });
        loop();

        return () => {
            window.removeEventListener('pointermove', move);
            window.removeEventListener('pointerover', over);
            cancelAnimationFrame(raf);
        };
    }, []);

    return (
        <>
            <div className="cursor-dot" ref={dotRef} aria-hidden="true" />
            <div className="cursor-ring" ref={ringRef} aria-hidden="true" />
        </>
    );
}
