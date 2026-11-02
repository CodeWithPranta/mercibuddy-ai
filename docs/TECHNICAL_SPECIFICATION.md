# MerciBuddy Technical Specification

## 1. Overview

MerciBuddy is a Laravel web application and marketplace directory that connects visitors with local skilled artisans. Visitors browse artisans by country and service category, view public profiles, rate artisans, and send enquiries. Artisans maintain their public profile, services, contact details, and incoming enquiries through a dedicated Filament panel. Administrators manage users, artisans, categories, pages, locations, and site settings through a separate Filament panel.

The application also includes an AI concierge, "Yaara Brains", which answers questions about the directory and recommends real artisans from the database.

## 2. Technology Stack

| Layer | Technology |
| --- | --- |
| Runtime | PHP 8.4 |
| Framework | Laravel 13.30.1 |
| Database | MySQL (production), SQLite in-memory (tests) |
| Admin UI | Filament 4.12.8 |
| Reactive UI | Livewire 3.8.7, Volt 1.11.2 |
| AI | Laravel AI 0.11.2, Gemini, Groq |
| Authentication | Laravel Breeze, Laravel Socialite (Google) |
| API tokens | Laravel Sanctum |
| Frontend | Vite 4, Tailwind CSS 3.3, Flowbite 2.2 |
| Testing | Pest 4, PHPUnit 12 |
| Code style | Laravel Pint |

## 3. Architecture

The application follows a conventional Laravel monolith structure:

- Controllers handle public HTTP entry points.
- Eloquent models own relationships and presentation-oriented domain helpers.
- Livewire components handle filtering, listing, navigation, reactions, and footer state.
- Filament resources and pages provide the administrative and artisan workspaces.
- Laravel AI agents and tools provide the assistant; a deterministic local concierge provides fallback behaviour.
- Blade views render the public site and Filament page shells.

```text
Browser
  |
  +-- Web routes (routes/web.php)
  |     +-- Controllers -> Eloquent -> MySQL
  |     +-- Livewire components -> Blade views
  |     +-- SSE assistant endpoint -> ChatResponder -> Gemini/Groq or LocalConcierge
  |
  +-- Filament panels
        +-- /admin   -> AdminPanelProvider  (user_type = 1)
        +-- /artisan -> ArtisanPanelProvider (user_type = 2 or 3)
```

## 4. Directory Structure

Key application paths:

- `app/Http/Controllers` - public controllers and authentication callbacks.
- `app/Http/Middleware` - authentication redirects and filtered-page guard.
- `app/Livewire` - public interactive components.
- `app/Models` - domain models and query helpers.
- `app/Filament` - admin and artisan panel pages, resources, and widgets.
- `app/Ai` - assistant agent, tools, responder, and local fallback.
- `resources/views` - public Blade views, Livewire views, emails, and Filament page views.
- `database/migrations` - schema history.
- `database/seeders` - demo content and location data.
- `tests/Feature` and `tests/Unit` - application tests.
- `routes/web.php` - public, authenticated, contact, and assistant routes.
- `routes/api.php` - Sanctum-protected user endpoint.

## 5. User Roles and Authorization

`users.user_type` is an integer role discriminator:

| Value | Role | Panel access |
| --- | --- | --- |
| `0` | General user | None |
| `1` | Administrator | `/admin` |
| `2` | Artisan | `/artisan` |
| `3` | Both artisan and general user | `/artisan` |

Authorization for panels is implemented by `User::canAccessPanel()`:

- Admin panel requires `user_type === 1`.
- Artisan panel requires `user_type === 2` or `3`.

Authentication is session-based. Passwords are hashed via the `hashed` cast. Google login is handled by `GoogleSocialiteController`:

- Existing accounts are matched by `social_id`.
- New Google accounts are created with `user_type = 3` and a verified email.
- Existing Google accounts with `user_type = 0` are upgraded to `3`.
- Deliberate admin and artisan assignments are preserved.

Routes for artisan profiles, enquiries, notifications, and the dashboard require both `auth` and `verified` middleware. Email verification is required by route middleware even though `MustVerifyEmail` is currently commented out on the `User` model.

