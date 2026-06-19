// ============================================================
// Angel Krown — shared site data (single source of truth)
// ============================================================

export const SITE = {
    name: 'Angel Krown',
    tagline: 'Luxury Nail & Beauty Lounge',
    area: 'Ampang',
    waNumber: '60162674626', // WhatsApp business number (change in one place)
    phones: {
        nail: { label: 'Nail Salon', value: '016 2674626', tel: '+60162674626' },
        hair: { label: 'Hair Salon', value: '012 9626286', tel: '+60129626286' },
    },
    address:
        '49-G87, Galaxy Ampang Shopping Centre, Jln Gagang 5, Taman Gagang, 68000 Ampang, Selangor',
    hours: 'Mon – Sun · 9:00 AM – 6:00 PM',
    mapEmbed:
        'https://maps.google.com/maps?q=Galaxy%20Ampang%20Shopping%20Centre%20Ampang%20Selangor&t=&z=15&ie=UTF8&iwloc=&output=embed',
};

export const STATS = [
    { value: '4.9★', label: 'Guest Rating' },
    { value: '8+', label: 'Specialists' },
    { value: '1k+', label: 'Happy Guests' },
    { value: '7', label: 'Days Open' },
];

export const SERVICES = [
    {
        num: '01',
        icon: '💅',
        title: 'Nail Studio',
        items: [
            'Classic manicure & pedicure',
            'Gel polish & long-wear colour',
            'Custom nail art & design',
            'Nail extensions / acrylic',
            'Nail repair & hand & foot spa',
        ],
        from: 'From RM 39',
        img: '/assets/img/hero-2.jpg',
    },
    {
        num: '02',
        icon: '💇',
        title: 'Hair & Salon',
        items: [
            'Fresh cuts & styling',
            'Healthy scalp treatments',
            'Soft, natural hair colouring',
            'Blow-dry & finishing',
            'Keratin & repair treatments',
        ],
        from: 'From RM 59',
        img: '/assets/img/team-meiling.jpg',
    },
    {
        num: '03',
        icon: '✨',
        title: 'Skin & Glow',
        items: [
            'Brightening facials',
            'Hydration & glow treatments',
            'Lash lift & extensions',
            'Brow shaping & tint',
            'Bridal & event prep',
        ],
        from: 'From RM 79',
        img: '/assets/img/team-farah.jpg',
    },
];

export const PACKAGES = [
    {
        tag: 'Express',
        name: 'The Quick Refresh',
        price: 'RM 49',
        unit: '/visit',
        items: ['Express manicure', 'Shape & buff', 'Single gel colour', '~45 minutes'],
        service: 'Nail Studio',
        featured: false,
    },
    {
        tag: 'Signature',
        name: 'The Signature Glow',
        price: 'RM 149',
        unit: '/visit',
        items: ['Gel manicure + pedicure', 'Custom nail art', 'Express facial', '~2 hours of pampering'],
        service: 'Skin & Glow',
        featured: true,
    },
    {
        tag: 'Luxe',
        name: 'The Full Krown',
        price: 'RM 299',
        unit: '/visit',
        items: ['Hair, nails & facial', 'Half-day retreat', 'Drinks & treats', 'Perfect for events'],
        service: 'Hair & Salon',
        featured: false,
    },
];

export const TEAM = [
    {
        name: 'Aisyah',
        role: 'Lead Nail Artist',
        blurb: 'Gel art, intricate designs & extensions.',
        img: '/assets/img/team-aisyah.jpg',
        option: 'Aisyah — Nail Artist',
    },
    {
        name: 'Mei Ling',
        role: 'Senior Stylist',
        blurb: 'Colour, cuts & scalp care.',
        img: '/assets/img/team-meiling.jpg',
        option: 'Mei Ling — Senior Stylist',
    },
    {
        name: 'Priya',
        role: 'Lash & Brow Artist',
        blurb: 'Lash lifts, extensions & brow shaping.',
        img: '/assets/img/team-priya.jpg',
        option: 'Priya — Lash & Brow',
    },
    {
        name: 'Farah',
        role: 'Spa & Facials',
        blurb: 'Glow facials & hand & foot spa.',
        img: '/assets/img/team-farah.jpg',
        option: 'Farah — Spa & Facials',
    },
];

export const TIMES = ['10:00 AM', '11:30 AM', '1:00 PM', '2:30 PM', '4:00 PM', '5:00 PM'];

// Build the WhatsApp booking message from the booking state
export function buildBookingMessage(s) {
    let msg = "Hi Angel Krown! 💖 I'd like to book an appointment.\n";
    if (s.service) msg += `\n• Service: ${s.service}`;
    if (s.pkg) msg += `\n• Package: ${s.pkg}`;
    msg += `\n• Location: ${s.location === 'home' ? 'Home service' : 'In-salon (Galaxy Ampang)'}`;
    if (s.location === 'home' && s.addr) msg += `\n• Address: ${s.addr}`;
    if (s.staff) msg += `\n• Specialist: ${s.staff}`;
    if (s.date) msg += `\n• Date: ${s.date}`;
    if (s.time) msg += `\n• Time: ${s.time}`;
    if (s.name) msg += `\n\nName: ${s.name}`;
    if (s.phone) msg += `\nPhone: ${s.phone}`;
    return msg;
}

export function waLink(message, number = SITE.waNumber) {
    return `https://wa.me/${number}?text=${encodeURIComponent(message)}`;
}
