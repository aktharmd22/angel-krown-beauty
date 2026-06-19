import { useLayoutEffect, useRef } from 'react';
import { usePage } from '@inertiajs/react';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { TEAM } from '../lib/site';
import { useBooking } from '../context/BookingContext';

gsap.registerPlugin(ScrollTrigger);

export default function Specialists() {
    const { state, update, scrollToBook } = useBooking();
    const { specialists } = usePage().props;
    const team = specialists?.length ? specialists : TEAM;
    const rowRef = useRef(null);

    useLayoutEffect(() => {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduce || !rowRef.current) return;

        const ctx = gsap.context(() => {
            gsap.from('.spec-panel', {
                clipPath: 'inset(100% 0% 0% 0%)',
                yPercent: 12,
                opacity: 0,
                duration: 1.1,
                ease: 'power3.out',
                stagger: 0.12,
                scrollTrigger: { trigger: rowRef.current, start: 'top 82%' },
            });
        }, rowRef);

        return () => ctx.revert();
    }, [team.length]);

    const pick = (m) => {
        update({ staff: m.option });
        scrollToBook();
    };

    return (
        <section id="team" className="section specialists">
            <div className="wrap">
                <div className="sec-head center reveal">
                    <span className="eyebrow">Meet Your Specialist</span>
                    <h2>Book the hands you trust</h2>
                    <p>Hover a specialist to meet them — tap to add them to your booking.</p>
                </div>

                <div className="spec-row" ref={rowRef}>
                    {team.map((m, i) => {
                        const selected = state.staff === m.option;
                        return (
                            <button
                                key={m.name}
                                className={selected ? 'spec-panel sel' : 'spec-panel'}
                                onClick={() => pick(m)}
                                style={m.img ? { backgroundImage: `url(${m.img})` } : undefined}
                            >
                                <span className="spec-overlay" aria-hidden="true" />
                                <span className="spec-index">{String(i + 1).padStart(2, '0')}</span>
                                <span className="spec-info">
                                    <b>{m.name}</b>
                                    <span className="spec-role">{m.role}</span>
                                    {m.blurb && <p>{m.blurb}</p>}
                                    <span className="spec-cta">{selected ? 'Selected ✓' : 'Add to booking →'}</span>
                                </span>
                            </button>
                        );
                    })}
                </div>
            </div>
        </section>
    );
}