## 6. Public User Flows

### 6.1 Landing and filtering

- `GET /` renders the landing page with a country/category filter.
- `FilterSection` loads countries through `Country::ordered()` and categories alphabetically.
- Authenticated filter choices are persisted to `filter_values`; guest choices are stored in the session.
- Selecting a category immediately persists the filter and redirects to the shareable listing URL.
- Changing the country clears the selected category.

### 6.2 Artisan listing

- `GET /artisans/{country}/{category}` renders the listing.
- Country resolution tries `countries.slug`, then slugified country name, then exact name.
- `FilteredArtisan` searches name, address, and profession.
- Sorting options:
  - Yaara Recommended: likes descending, experience descending, oldest first.
  - Price high to low.
  - Price low to high with null prices last.
  - Satisfaction high to low.
  - Satisfaction low to high.
- Satisfaction is calculated as likes divided by total reactions.
- `GET /filtered-artisans` is a legacy session-based route guarded by `restrict.filtered.page`; it redirects to the canonical listing URL with HTTP 301.

### 6.3 Artisan profile

- `GET /artisan/{artisan}/{slug?}` requires authentication and verification.
- A missing or incorrect slug redirects to the canonical slug URL with HTTP 301.
- `GET /artisan-detail/{id}` is a legacy URL that redirects to the canonical profile.
- The profile displays biography, skills, languages, services, contact actions, reactions, and an enquiry form.
- Inactive artisans (`artisans.is_active = false`) cannot receive calls; the call control is disabled and an Alpine toast explains that the artisan is not active.
- Visitors can submit an enquiry through `POST /artisan/{artisan}/query`; the phone and query are validated and stored in `artisan_queries`.

### 6.4 Notifications and enquiries

- `GET /notifications` requires authentication and verification.
- Artisans see enquiries sent to their profile and unread enquiries are marked read.
- Non-artisan users see enquiries they have sent to artisans.
- The footer exposes the notifications link only when relevant and displays an unread count for artisans.

### 6.5 Other public pages

- `GET /page/{slug}` renders managed pages such as About, Privacy Policy, and Terms of Use.
- `GET /contact` renders the contact form.
- `POST /contact` validates the form and emails the configured sender address through `ContactMail`.
- Google OAuth routes are `GET /auth/google` and `GET /callback/google`.

## 7. AI Concierge

### 7.1 Endpoint behaviour

- `POST /assistant/stream` is throttled to 30 requests per minute.
- `POST /assistant/clear` clears the assistant conversation from the session.
- The assistant is available to guests and does not require authentication.
- Conversation history is stored in the session as `assistant_chat`, capped at the latest 30 messages.
- The latest 12 messages are sent to the model.
- The reply is generated before the streamed response begins so the session is persisted reliably.
- The response is emitted as Server-Sent Events with `token` events followed by a final `done` event containing the message and artisan cards.

### 7.2 AI execution path

`ChatResponder::respond()` follows this order:

1. If a Gemini or Groq key is configured, prompt the `MercibuddyAssistant` agent.
2. On Gemini rate limiting, retry with `gemini-flash-latest`.
3. On further rate limiting, fall back to Groq when configured.
4. On any other exception or an empty reply, use `LocalConcierge`.
5. If no AI credentials are configured, use `LocalConcierge` immediately.

The agent is configured as:

- Provider: Gemini
- Model: `gemini-3.5-flash-lite`
- Temperature: 0.8
- Max tokens: 800
- Max steps: 8
- Timeout: 90 seconds

Structured output:

```json
{
  "message": "string",
  "suggested_artisan_ids": [1, 2, 3]
}
```

### 7.3 Tools and grounding

- `FindMatchingArtisans` searches the live database by name, profession, profession type, address, biography, category, country, state, and city. It returns real records only, limited to five.
- `GetServiceCatalog` returns current categories and the full country/state/city tree.
- The agent instructions restrict it to MerciBuddy topics, require tool use before recommendations, and forbid inventing artisans, locations, prices, or availability.
- `LocalConcierge` provides deterministic keyword-based intent detection, location-only browsing, service-only clarification, combined searches, greetings, platform explanations, and honest no-match responses.

