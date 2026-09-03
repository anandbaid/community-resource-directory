# Architecture

Community Resource Directory is a Laravel 10 application with a **Vue 3** front end. This
document explains how the two fit together, why the front end uses two different
Vue patterns rather than one, and what is deliberately still rendered by Blade.

---

## The shape of the thing

```
Laravel 10 (PHP 8.1+)
├── Inertia.js v1  ──► Vue 3 pages          for everything behind a login
└── Blade          ──► Vue 3 islands        for everything Google indexes
```

Both halves are one Vite build, one Vue version, one component library. What
differs is only *who owns the page shell*.

| | Inertia pages | Vue islands |
|---|---|---|
| Entry point | `resources/js/lib/inertiaApp.js` | `resources/js/site.js` |
| Page HTML | An empty `<div id="app">` | Fully server rendered |
| Vue owns | The whole page body | One widget at a time |
| Used for | All of `/admin`, the account area, auth | Home, directory, library, marketing |
| Why | No SEO requirement; forms and CRUD benefit most from a single client-side form/validation model | The HTML *is* the product for a crawler; a blank div would remove the site from search |

### Why not Inertia everywhere

Inertia without a server-side rendering process ships an empty `<div>` and fills
it in the browser. That is fine for `/admin/*` and `/dashboard`, which no one
should be indexing. It is not fine for `/search-resources`, which is the crawl
path to every one of the `/organization-details/{id}` pages, or for `/library`,
which is the crawl path to every publication. Inertia SSR needs a long-running
Node process next to PHP, which this app's hosting does not provide.

So the public pages keep their server-rendered HTML and mount Vue *into* it.

### Why not islands everywhere

The admin forms are the opposite case. `backend/organizations/create.blade.php`
was 807 lines of markup plus a jQuery validation config, and `edit` was another
886 lines that had drifted out of sync with it. As an Inertia page the two share
one `OrganizationForm.vue`, one validation contract with the server, and one set
of error bindings. There is nothing to index and everything to deduplicate.

---

## Inertia pages

**Root views.** `App\Http\Middleware\HandleInertiaRequests::rootView()` picks the
Blade shell per area:

```php
return $request->is('admin', 'admin/*')
    ? 'backend.layouts.inertia'
    : 'frontend.layouts.inertia';
```

Each shell `@include`s the *existing* chrome partials (head, header, sidebar,
footer, foot) and drops `@inertia` where the content used to go. Converted and
unconverted screens are therefore indistinguishable to a user — the nav, the
flash toasts and the asset stack are literally the same files.

**Shared props.** The same middleware shares `auth.user` and a `flash` bag whose
keys mirror `resources/views/common/flash.blade.php`, so a redirect-with-flash
raises the same SweetAlert whether it lands on a Blade page or a Vue one.

**Handing off to Blade.** Several converted actions redirect to pages that are
still Blade (`/home`, the admin dashboard). Inertia would follow such a redirect
with an XHR and then fail to resolve a component, so those return
`Inertia::location($url)` — a 409 with an `X-Inertia-Location` header that tells
the client to do a real browser navigation. Note that `Inertia::location()`
returns a plain `Response`, not a `RedirectResponse`, so it has no `->with()`;
flash first with `session()->flash(...)`, then return it.

**Layout background.** `frontend.layouts.inertia` reads a `lightBack` page prop
to decide whether the page sits on the light account background or the dark
banner, because the Blade `@section` has to be set before the header include.

**The signed-out admin shell.** `/admin/login` uses
`backend.layouts.inertia-blank`, the same root view without the header and
sidebar — those are built from the authenticated user and have nothing to show
to a visitor who has not signed in yet.

## Vue islands

An island is a placeholder element carrying its props as JSON:

```blade
<div data-vue-island="resource-map" data-vue-props="{{ json_encode([
    'apiKey'     => config('custom.map_api_key'),
    'locations'  => $location_array,
    'detailsUrl' => url('/organization-details'),
]) }}"></div>
```

