<x-filament-panels::page>
    <style>
        .artisan-dashboard {
            --artisan-ink: #111827;
            --artisan-muted: #64748b;
            --artisan-line: #e2e8f0;
            --artisan-blue: #2563eb;
            --artisan-violet: #7c3aed;
            --artisan-cyan: #0891b2;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .artisan-dashboard-hero {
            position: relative;
            overflow: hidden;
            border-radius: 1.5rem;
            padding: 2rem;
            color: #fff;
            background: linear-gradient(120deg, #0f172a 0%, #1d4ed8 52%, #0891b2 100%);
            box-shadow: 0 1.5rem 3rem rgba(15, 23, 42, .16);
        }

        .artisan-dashboard-hero::after {
            position: absolute;
            right: -4rem;
            bottom: -7rem;
            width: 18rem;
            height: 18rem;
            border: 2rem solid rgba(255, 255, 255, .12);
            border-radius: 999px;
            content: '';
        }

        .artisan-dashboard-hero-content,
        .artisan-dashboard-actions {
            position: relative;
            z-index: 1;
        }

        .artisan-dashboard-kicker {
            margin: 0 0 .5rem;
            color: rgba(255, 255, 255, .72);
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .artisan-dashboard-title {
            max-width: 38rem;
            margin: 0;
            font-size: clamp(1.75rem, 4vw, 3rem);
            font-weight: 800;
            letter-spacing: -.04em;
            line-height: 1.05;
        }

        .artisan-dashboard-copy {
            max-width: 38rem;
            margin: .85rem 0 0;
            color: rgba(255, 255, 255, .82);
            font-size: .92rem;
            line-height: 1.6;
        }

        .artisan-dashboard-actions {
            display: flex;
            flex-wrap: wrap;
            gap: .7rem;
            margin-top: 1.5rem;
        }

        .artisan-dashboard-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.65rem;
            padding: 0 1rem;
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: .8rem;
            color: #fff;
            background: rgba(255, 255, 255, .14);
            font-size: .78rem;
            font-weight: 800;
            text-decoration: none;
            transition: background .18s, transform .18s;
        }

        .artisan-dashboard-action:hover {
            color: #fff;
            background: rgba(255, 255, 255, .24);
            transform: translateY(-1px);
        }

        .artisan-dashboard-action-primary {
            border-color: #fff;
            color: #172554;
            background: #fff;
        }

        .artisan-dashboard-action-primary:hover {
            color: #172554;
            background: #f8fafc;
        }

        .artisan-dashboard-stat-wrap {
            padding: 1rem;
            border: 1px solid var(--artisan-line);
            border-radius: 1.25rem;
            background: #f8fafc;
        }

        .artisan-dashboard-stat-wrap .fi-wi-stats-overview {
            gap: .85rem;
        }

        .artisan-dashboard-stat-wrap .fi-wi-stats-overview-stat {
            border-top: 3px solid var(--artisan-blue);
            box-shadow: 0 .6rem 1.5rem rgba(15, 23, 42, .06);
        }

        .artisan-dashboard-stat-wrap .fi-wi-stats-overview-stat:nth-child(3n + 2) {
            border-top-color: var(--artisan-violet);
        }

        .artisan-dashboard-stat-wrap .fi-wi-stats-overview-stat:nth-child(3n) {
            border-top-color: var(--artisan-cyan);
        }

        .artisan-dashboard-links {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .artisan-dashboard-link-card {
            display: block;
            padding: 1.15rem;
            border: 1px solid var(--artisan-line);
            border-radius: 1.15rem;
            color: var(--artisan-ink);
            background: #fff;
            text-decoration: none;
            transition: border-color .18s, box-shadow .18s, transform .18s;
        }

        .artisan-dashboard-link-card:hover {
            border-color: #94a3b8;
            box-shadow: 0 .8rem 1.8rem rgba(15, 23, 42, .08);
            transform: translateY(-2px);
        }

        .artisan-dashboard-link-card strong {
            display: block;
            font-size: .9rem;
            font-weight: 800;
        }

        .artisan-dashboard-link-card span {
            display: block;
            margin-top: .35rem;
            color: var(--artisan-muted);
            font-size: .78rem;
            line-height: 1.45;
        }

        @media (max-width: 700px) {
            .artisan-dashboard-hero {
                padding: 1.35rem;
                border-radius: 1.15rem;
            }

            .artisan-dashboard-links {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="artisan-dashboard">
        <section class="artisan-dashboard-hero">
            <div class="artisan-dashboard-hero-content">
                <p class="artisan-dashboard-kicker">MerciBuddy workspace</p>
                <h1 class="artisan-dashboard-title">Turn your skills into your next opportunity.</h1>
                <p class="artisan-dashboard-copy">Keep your profile sharp, publish the services you offer, and stay ready when new customer requests arrive.</p>
                <div class="artisan-dashboard-actions">
                    <a href="{{ url('/artisan/artisan-profile') }}" class="artisan-dashboard-action artisan-dashboard-action-primary">Edit profile</a>
                    <a href="{{ url('/artisan/services/create') }}" class="artisan-dashboard-action">Add a service</a>
                    <a href="{{ url('/artisan/contact-media') }}" class="artisan-dashboard-action">Contact details</a>
                </div>
            </div>
        </section>

        <section class="artisan-dashboard-stat-wrap" aria-label="Workspace overview">
            {{ $this->content }}
        </section>

        <section class="artisan-dashboard-links" aria-label="Workspace shortcuts">
            <a href="{{ url('/artisan/artisan-profile') }}" class="artisan-dashboard-link-card">
                <strong>Profile and availability</strong>
                <span>Update your profession, location, rate, and account status.</span>
            </a>
            <a href="{{ url('/artisan/services') }}" class="artisan-dashboard-link-card">
                <strong>Service portfolio</strong>
                <span>Show customers the work and services you offer.</span>
            </a>
            <a href="{{ url('/artisan/contact-media') }}" class="artisan-dashboard-link-card">
                <strong>Contact details</strong>
                <span>Keep your phone and email ready for customer requests.</span>
            </a>
        </section>
    </div>
</x-filament-panels::page>
