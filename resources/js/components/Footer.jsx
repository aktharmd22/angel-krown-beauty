import { SITE } from '../lib/site';

export default function Footer() {
    return (
        <footer>
            <div className="wrap foot-grid">
                <div>
                    <a href="#top" className="foot-brand">
                        <img src="/assets/img/logo.png" alt="Angel Krown" className="foot-logo-img" />
                    </a>
                    <p>
                        Your one-stop beauty destination for elegant nails, glowing skin, and trendy
                        hairstyles in Ampang.
                    </p>
                </div>
                <div>
                    <h4>Explore</h4>
                    <a href="#about">About Us</a>
                    <a href="#services">Services</a>
                    <a href="#team">Specialists</a>
                    <a href="#pricing">Pricing</a>
                    <a href="#home-service">Home Service</a>
                </div>
                <div>
                    <h4>Get in touch</h4>
                    <a href={`tel:${SITE.phones.nail.tel}`}>Nail · {SITE.phones.nail.value}</a>
                    <a href={`tel:${SITE.phones.hair.tel}`}>Hair · {SITE.phones.hair.value}</a>
                    <a href="#book">Book on WhatsApp</a>
                    <a href="#contact">Find us</a>
                </div>
            </div>
            <div className="foot-bottom">
                © 2026 Angel Krown Beauty Studio. All rights reserved. · Crafted for effortless beauty.
            </div>
        </footer>
    );
}
