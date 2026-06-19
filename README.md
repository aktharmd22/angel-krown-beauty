# Angel Krown Beauty

Marketing website **and** admin platform for **Angel Krown** — a luxury nail & beauty salon at
Galaxy Ampang Shopping Centre, Ampang, Selangor.

A single unified Laravel application that serves the public site and the staff admin panel.

## Tech stack

- **Laravel 11** (PHP 8.2+)
- **Inertia.js + React 19 + Vite** — animated single-page marketing site (GSAP, Lenis smooth scroll)
- **Filament v3** — admin panel (DM Sans theme, wine & gold brand)
- **WhatsApp Cloud API** (Meta Graph API) — booking notifications, approved message templates,
  marketing broadcasts, and a two-way inbox
- **DomPDF** — branded salon invoices
- SQLite for local dev / MySQL in production

## Features

**Public site** — cinematic hero, services, specialists, packages, and a booking flow.

**Admin panel**
- Dashboard analytics (revenue, bookings, top services)
- Bookings, Invoices/Billing (PDF), Customers (mini-CRM), Specialists
- **WhatsApp** — two-way Inbox, Cloud API settings, admins
- **Marketing** — Contacts, Groups, message Templates (Meta-approved), Broadcasts

## Local setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build        # or: npm run dev
php artisan serve
```

Admin panel: `/admin` · public site: `/`

## Configuration

WhatsApp Cloud API credentials are configured in **Admin → WhatsApp → Settings & API**
(stored in the database, not in `.env`). Point the Meta webhook at `/api/whatsapp/webhook`
and subscribe to the `messages` field to receive customer messages in the Inbox.
