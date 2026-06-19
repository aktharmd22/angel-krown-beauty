import { useLayoutEffect, useRef } from 'react';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SITE } from '../lib/site';
import { MapPin, Phone, Clock } from './icons';

gsap.registerPlugin(ScrollTrigger);

export default function Contact() {
    const ref = useRef(null);

    useLayoutEffect(() => {
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduce || !ref.current) return;

        const ctx = gsap.context(() => {
            gsap.from('.contact-info > *', {
                y: 28,
                opacity: 0,
                duration: 0.8,
                stagger: 0.1,
                ease: 'power3.out',
                scrollTrigger: { trigger: ref.current, start: 'top 76%' },
            });
            gsap.from('.map', {
                opacity: 0,
                scale: 0.95,
                duration: 1.1,
                ease: 'power3.out',
                scrollTrigger: { trigger: ref.current, start: 'top 76%' },
            });
        }, ref);

        return () => ctx.revert();
    }, []);

    return (
        <section id="contact" className="section">
            <div className="wrap contact-grid" ref={ref}>
                <div className="contact-info">
                    <span className="eyebrow">Get in Touch</span>
                    <h2>Visit us in Ampang</h2>

                    <div className="ci-item">
                        <span className="ic"><MapPin /></span>
                        <div>
                            <b>Address</b>
                            <span>{SITE.address}</span>
                        </div>
                    </div>

                    <a className="ci-item" href={`tel:${SITE.phones.nail.tel}`}>
                        <span className="ic"><Phone /></span>
                        <div>
                            <b>{SITE.phones.nail.label}</b>
                            <span>{SITE.phones.nail.value}</span>
                        </div>
                    </a>

                    <a className="ci-item" href={`tel:${SITE.phones.hair.tel}`}>
                        <span className="ic"><Phone /></span>
                        <div>
                            <b>{SITE.phones.hair.label}</b>
                            <span>{SITE.phones.hair.value}</span>
                        </div>
                    </a>

                    <div className="ci-item">
                        <span className="ic"><Clock /></span>
                        <div>
                            <b>Opening Hours</b>
                            <span>{SITE.hours}</span>
                        </div>
                    </div>

                    <a href="#book" className="btn">Book your appointment</a>
                </div>

                <div className="map">
                    <iframe loading="lazy" src={SITE.mapEmbed} title="Angel Krown location" />
                </div>
            </div>
        </section>
    );
}
