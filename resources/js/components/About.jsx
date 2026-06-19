import { useEffect, useRef } from 'react';
import { STATS } from '../lib/site';

/** Counts the numeric part up from 0 when it scrolls into view. */
function CountUp({ value }) {
    const ref = useRef(null);

    useEffect(() => {
        const el = ref.current;
        const match = String(value).match(/^([\d.]+)(.*)$/);
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (!match || reduce) {
            el.textContent = value;
            return;
        }

        const target = parseFloat(match[1]);
        const suffix = match[2];
        const decimals = (match[1].split('.')[1] || '').length;
        let done = false;

        const io = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting || done) return;
                    done = true;
                    const duration = 1400;
                    const start = performance.now();
                    const tick = (now) => {
                        const p = Math.min(1, (now - start) / duration);
                        const eased = 1 - Math.pow(1 - p, 3);
                        el.textContent = (target * eased).toFixed(decimals) + suffix;
                        if (p < 1) requestAnimationFrame(tick);
                        else el.textContent = match[1] + suffix;
                    };
                    requestAnimationFrame(tick);
                    io.unobserve(el);
                });
            },
            { threshold: 0.6 },
        );

        io.observe(el);
        return () => io.disconnect();
    }, [value]);

    return <b ref={ref}>{value}</b>;
}

export default function About() {
    const photoRef = useRef(null);

    const onMove = (e) => {
        const el = photoRef.current;
        if (!el || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        const r = el.getBoundingClientRect();
        const px = (e.clientX - r.left) / r.width - 0.5;
        const py = (e.clientY - r.top) / r.height - 0.5;
        el.style.setProperty('--rx', `${(-py * 6).toFixed(2)}deg`);
        el.style.setProperty('--ry', `${(px * 6).toFixed(2)}deg`);
    };

    const onLeave = () => {
        const el = photoRef.current;
        if (el) {
            el.style.setProperty('--rx', '0deg');
            el.style.setProperty('--ry', '0deg');
        }
    };

    return (
        <section id="about" className="section">
            <span className="about-orb orb-1" aria-hidden="true" />
            <span className="about-orb orb-2" aria-hidden="true" />

            <div className="wrap about-grid">
                <div className="about-text reveal">
                    <span className="eyebrow">About Us</span>
                    <h2>Enhancing natural beauty with a personal touch</h2>
                    <p>
                        Founded on a passion for creativity and care, Angel Krown Beauty Studio is your
                        one-stop destination for elegant nails, glowing skin, and trendy hair — all in a
                        calm, modern lounge in the heart of Ampang.
                    </p>
                    <div className="mission">
                        <b>Our mission</b> — to help every guest leave feeling confident, radiant, and
                        beautifully renewed.
                    </div>
                    <div className="stats">
                        {STATS.map((s) => (
                            <div className="stat" key={s.label}>
                                <CountUp value={s.value} />
                                <span>{s.label}</span>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="about-photo-wrap reveal">
                    <div className="about-photo" ref={photoRef} onMouseMove={onMove} onMouseLeave={onLeave}>
                        <img src="/assets/img/about-lounge.jpg" alt="Inside the Angel Krown lounge" loading="lazy" />
                        <div className="badge">
                            <span>Each visit is more than a service</span>
                            <a href="#book">Book →</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
