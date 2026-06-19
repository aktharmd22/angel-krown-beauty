import { createContext, useContext, useState, useCallback, useMemo } from 'react';
import { buildBookingMessage, waLink, SITE } from '../lib/site';

const BookingContext = createContext(null);

const INITIAL = {
    service: '',
    pkg: '',
    location: 'salon',
    staff: '',
    date: '',
    time: '',
    name: '',
    phone: '',
    addr: '',
};

export function BookingProvider({ children, waNumber = SITE.waNumber }) {
    const [state, setState] = useState(INITIAL);

    const update = useCallback((patch) => setState((s) => ({ ...s, ...patch })), []);

    const scrollToBook = useCallback(() => {
        const el = document.getElementById('book');
        if (!el) return;
        if (window.lenis) window.lenis.scrollTo(el, { offset: -10, duration: 1.2 });
        else el.scrollIntoView({ behavior: 'smooth' });
    }, []);

    const message = useMemo(() => buildBookingMessage(state), [state]);
    const link = useMemo(() => waLink(message, waNumber), [message, waNumber]);

    const value = useMemo(
        () => ({ state, update, setState, scrollToBook, message, link, waNumber }),
        [state, update, scrollToBook, message, link, waNumber],
    );

    return <BookingContext.Provider value={value}>{children}</BookingContext.Provider>;
}

export function useBooking() {
    const ctx = useContext(BookingContext);
    if (!ctx) throw new Error('useBooking must be used within BookingProvider');
    return ctx;
}
