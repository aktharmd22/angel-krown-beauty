import { useState } from 'react';
import { usePage } from '@inertiajs/react';
import { TEAM, TIMES } from '../lib/site';
import { useBooking } from '../context/BookingContext';

const SERVICE_CHIPS = ['Nail Studio', 'Hair & Salon', 'Skin & Glow', 'Package'];

export default function Booking() {
    const { state, update, message, link } = useBooking();
    const { specialists, wa } = usePage().props;
    const team = specialists?.length ? specialists : TEAM;
    const apiEnabled = !!wa?.apiEnabled;
    const [sending, setSending] = useState(false);
    const [sent, setSent] = useState(false);

    const chipActive = (chip) =>
        state.service === chip || (chip === 'Package' && !!state.pkg);

    const pickService = (chip) => update({ service: chip, pkg: chip === 'Package' ? state.pkg : '' });

    // Save the booking; if the Cloud API is on, it messages the customer +
    // admins automatically — otherwise fall back to the wa.me deep link.
    const submit = async (e) => {
        e.preventDefault();
        setSending(true);
        let useApi = false;
        try {
            const res = await fetch('/api/bookings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify({ ...state, message }),
            });
            if (res.ok) {
                const data = await res.json().catch(() => ({}));
                useApi = !!data.whatsapp_enabled;
            }
        } catch {
            /* network error — fall back to the deep link below */
        } finally {
            setSending(false);
            if (useApi) setSent(true);
            else window.open(link, '_blank', 'noopener');
        }
    };

    return (
        <section id="book" className="section booking">
            <div className="wrap">
                <div className="sec-head center reveal">
                    <span className="eyebrow">✦ Book Now</span>
                    <h2>Book your appointment today</h2>
                    <p>Ready to treat yourself? Tell us what you'd like — we'll confirm in minutes.</p>
                </div>

                <div className="book-card reveal">
                    {sent ? (
                        <div className="book-success">
                            <div className="success-emoji">💖</div>
                            <h2>Thank you, {state.name || 'lovely'}!</h2>
                            <p>Your booking is in — we've sent a confirmation to your WhatsApp and our team will be in touch shortly.</p>
                            <button type="button" className="btn ghost" onClick={() => setSent(false)}>Make another booking</button>
                        </div>
                    ) : (
                        <form className="book-form" onSubmit={submit}>
                            <div className="field">
                                <label>Choose a service</label>
                                <div className="chips">
                                    {SERVICE_CHIPS.map((chip) => (
                                        <button
                                            type="button"
                                            key={chip}
                                            className={chipActive(chip) ? 'chip on' : 'chip'}
                                            onClick={() => pickService(chip)}
                                        >
                                            {chip}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            <div className="field">
                                <label>Where would you like it?</label>
                                <div className="chips">
                                    <button
                                        type="button"
                                        className={state.location === 'salon' ? 'chip on' : 'chip'}
                                        onClick={() => update({ location: 'salon' })}
                                    >
                                        In-salon · Galaxy Ampang
                                    </button>
                                    <button
                                        type="button"
                                        className={state.location === 'home' ? 'chip on' : 'chip'}
                                        onClick={() => update({ location: 'home' })}
                                    >
                                        Home service · we come to you
                                    </button>
                                </div>
                            </div>

                            <div className={state.location === 'home' ? 'field addr show' : 'field addr hide'}>
                                <label>Your home address</label>
                                <input
                                    type="text"
                                    placeholder="Unit, street, area — Klang Valley"
                                    value={state.addr}
                                    onChange={(e) => update({ addr: e.target.value })}
                                />
                            </div>

                            <div className="field">
                                <label>Preferred specialist</label>
                                <select value={state.staff} onChange={(e) => update({ staff: e.target.value })}>
                                    <option value="">No preference</option>
                                    {team.map((m) => (
                                        <option key={m.name} value={m.option}>{m.option}</option>
                                    ))}
                                </select>
                            </div>

                            <div className="row2">
                                <div className="field">
                                    <label>Date</label>
                                    <input type="date" value={state.date} onChange={(e) => update({ date: e.target.value })} />
                                </div>
                                <div className="field">
                                    <label>Time</label>
                                    <select value={state.time} onChange={(e) => update({ time: e.target.value })}>
                                        <option value="">Select</option>
                                        {TIMES.map((t) => (
                                            <option key={t}>{t}</option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            <div className="row2">
                                <div className="field">
                                    <label>Your name</label>
                                    <input type="text" placeholder="Full name" value={state.name} onChange={(e) => update({ name: e.target.value })} />
                                </div>
                                <div className="field">
                                    <label>Your phone</label>
                                    <input type="tel" placeholder="01X-XXX XXXX" value={state.phone} onChange={(e) => update({ phone: e.target.value })} />
                                </div>
                            </div>

                            <button type="submit" className="btn book-submit" disabled={sending}>
                                {sending ? 'Sending…' : 'Book an Appointment'}
                            </button>
                            <p className="book-note">
                                {apiEnabled
                                    ? "We'll message you on WhatsApp to confirm — our team replies within minutes during opening hours."
                                    : 'Opens WhatsApp with your booking ready to send — our team confirms within minutes.'}
                            </p>
                        </form>
                    )}
                </div>
            </div>
        </section>
    );
}