`resources/js/site.js` walks `[data-vue-island]`, looks the name up in a registry
of dynamic imports, and mounts. Each island is its own lazily-loaded chunk, so a
page pays only for the widgets it actually uses.

Two details worth knowing:

- `resources/css/islands.css` sets `[data-vue-island] { display: contents }` so
  the placeholder is layout-transparent. The component's root element lands in
  exactly the flex/grid slot the original markup occupied, and no existing CSS
  had to change.
- Inertia's ~97 kB runtime is only imported when `#app[data-page]` exists. A
  marketing page never downloads it.

### The island registry

| Island | Where | Replaces |
|---|---|---|
| `resource-search-form` | home, `/search-resources` | Two near-duplicate search forms |
| `resource-map` | home, `/search-resources`, org details | Three copies of a 111-line Google Maps bootstrap |
| `search-result-actions` | `/search-resources` | Sort select + save-search AJAX |
| `save-resource-toggle` | `/search-resources`, org details | Heart toggle AJAX |
| `publication-grid` | org details | jQuery that rebuilt cards from HTML strings |
| `report-spam-modal` | org details | Modal + jQuery-validate + AJAX |
| `share-modal` | library, org details, career | jQuery poking values into the modal by element id |
| `library-filters` | `/library` | select2 + a hidden input + form submit |
| `contact-form` | `/contact-us` | jQuery-validate + FormData AJAX |
| `career-hub` | `/career-success-hub` | 212 lines driving the segment wheel, details card and topic modal |
| `team-member-modal` | `/about-us` | `getElementById` writes into a modal with duplicated ids |

### One organization form, four screens

`Components/Organizations/OrganizationForm.vue` backs admin **create**, admin
**edit**, the **read-only view** and the **suggested-organization review**
screen. Those were four Blade files totalling ~2,975 lines that had drifted
apart from each other. The component takes the differences as seams rather than
forking:

- `mode` — `create` | `edit` | `review`; the view screen is `edit` + `readonly`
- `readonly` — one `disabled` on a wrapping `<fieldset>` takes every control
  with it, and disabled controls are skipped by constraint validation
- a `#publications` slot — admin assigns existing publications, review edits the
  rows the visitor submitted (`Components/Publications/PublicationRows.vue`,
  itself shared with the public suggest form via a `variant` prop)
- an `#actions` slot — Save, or Accept / Reject
- a `transform` prop, applied on top of the component's own, so a host page can
  fold in fields it owns without re-implementing the submit

One field genuinely differs and is not shared: `file_url` is an upload on the
admin form and a link the visitor typed on a suggestion, so it renders and
validates differently in `review` mode.

### The page builder

`/admin/static-pages` builds page bodies with **GrapesJS**. The create and edit
Blade views each carried their own 135-line copy of its configuration —
identical apart from indentation, which is exactly the sort of pair that drifts
silently. That config now lives once in `resources/js/lib/pageBuilder.js`, and
`Components/Admin/PageBuilder.vue` mounts it.

GrapesJS deliberately stays on its CDN rather than going through Vite: it is a
large editor used on two admin screens, and bundling it would drag it into the
shared chunk graph for no benefit. `lib/loadScript.js` loads it once per page.

The builder is read at submit time via `defineExpose`, not mirrored into
reactive state — it fires change events constantly and the form only needs the
final document.

Static pages come in three shapes, and the controller picks between them:

| Shape | Editor |
|---|---|
| Normal CMS page | `StaticPages/Form.vue` with the page builder |
| Legacy page (`about-us`, `contact-us`, …) | the same form, `isLegacy`: rich-text blocks and repeatable items, no routing fields or builder |
| `career-success-hub` | `StaticPages/Career.vue`, four fixed segments each owning ordered topics |

