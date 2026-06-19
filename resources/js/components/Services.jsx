import { useRef } from 'react';
import { SERVICES } from '../lib/site';
import { useBooking } from '../context/BookingContext';

function ServiceCard({ svc }) {
    const ref = useRef(null);
    const { update, scrollToBook } = useBooking();

    const onMove = (e) => {
        const el = ref.current;
        if (!el || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        const r = el.getBoundingClientRect();
        const px = (e.clientX - r.left) / r.width - 0.5;
        const py = (e.clientY - r.top) / r.height - 0.5;
        el.style.transform = `translateY(-10px) rotateY(${px * 6}deg) rotateX(${-py * 6}deg)`;
    };
    const onLeave = () => {
        if (ref.current) ref.current.style.transform = '';
    };

    const choose = () => {
        update({ service: svc.title, pkg: '' });
        scrollToBook();
    };

    return (
        <div className="svc reveal" ref={ref} onMouseMove={onMove} onMouseLeave={onLeave}>
            <span className="svc-glow" aria-hidden="true" />
            <div className="svc-head">
                <span className="svc-icon">{svc.icon}</span>
                <span className="svc-num">{svc.num}</span>
            </div>
            <h3>{svc.title}</h3>
            <ul>
                {svc.items.map((i) => (
                    <li key={i}>{i}</li>
                ))}
            </ul>
            <div className="svc-foot">
                <span className="from">{svc.from}</span>
                <button className="svc-pick" onClick={choose}>
                    Book this <span className="arr">→</span>
                </button>
            </div>
        </div>
    );
}

export default function Services() {
    return (
        <section id="services" className="section services">
            <div className="wrap">
                <div className="sec-head center reveal">
                    <span className="eyebrow">Services You'll Love</span>
                    <h2>Nail &amp; salon services designed for you</h2>
                </div>
                <div className="svc-grid">
                    {SERVICES.map((svc) => (
                        <ServiceCard key={svc.num} svc={svc} />
                    ))}
                </div>
            </div>
        </section>
    );
}