## 8. Filament Panels

### 8.1 Admin panel (`/admin`)

Provider: `AdminPanelProvider`.

Features:

- Primary color: amber.
- SPA navigation enabled.
- Login enabled.
- Custom footer rendered at `panels::body.end`.
- Dashboard with account and Filament info widgets.

Managed resources:

- Users
- Artisans
- Categories
- Pages
- Countries, states, and cities under a Location navigation group
- Site settings page

Admin user role options in `UserResource` are General, Skilled Artisan, and Both. Administrator accounts are not created through that resource.

Country tables default to sorting by name.

### 8.2 Artisan panel (`/artisan`)

Provider: `ArtisanPanelProvider`.

Features:

- Primary color: blue.
- SPA navigation enabled.
- Login and email verification enabled.
- Home navigation item linking to `/`.
- Dashboard with account widget and reaction statistics.

Artisan workspace:

- **Artisan profile**: cover photo, profile photo, name, category, profession, skills, price, currency, rate type, active status, date of birth, country/state/city cascade, address, language proficiencies, and biography.
- **Contact media**: phone and email.
- **Services**: create, edit, list, and bulk delete services. Queries are scoped to the authenticated user.
- **Stats overview**: likes and dislikes for the current artisan profile.

## 9. Data Model

### 9.1 Core entities

```text
users
  1 -- 1 artisans
  1 -- 1 contact_details
  1 -- * services
  1 -- * artisan_queries
  1 -- * comments
  1 -- 0..1 filter_values
  * -- * artisans (artisan_user, reaction: like|dislike)

countries
  1 -- * states
  1 -- * artisans

states
  1 -- * cities
  1 -- * artisans

cities
  1 -- * artisans

categories
  1 -- * artisans

artisans
  1 -- * artisan_queries
  1 -- * comments
```

### 9.2 Table summary

| Table | Purpose |
| --- | --- |
| `users` | Accounts, role discriminator, Google identity, email verification |
| `artisans` | Public artisan profile, skills, pricing, status, location, languages |
| `categories` | Service categories, slug, SVG icon, profession options |
| `countries` | Country name, ISO short name, slug, phone code |
| `states` | States belonging to countries |
| `cities` | Cities belonging to states |
| `contact_details` | Per-user phone, email, and social contacts |
| `services` | Per-user services with title, slug, image, and content |
| `artisan_queries` | Enquiries sent to an artisan, with read state |
| `artisan_user` | Like/dislike pivot between users and artisans |
| `comments` | Comments left on artisans |
| `filter_values` | Persisted country/category choices for authenticated users |
| `settings` | Single-row site identity, hero copy, SEO, and copyright |
| `pages` | Managed CMS pages |
| `agent_conversations`, `agent_conversation_messages` | Laravel AI conversation storage |
| `personal_access_tokens` | Sanctum API tokens |
| `password_reset_tokens` | Password reset tokens |
| `failed_jobs` | Failed queue jobs |

### 9.3 Important model behaviour

`Artisan`:

- Casts `hourly_rate` to two-decimal string, `is_active` to boolean, and `skills` / `language_proficiencies` to arrays.
- Stores 155 ISO currency codes and symbols.
- Produces price labels such as `EUR 50/day`, `CHF 90/session`, `Free`, and `Price on request`.
- Maps rate types to suffixes such as `/hour`, `/day`, `/visit`, `/session`, `/project`, `/km`, `/week`, and `/month`.
- Exposes location, experience, like/dislike counts, and satisfaction percentage helpers.

`Country`:

- `ordered()` always sorts alphabetically by name.
- Resolves locations through slug-first URL handling.

`Category`:

- Combines stored profession options, configured options from `config/professions.php`, and professions already used by artisans.

## 10. Routes

