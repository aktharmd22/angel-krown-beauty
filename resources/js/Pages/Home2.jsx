import { Head } from '@inertiajs/react';
import { usePage } from '@inertiajs/react';
import { BookingProvider } from '../context/BookingContext';
import { useSmoothScroll } from '../hooks/useSmoothScroll';
import { useReveal } from '../hooks/useReveal';

import Loader from '../components/Loader';
import Cursor from '../components/Cursor';
import Nav from '../components/Nav';
import CinematicHero from '../components/CinematicHero';
import Marquee from '../components/Marquee';
import About from '../components/About';
import Services from '../components/Services';
import Pricing from '../components/Pricing';
import Specialists from '../components/Specialists';
import HomeService from '../components/HomeService';
import Booking from '../components/Booking';
import Contact from '../components/Contact';
import Footer from '../components/Footer';
import WhatsAppFab from '../components/WhatsAppFab';

export default function Home2() {
    useSmoothScroll();
    useReveal();

    const { wa } = usePage().props;

    return (
        <BookingProvider waNumber={wa?.number}>
            <Head title="A Cinematic Beauty Experience" />

            <Loader />
            <Cursor />
            <div className="aurora" aria-hidden="true">
                <span className="aurora-blob ab1" />
                <span className="aurora-blob ab2" />
                <span className="aurora-blob ab3" />
            </div>
            <div className="grain" aria-hidden="true" />
            <div className="vignette" aria-hidden="true" />

            <Nav />
            <main className="cine-main">
                <CinematicHero />
                <Marquee />
                <About />
                <Services />
                <Pricing />
                <Specialists />
                <HomeService />
                <Booking />
                <Contact />
            </main>
            <Footer />
            <WhatsAppFab />
        </BookingProvider>
    );
}
