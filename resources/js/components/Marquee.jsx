const PHRASE = 'Welcome to Angel Krown ✦ Luxury Nails ✦ Hair & Salon ✦ Skin & Glow ✦ Home Service Available ✦ Open 7 Days ✦';

export default function Marquee() {
    return (
        <div className="marquee" aria-hidden="true">
            <div className="track">
                <span>{PHRASE}&nbsp;</span>
                <span>{PHRASE}&nbsp;</span>
            </div>
        </div>
    );
}
