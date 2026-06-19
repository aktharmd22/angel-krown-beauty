import { waLink } from '../lib/site';
import { useBooking } from '../context/BookingContext';
import { WhatsAppIcon } from './icons';

export default function WhatsAppFab() {
    const { waNumber } = useBooking();
    const href = waLink("Hi Angel Krown! I'd like to ask about your services.", waNumber);
    return (
        <a className="fab" href={href} target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
            <WhatsAppIcon size={30} />
        </a>
    );
}
