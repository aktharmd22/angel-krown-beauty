import { useEffect, useState } from 'react';
import { WhatsAppIcon, Facebook, Instagram } from './icons';
import { SITE, waLink } from '../lib/site';
import { useBooking } from '../context/BookingContext';

const LEFT = [
    ['About', '#about'],
    ['Services', '#services'],
    ['Pricing', '#pricing'],
];
const RIGHT = [
    ['Specialists', '#team'],
    ['Home Service', '#home-service'],
    ['Contact', '#contact'],
];

export default function Nav() {
    const [solid, setSolid] = useState(false);
    const [open, setOpen] = useState(false);
    const { waNumber } = useBooking();

    useEffect(() => {
        const onScroll = () => setSolid(window.scrollY > 60);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    return (
        <header className={solid ? 'site-head solid' : 'site-head'}>
            {/* top contact bar */}
            <div className="topbar">
                <div className="wrap topbar-inner">
                    <div className="topbar-left">
                        <a href={waLink("Hi Angel Krown! I'd like to ask about your services.", waNumber)} target="_blank" rel="noopener">
                            <WhatsAppIcon size={13} fill="currentColor" /> WhatsApp: {SITE.phones.nail.value}
                        </a>
                        <span className="sep">·</span>
                        <a href={`tel:${SITE.phones.hair.tel}`}>Call: {SITE.phones.hair.value}</a>
                    </div>
                    <div className="topbar-right">
                        <span className="follow">Follow Us</span>
                        <a href="#" aria-label="Facebook" className="soc"><Facebook /></a>
                        <a href="#" aria-label="Instagram" className="soc"><Instagram /></a>
                        <span className="sep">|</span>
                        <span className="hours">Open Daily · 9–6</span>
                    </div>
                </div>
            </div>

            {/* main nav with centered logo */}
            <nav className="mainnav">
                <div className="wrap mainnav-inner">
                    <ul className="nav-side nav-left">
                        {LEFT.map(([label, href]) => (
                            <li key={href}><a href={href}>{label}</a></li>
                        ))}
                    </ul>

                    <a href="#top" className="nav-logo">
                        <img src="/assets/img/logo.png" alt="Angel Krown — Beauty Studio" className="nav-logo-img" />
                    </a>

                    <ul className="nav-side nav-right">
                        {RIGHT.map(([label, href]) => (
                            <li key={href}><a href={href}>{label}</a></li>
                        ))}
                        <li><a href="#book" className="btn nav-book">Book</a></li>
                    </ul>

                    <button
                        className="menu-btn"
                        aria-label="Menu"
                        aria-expanded={open}
                        onClick={() => setOpen((o) => !o)}
                    >
                        <span /><span /><span />
                    </button>
                </div>
            </nav>

            <div className={open ? 'mobile-menu open' : 'mobile-menu'} onClick={() => setOpen(false)}>
                {[...LEFT, ...RIGHT].map(([label, href]) => (
                    <a key={href} href={href}>{label}</a>
                ))}
                <a href="#book" className="btn">Book Appointment</a>
            </div>
        </header>
    );
}
