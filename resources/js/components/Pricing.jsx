import { PACKAGES } from '../lib/site';
import { useBooking } from '../context/BookingContext';

export default function Pricing() {
    const { update, scrollToBook } = useBooking();

    const choose = (pkg) => {
        update({ service: pkg.service, pkg: pkg.name });
        scrollToBook();
    };

    return (
        <section id="pricing" className="section">
            <div className="wrap">
                <div className="sec-head center reveal">
                    <span className="eyebrow">✦ Pricing Packages</span>
                    <h2>Custom beauty bundles for your unique needs</h2>
                    <p>
                        We believe self-care should be accessible. Choose a curated package — or build your
                        own when you book.
                    </p>
                </div>
                <div className="price-grid">
                    {PACKAGES.map((p) => (
                        <div className={p.featured ? 'price feat reveal' : 'price reveal'} key={p.name}>
                            {p.featured && <span className="ribbon">Popular</span>}
                            <span className="tag">{p.tag}</span>
                            <h3>{p.name}</h3>
                            <div className="amt">
                                {p.price}
                                <small>{p.unit}</small>
                            </div>
                            <ul>
                                {p.items.map((i) => (
                                    <li key={i}>{i}</li>
                                ))}
                            </ul>
                            <button
                                className={p.featured ? 'btn price-btn-light' : 'btn ghost'}
                                onClick={() => choose(p)}
                            >
                                Choose
                            </button>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