Legacy pages have hand-written Blade templates keyed on their slug, so their
slug, menu placement and body are not the admin's to change — and they cannot be
deleted. The index renders that as a disabled button with a reason rather than
no button at all.

### Tables render from data, not HTML strings

Two admin lists — organizations and saved searches — were server-side
DataTables. Their endpoints did not return records; they returned **markup**:

```php
'actions' => '<a href="…" class="btn btn-primary"><i class="fa-solid fa-eye"></i></a>
    <button onclick="statusUpdate($(this))" data-url="…">…</button>',
'select'  => '<input type="checkbox" class="org-select" value="' . $id . '">',
```

Every cell was concatenated on the server, some of it carrying inline `onclick`
handlers that called globals defined in a `@push`ed script block. Both endpoints
are gone, along with their routes. The controllers paginate and hand over data;
`Components/Admin/DataTable.vue` renders it.

`DataTable` covers both modes:

- **client mode** — pass `searchKeys`; it filters and pages the rows it was given
- **server mode** — pass `paginator` (Laravel's payload) and `serverSearch`; the
  search box issues a debounced Inertia visit instead of filtering locally,
  because filtering the ten rows on the current page would search ten records
  and call it a result set

### Shared state between islands

Islands are separate Vue apps and cannot pass props to each other. Where two
need to talk — a share button inside `publication-grid` opening `share-modal` —
they import a small reactive store, `resources/js/lib/share.js`. Vite hoists it
into a chunk both import, so both see the same module instance.

`share-modal` additionally delegates clicks on any `.share-trigger[data-url]`
element, which is how the still-Blade library pages open it without changes, and
exposes `window.openShare()` for the career pages that compute their links in an
inline script.

---

## Conventions

**Forms.** Inertia pages use `useForm` and post to a controller that calls
`$request->validate()`. Errors come back in the standard error bag and bind to
`FieldError.vue`. Islands post with axios and read Laravel's 422 JSON shape.
The rule is that the server validates and the client only mirrors — no
validation rule is expressed solely in JavaScript.

**reCAPTCHA.** `resources/js/lib/recaptcha.js` mints a v3 token **at submit
time**. The Blade pages got theirs from a `DOMContentLoaded` handler that walked
the DOM appending a hidden input to every `<form>`, which fails twice over for
Vue: the forms do not exist when it runs, and v3 tokens expire about two minutes
after minting, so a token taken at page load is stale before anyone finishes a
long form.

**Phone numbers.** `PhoneInput.vue` displays a mask and emits bare digits, which
is what the server validates and stores. There is no formatting logic in the
controllers.

**Global scripts stay global.** AdminLTE, Bootstrap, jQuery, DataTables,
SweetAlert2 and CKEditor are loaded from `public/` by the existing layout
partials and used through `window.*`. Re-bundling them through Vite would have
meant re-testing every unconverted screen for no benefit. Vue components call
`window.bootstrap.Modal`, `window.Swal` and `window.printFile` directly.

---

## What is still Blade, and why

Not everything should be Vue. These are deliberate:

- **`frontend/includes/header.blade.php`** (332 lines) is the site's link graph —
  a mega-menu built from CMS rows. Rendering it client-side would hide every
  internal link from a crawler for zero user benefit. It carries no inline JS;
  its behaviour lives in `public/assets/js/script.js`.
- **`about`, `partners`, `support-us`, `legal`, `dynamic`** are CMS-authored
  prose. There is nothing to make interactive.
- **Publication and organization listings** are server rendered on purpose, per
  the SEO reasoning above. The filters around them are Vue; the results are not.
- **The admin chrome** — `backend/includes/{head,header,sidebar,footer,foot}` —
  is included by the Inertia root views. Converting it would mean re-rendering
  the same nav inside every page component for nothing.
- **`pdf/saved-search-pdf.blade.php`** is a dompdf template. It is never sent to
  a browser.
- **`emails/mail.blade.php`** and **`common/flash.blade.php`** are a mail
  template and the toast partial the Vue flash bag mirrors.

Every *screen* in `/admin` is a Vue page. What is left under
`resources/views/backend/` is chrome and layouts.

---

## Building and testing

```bash
npm install && npm run build     # or: npm run dev
php artisan test
```

Feature tests cover each converted area — `OrganizationInertiaSmokeTest`,
`AdminCrudInertiaTest`, `AccountVueSmokeTest`, `SuggestResourceVueTest`,
`OrganizationDetailsVueTest`, `AuthVueTest`, `FrontendVueSmokeTest`,
`PublicPagesVueTest`, `PublicRouteSmokeTest`, `SuggestedOrganizationReviewTest`,
`StaticPagesAdminVueTest`, `AdminChromeVueTest`, `AdminIndexesVueTest`,
`OrganizationsIndexVueTest`. They assert the rendered component name for Inertia pages
and the `data-vue-props` payload for islands, so the page's contract with Vue is
checked from the server side. Every test cleans up after itself, and each one
seeds the rows it needs rather than reaching for whatever the seeder left behind
— a test that skips on an empty table reports green without having run. The
suite is **160 tests, 0 skipped**.

---

## Known issues, not introduced by the Vue work

- **`laravel_wp_auth` carries a shared secret in cleartext.**
  `App\Http\Middleware\EncryptCookies` exempts it from encryption, and
  `UserLoginController::setWordPressAuthCookies()` writes
  `"$user_name|$expiration|$secret_key"` where the key is
  `API_VERIFICATION_TOKEN`. Anything with DOM access can read the secret the
  WordPress side uses to trust a session, and then forge a cookie for any
  username. The fix is an HMAC over the username and expiry instead of the raw
  key — but the WordPress plugin that parses this cookie is not in this repo, so
  the two have to change together.
- **Registration emails a generated password in plaintext** and never requires a
  change on first login.
- **Laravel 10 is end-of-life.** Inertia is pinned to v1.3 for the same reason —
  v2 requires Laravel 11. The upgrade path is Laravel 10 → 11 → 12, then Inertia
  v2.

## Bugs found and fixed during the conversion

Reading each screen closely turned up defects that predate this work:

1. Editing a user through the admin **wiped their password** — the form posted a
   blank `password` that was hashed and saved unconditionally.
2. `email_templates.status` defaulted to `'Active'`, which is not a member of its
   own `enum('active','inactive')`, so inserts failed on strict MySQL.
3. The banner admin crashed on a missing page title.
4. Saved searches could be **downloaded and deleted by id without an ownership
   check** (IDOR), and the download path was built from unsanitised input.
5. The suggest-a-resource form followed a posted `redirect` field verbatim —
   an open redirect.
6. **The login form did the same**, on the endpoint that hands out an
   authenticated session.
7. **Password reset tokens never expired.** `created_at` was written and never
   read, so a link that leaked out of an inbox worked forever. Now one hour, and
   an expired token is deleted on use.
8. **The forgot-password form was an account oracle**: `exists:users,email`
   meant a known address and an unknown one produced different responses. It
   also 500'd for a user whose status was not `active`, dereferencing null.
9. The contact form validated `email` as `required` with **no format rule**, so
   unreplyable addresses reached the admin's queue.
10. The contact notification email **interpolated the submitted message into HTML
    unescaped** — markup the admin's mail client would render.
11. Google Maps marker icons were requested over **`http://`**, which browsers
    block as mixed content on an https page.
12. Five public pages 500'd if their CMS row was missing or renamed; only
    `privacyPolicy()` had guarded against it.
13. The `/about-us` team modal had **duplicate element ids** (`popupTitle`,
    `popupSubtitle`), so the title was written into the hidden copy.
14. `RecaptchaProtection` answered every rejection with a redirect, which an XHR
    caller reads as a 200 full of HTML, and dereferenced a null response body if
    the call to Google failed.
15. **`saveSettings` wrote any `key[...]` name it was posted**, so a crafted
    request could create or overwrite any row in the settings table — including
    `asset_version`, which the cache-busting logic reads. The allowed names are
    a whitelist now, and `admin_email` (which the contact form mails) is
    validated as an address.
16. **The admin login had the same open redirect as the public one**, on the
    endpoint that hands out an administrator session. Both now share one
    `RedirectsSafely` trait.
17. **The settings form's HQ and mailing lists shared their container ids.**
    `id="footer-hq-phones"` appeared twice, so "Add Phone" under Mailing
    appended a row to the *HQ* block — visually in the wrong section, and saved
    under the mailing key.
18. **Accepting a suggestion wiped four fields.** The review form hardcoded
    `value=""` on `additional_resource`, `title`, `description` and `file_url`,
    and the accept path assigns them straight onto the organization — so
    approving an update blanked whatever the organization already had. The
    suggestion had been storing all four correctly the whole time.
19. **A junk suggestion could not be rejected.** Validation ran *before* the
    accept/reject branch, so a suggestion with an invalid website or missing
    coordinates — exactly the ones worth rejecting — failed validation and could
    never be thrown out.
20. **A suggestion could be decided twice.** Nothing checked the current status
    before applying a decision, so a re-post of the accept form created a second
    organization.
21. Accepting an update for an organization with no `point_of_contacts` or
    `organization_details` row dereferenced null, and the publication loop read
    `$request->cover_file_path[$key]` without checking the array was sent.
22. A missing email template made the whole review action fail *after* the
    decision was already saved, so the admin saw an error for work that had
    succeeded.
23. **The bulk importer's file rule was `nullable`**, and the next line
    dereferenced the upload — posting the form with no file was a 500. Reading
    the "last import" report also called `file_get_contents()` on a path that
    does not exist on a fresh install; `?? ''` never caught it, because that
    function returns `false`, not null.
24. **`spam_report()` read the organization id off the last URL segment** rather
    than its route parameter, so the unfiltered `/admin/spam-report` filtered on
    the literal string `"spam-report"` and only worked by accident.
25. `updateStatus` accepted any status string and only checked membership of
    `[active, inactive]` *after* running the ping validation, so an unknown
    value did the network work and then failed.
26. **The public organization page 500s for any organization without social
    links.** `organization-details.blade.php` ran
    `json_decode($organizationDetails->social_links, true)` and indexed
    `['facebook']` straight off the result. `social_links` is nullable and
    `json_decode` returns `null` for both an absent value and malformed JSON, so
    the page died on a normal record. It surfaced only when a test stopped
    reusing whatever the seeder had left behind and created its own
    organization the way the admin form does.
27. **The scheduled command could never run on a Linux server.** The directory
    was `app/Console/commands/` (lowercase), while `Kernel::commands()` loads
    `__DIR__.'/Commands'` and the class declares `namespace App\Console\Commands`.
    Case-insensitive filesystems — Windows, and macOS by default — hide this
    completely. On any Linux host `php artisan jobs:check` is
    *"There are no commands defined in the `jobs` namespace"*, so the
    every-minute scheduler entry fails silently forever. Renamed to `Commands`.
28. **Six `env()` calls sat outside `config/`**, in a job, a middleware, two
    controllers and a Blade view. `env()` returns `null` once `config:cache` has
    run, and caching config is the first thing any production deploy does — so
    `BLOG_URL` would render `href=""` in the header of every page,
    `KICKBOX_API_KEY` would silently disable email verification, and
    `API_VERIFICATION_TOKEN` would sign the WordPress cookie with an empty
    secret. All three moved into `config/services.php`.

29. **Two routes shared the name `admin.profile`** — the GET and the POST in
    `routes/admin.php`. Laravel tolerates that at registration but
    `php artisan route:cache` refuses outright, so the one command that most
    affects production routing performance could never run. Renamed the POST to
    `admin.profile.update`; both map to the same URI, so every existing
    `route('admin.profile')` still resolves.
30. **The lock file required PHP 8.4.1**, while `composer.json` advertised
    `^8.1`. `symfony/css-selector` had resolved to a release requiring
    `>= 8.4.1` because the lock was generated on a PHP 8.4 machine, so
    `vendor/composer/platform_check.php` would abort before Laravel booted on
    any host running 8.2 or 8.3. Pinned `config.platform.php` to `8.2.0` and
    re-resolved; the tree now installs and runs on 8.2, 8.3 or 8.4.

The last four are invisible on a development machine and certain to appear in
production — see `DEPLOYMENT.md`.

---

## Orphaned Blade views

These 55 views are no longer referenced by any controller, route or other view —
their screens are Vue now. They are **left in place on purpose**: this checkout
is not a git repository, so deleting them would be irreversible. Removing them
is safe whenever you are ready, and worth roughly **12,036 lines**.

Verify before deleting:

```bash
grep -rn "backend.organizations.create" app/ routes/ resources/views/
```

Note that `backend/layouts/app.blade.php` and `backend/layouts/blank.blade.php`
still show references — but only *from views on this list*. They go when the
views that extend them do.

**Frontend (14)**

```
frontend/auth/login.blade.php
frontend/auth/register.blade.php
frontend/auth/forgot-password.blade.php
frontend/auth/reset-password.blade.php
frontend/dashboard.blade.php
frontend/profile.blade.php
frontend/includes/sidebar.blade.php
frontend/layouts/blank.blade.php
frontend/organization/review.blade.php
frontend/organization/saved-resources.blade.php
frontend/organization/saved-search.blade.php
frontend/organization/suggest.blade.php
frontend/organization/new-suggested-fields.blade.php
frontend/organization/existing-suggested-fields.blade.php
```

**Admin (41)**

```
backend/index.blade.php
backend/settings.blade.php
backend/profile.blade.php
backend/auth/login.blade.php

backend/organizations/index.blade.php
backend/organizations/show.blade.php
backend/organizations/create.blade.php            (807 lines)
backend/organizations/edit.blade.php              (886 lines)
backend/organizations/bulk-imports.blade.php
backend/organizations/spam-report.blade.php

backend/suggestorganizations/index.blade.php
backend/suggestorganizations/edit.blade.php       (863 lines)

backend/staticpages/index.blade.php
backend/staticpages/create.blade.php
backend/staticpages/edit.blade.php                (562 lines)
backend/staticpages/edit-career.blade.php
backend/homepage/index.blade.php                  (519 lines)

backend/savedsearches/index.blade.php
backend/savedsearches/partials/actions.blade.php
backend/savedsearches/partials/user.blade.php
backend/library/index.blade.php
backend/resources/index.blade.php

backend/categories/index.blade.php
backend/categories/create.blade.php
backend/emailtemplates/index.blade.php
backend/emailtemplates/create.blade.php
backend/emailtemplates/edit.blade.php
backend/queries/index.blade.php
backend/queries/create.blade.php
backend/users/index.blade.php
backend/users/create.blade.php
backend/users/edit.blade.php
backend/users/show.blade.php
backend/banners/index.blade.php
backend/banners/create.blade.php
backend/banners/edit.blade.php
backend/reviews/index.blade.php
backend/reviews/show.blade.php
backend/publications/index.blade.php
backend/publications/create.blade.php
backend/publications/show.blade.php
```

**Layouts that go with them (2)**

`backend/layouts/app.blade.php` and `backend/layouts/blank.blade.php` are still
referenced — but only *by views on this list*. They go when those do.

**Not counted here**

`welcome.blade.php` is Laravel's stock landing page and was already unused
before this work. `vendor/pagination/bootstrap-5.blade.php` is a published
framework view that Laravel resolves by convention rather than by name, so no
grep will show a reference — leave it alone.
