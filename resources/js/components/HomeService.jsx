import { WhatsAppIcon } from './icons';
import { useBooking } from '../context/BookingContext';

const FEATURES = [
    ['Same specialists, your sofa.', "Choose your favourite and we'll send them."],
    ['Perfect for bridal & events.', 'Get ready without the rush.'],
    ['Sanitised, sealed tools.', 'Hygiene-first, every visit.'],
];

export default function HomeService() {
    const { update, scrollToBook } = useBooking();

    const bookHome = () => {
        update({ location: 'home' });
        scrollToBook();
    };

    return (
        <section id="home-service" className="section home-section">
            <div className="home-serv reveal">
                <div className="home-grid">
                    <div className="home-copy">
                        <span className="eyebrow">✦ At-Home Beauty</span>
                        <h2>Bring the lounge home</h2>
                        <p>
                            Can't make it to Galaxy Ampang? We come to you. Our specialists arrive with
                            everything needed for a salon-grade experience in your own space — across the
                            Klang Valley.
                        </p>
                        <div className="home-feats">
                            {FEATURES.map(([title, body]) => (
                                <div key={title}>
                                    <span className="ic">✓</span>
                                    <span>
                                        <b>{title}</b> {body}
                                    </span>
                                </div>
                            ))}
                        </div>
                        <button className="btn home-cta" onClick={bookHome}>
                            <WhatsAppIcon /> Book a home visit
                        </button>
                    </div>
                    <div className="home-photo">
                        <img src="/assets/img/home-service.jpg" alt="At-home beauty service" loading="lazy" />
                    </div>
                </div>
            </div>
        </section>
    );
}
