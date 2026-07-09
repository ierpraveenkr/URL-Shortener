# URL Shortener

A role-based URL shortener service built with **PHP Laravel 8** and **SQLite**. Companies have multiple users with strict role-based access control over who can shorten URLs and invite new members.

---

## Features

- **5 Roles**: SuperAdmin, Admin, Member, Sales, Manager
- **Invitation-based onboarding** — no open public registration; users join via email invite links
- **Role-based URL visibility**:
  - SuperAdmin sees all URLs across every company (cannot create URLs)
  - Admin sees all URLs created within their own company
  - Member sees only the URLs they created themselves
- **Short URL redirect** — anyone with a short link is redirected to the original URL
- **Paginated URL list** — 5 results per page
- **Duplicate prevention** — submitting the same URL twice reuses the existing short code

---

## Requirements

- PHP 7.4+
- Composer
- SQLite (bundled with PHP — no separate installation needed)

---

## Local Setup

### 1. Clone the repository

```bash
git clone <your-repo-url>
cd url_shortner
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and make sure the database section looks like this:

```env
DB_CONNECTION=sqlite
```

All other DB lines (`DB_HOST`, `DB_PORT`, etc.) can be removed or left as-is — they are ignored when using SQLite.

Mail is set to `log` driver so no email server is needed:

```env
MAIL_MAILER=log
```

### 4. Create the SQLite database file

```bash
touch database/database.sqlite
```

### 5. Run migrations and seed the database

```bash
php artisan migrate --seed
```

This will:
- Create all database tables
- Seed the **5 roles** (SuperAdmin, Admin, Member, Sales, Manager)
- Create the **SuperAdmin account** (via raw SQL as required)

> **SuperAdmin credentials:**
> - Email: `superadmin@example.com`
> - Password: `password`

### 6. (Optional) Seed demo data for testing

To populate 3 companies, 6 users, and 12 short URLs for testing:

```bash
php artisan db:seed --class=DummyDataSeeder
```

**Demo accounts created (password: `password`):**

| Email | Role | Company |
|---|---|---|
| `superadmin@example.com` | SuperAdmin | — |
| `admin1@example.com` | Admin | Test Company 1 |
| `admin2@example.com` | Admin | Test Company 2 |
| `admin3@example.com` | Admin | Test Company 3 |
| `member1@example.com` | Member | Test Company 1 |
| `member2@example.com` | Member | Test Company 2 |
| `member3@example.com` | Member | Test Company 3 |

### 7. Start the development server

```bash
php artisan serve
```

Visit: [http://127.0.0.1:8000](http://127.0.0.1:8000)

---

## How the Invitation Flow Works

Since there is no open registration, new users are invited via a unique link:

1. **SuperAdmin** logs in → fills in "New Company Name" + invitee email → clicks **Send Invitation**
2. **Admin** logs in → fills in invitee email + selects role (Admin or Member) → clicks **Send Invitation**
3. The app sends an email with a unique invite link to `storage/logs/laravel.log` (no SMTP needed locally)
4. The invited user opens the link (e.g. `http://127.0.0.1:8000/invite/{token}`), sets their name and password, and their account is created automatically

---

## Database Schema

| Table | Purpose |
|---|---|
| `roles` | Stores the 5 role types |
| `companies` | One record per company |
| `users` | Each user belongs to a role and optionally a company |
| `invitations` | Pending/accepted invite tokens |
| `short_urls` | Maps a `short_code` to an `original_url` per company |

---

## Tech Stack

- **Framework**: Laravel 8 (PHP 7.4)
- **Database**: SQLite
- **Auth**: Laravel session-based auth (no packages)
- **CSS**: Vanilla CSS with glassmorphism dark theme
- **Templating**: Laravel Blade
