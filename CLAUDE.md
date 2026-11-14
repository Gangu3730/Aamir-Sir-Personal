# Aamir Sheeraz - website + private community (Laravel)

Public site (design copied 1:1 from the old WordPress/Elementor site) plus a curated, members-only community.
Stack: Laravel, Blade, MySQL, plain CSS/JS (no build step - safe for Hostinger shared hosting).

## How the pieces fit
- **Public pages are CMS content**: `pages` table (Home, About Me, Professional). Raw Elementor markup is stored in `pages.content`
  and rendered by `resources/views/site/page.blade.php`. Header/footer/popup fragments live in `settings` (`part_header`, `part_footer`, `part_popup`, `part_auth_intro`).
  `[[menu]]` inside a nav `<ul>` is replaced by the live menu (`menu_items`). Edit via `/admin/pages` (super admin).
- **Mirrored design assets** live in `public/site/**` (CSS, fonts, images). Never hotlink the old WordPress host. Re-mirror with `python tools/mirror.py app`.
- **Flow**: `/registration` -> pending user -> admin approves in `/admin/applications` -> email with expiring password-setup link -> `/login` -> `/community`.
- **Roles**: `admin` (Aamir), `moderator`, `member`. Status: `pending|approved|rejected|suspended`. Only `approved` users can log in.
- **Community**: `posts` (+ `comments` threaded via `parent_id`, `saved_posts`, `reports`, `notifications`). Always `noindex` via middleware `member`/`role`.
- **Settings** (`/admin/settings`): member posts on/off, moderation of member posts, rejection email, menu.

## Operations Claude may perform (use these, do not hand-write SQL)
| Request | Command |
|---|---|
| Draft a post (never published automatically) | `php artisan community:draft "Title" --file=draft.html --type=discussion` |
| Publish an approved draft + alert members | `php artisan community:publish <slug>` |
| Weekly themes | `php artisan community:digest --days=7` then summarise |
| List / approve / reject applicants | `php artisan member:review list` / `approve <email>` / `reject <email>` |
| Change public page text/CTA | edit the page via admin or `Page::where('slug',...)->update(...)` in tinker, then clear cache |

## Rules (guardrails)
1. **Content** (drafts, publishing) may be done directly when Aamir asks.
2. **Code changes** go through Git on a branch; run `php artisan test` first; deploy only when Aamir says **"deploy"**.
3. Before any migration or deploy: take a DB backup (`mysqldump`) and note the Git commit for rollback.
4. Never email or print passwords, never commit `.env`, never disable `noindex` on community/admin routes.
5. Add fields by: migration + `$fillable`/validation + form view + admin view + a test.
6. Do not change the visual design of public pages unless explicitly requested.

## Commands
```
php artisan serve            # local server
php artisan migrate --seed   # schema + CMS content (database/seed/site.json)
php artisan test             # feature tests (needs MySQL db aamir_site_test)
```
