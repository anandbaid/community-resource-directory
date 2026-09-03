# Community Resource Directory

A Laravel 10 + Vue 3 application for running a searchable directory of
community service providers: organisation records with custom fields and
points of contact, category and state filtering, user reviews, a publications
library, saved resources and saved searches with PDF export, and a
community-suggestion and spam-reporting workflow, all behind a full admin
back end.

**Stack:** Laravel 10 (PHP 8.1+) · Vue 3 · Inertia.js v1 · Blade · Vite · MySQL

---

## ⚠️ This is a reference copy — it will not run

Two things are deliberately withheld from this public repository:

- **The database layer.** No migrations, seeders or factories. There is no
  schema to create, so every query fails and there is no account to sign in
  with.
- **The public marketing pages.** The homepage, site chrome and static content
  templates are not included.

`composer install` will succeed and the code is complete enough to read, but
the application cannot be deployed. This is intentional — please don't open
issues about it.

## What is worth reading

[`ARCHITECTURE.md`](ARCHITECTURE.md) is the point of this repository. It
explains why the front end runs **two Vue patterns rather than one**:

```
Laravel 10 (PHP 8.1+)
├── Inertia.js v1  ──► Vue 3 pages          for everything behind a login
└── Blade          ──► Vue 3 islands        for everything a crawler indexes
```

Both halves are one Vite build, one Vue version, one component library — what
differs is only who owns the page shell. Inertia without a server-side
rendering process ships an empty `<div>`, which is fine for the admin area and
fatal for the pages that are the crawl path to every directory entry. Since
the target hosting could not run a Node process alongside PHP, the public
pages keep their server-rendered HTML and mount Vue *into* it, while the admin
forms — two templates of 807 and 886 lines that had drifted out of sync —
collapse into shared Inertia components with one validation contract.

Worth a look:

| Path | What's there |
|---|---|
| [`resources/js/Pages`](resources/js/Pages) | Inertia pages — the admin CRUD surface |
| [`resources/js/Islands`](resources/js/Islands) | Vue islands mounted into Blade |
| [`resources/js/lib`](resources/js/lib) | the Inertia bootstrap and shared helpers |
| [`app/Http/Middleware`](app/Http/Middleware) | root-view switching and shared props |

## Naming and content

This is a generalised copy of a client project. The organisation name,
branding, imagery, contact details and marketing copy have been replaced with
placeholders throughout. Any address, phone number or email in this tree is
fictional.

## Licence

See [LICENSE](LICENSE) — all rights reserved. This code is published for
reference only; reading it does not grant a licence to use it.