| Method | URI | Auth | Purpose |
| --- | --- | --- | --- |
| GET | `/` | No | Landing page |
| GET | `/artisans/{country}/{category}` | No | Artisan listing |
| GET | `/filtered-artisans` | Conditional | Legacy filtered listing redirect |
| GET | `/artisan/{artisan}/{slug?}` | Yes + verified | Artisan profile |
| POST | `/artisan/{artisan}/query` | Yes + verified | Submit enquiry |
| GET | `/notifications` | Yes + verified | Notifications |
| GET | `/artisan-detail/{id}` | Yes + verified | Legacy profile redirect |
| GET | `/profile` | Yes + verified | Dashboard |
| GET | `/page/{slug}` | No | Managed page |
| POST | `/assistant/stream` | No | AI reply stream |
| POST | `/assistant/clear` | No | Clear AI chat |
| GET/POST | `/contact` | No | Contact form and mail |
| GET | `/auth/google` | No | Start Google OAuth |
| GET | `/callback/google` | No | Google OAuth callback |
| POST | `/user` | No | Demo JSON endpoint |
| GET | `/api/user` | Sanctum | Current API user |

Authentication routes are provided by `routes/auth.php`.

## 11. UI and Content Conventions

- Public layout: `resources/views/layouts/landing.blade.php`.
- Listing view: `resources/views/livewire/filtered-artisan.blade.php`.
- Profile view: `resources/views/artisan-detail.blade.php`.
- Tailwind is configured with the Inter font stack, Flowbite plugin, forms plugin, and class-based dark mode.
- Public navigation and footer are Livewire components.
- Country and category selections are shareable through URL parameters.
- Price displays use the ISO currency code followed by the amount and rate suffix, without a space before the slash.
- Countries are alphabetically ordered in all selector and listing contexts.
- Inactive artisan profiles remain publicly viewable but cannot receive calls.
- Empty service lists render an explicit "No service details added!" state rather than an empty section.

## 12. Configuration and Environment

Relevant environment variables:

- Application: `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_URL`, `APP_DEBUG`.
- Database: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
- Mail: `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_FROM_ADDRESS`.
- Google OAuth: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`.
- AI providers: `GEMINI_API_KEY`, `GROQ_API_KEY`, plus optional provider keys in `config/ai.php`.

Defaults in the example environment use MySQL, file cache, file sessions, synchronous queues, and SMTP/Mailpit.

## 13. Setup and Build

Local development is served by Laravel Herd at `http://mercibuddy-ai.test`.

Typical setup:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
```

Development asset server:

```bash
npm run dev
```

Optional full development process:

```bash
composer run dev
```

There is no committed Docker, CI, or deployment pipeline. Production deployment must provide PHP 8.4, MySQL, writable storage and cache directories, a web root pointed at `public`, and built Vite assets.

## 14. Testing

- Test runner: Pest 4 on PHPUnit 12.
- Configuration: in-memory SQLite, array cache, array session, array mail, synchronous queue.
- Main test areas:
  - Artisan routes, ordering, profile redirects, and pricing.
  - Artisan query submission.
  - Filter persistence.
  - Assistant concierge and streaming endpoint.
  - Category professions.
  - Contact form and mail.
  - Google login.
  - Header, footer/share menu, profile, logout, and panel access.
  - Breeze authentication, verification, password reset, and password update.
  - Discount price calculation.
- Commands:

```bash
php artisan test --compact
vendor/bin/pest
vendor/bin/pint --dirty --format agent
```

## 15. Known Constraints and Technical Debt

- `AdminSeeder` still inserts a legacy `is_admin` column that does not exist; `DatabaseSeeder` is the working seed path.
- `MustVerifyEmail` is commented out on `User`, while authenticated routes use the `verified` middleware.
- `artisan_user` has no unique constraint on `(user_id, artisan_id)`; uniqueness is enforced only by application logic.
- Contact mail uses the submitter's address as the `From` address, which can cause deliverability issues with strict SMTP providers.
- The assistant is session-based, unauthenticated, and intentionally limited to MerciBuddy topics.
- The public listing does not filter inactive artisans; only the call action is disabled.
- The full test suite has a known isolation issue in `WorkspaceAccessTest` where running the file alone can fail on a missing `seedRouteDirectory()` helper, while targeted tests pass.
- `routes/web.php` includes a demo `POST /user` endpoint that is not part of the core product.
