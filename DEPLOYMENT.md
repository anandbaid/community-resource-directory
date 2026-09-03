# Deploying to Hostinger shared hosting

Written for Hostinger's shared plans (hPanel). The awkward part of any Laravel
deploy on shared hosting is that the web root is fixed at `public_html`, while
Laravel expects to serve from its own `public/` directory with everything else
above the web root. Step 6 is where that gets resolved; the rest is ordinary.

**Check first:** hPanel → Advanced → **SSH Access**. If your plan has SSH, use
it — the whole thing takes about fifteen minutes. If it does not, see
[Appendix A](#appendix-a--no-ssh) before starting, because the ordering changes.

On **Business** plans you have SSH, and Node.js as well. Node is not needed to
run this app — see the note in step 3 — but it is there if you want it.

### Adding this alongside an existing site

If the account already hosts something else, add this as its own domain or
subdomain (hPanel → Domains → **Subdomains**, or Websites → **Add Website**).
Each site gets its own `~/domains/<name>/public_html`, which is the path used
throughout this guide, so the two do not interact.

One thing that *is* shared: **PHP settings in step 1 apply per domain**, so set
them on this site specifically rather than assuming the account default carries
over. Databases are shared at the account level but separate per database —
create a new one rather than reusing an existing site's.

---

## 1. Set the PHP version and extensions

hPanel → Advanced → **PHP Configuration**.

Select **PHP 8.2 or 8.3. Not 8.1.**

`composer.json` claims `^8.1`, but that is wrong — the resolved lock file needs
8.2 at minimum. `nette/utils` requires `8.2 - 8.5`, and `symfony/string`,
`symfony/event-dispatcher` and `dragonmantank/cron-expression` all require
`>= 8.2`. On PHP 8.1 the site dies at the autoloader with a platform check
failure before Laravel boots.

On the **PHP extensions** tab, make sure these are ticked. The list is what this
project's dependency tree actually declares, not a generic list:

```
ctype  dom  fileinfo  filter  gd  hash  iconv  json  libxml  mbstring
openssl  pcre  session  simplexml  tokenizer  xml  xmlreader  xmlwriter
zip  zlib
```

Two are worth checking by name, because a missing one fails late and confusingly:

- **`gd`** — image resizing (`intervention/image`) and PDF rendering (`dompdf`).
- **`zip`** — the Excel import/export (`maatwebsite/excel`).

On the **PHP options** tab, raise these — the organization bulk importer accepts
spreadsheets and the defaults are too tight:

| Option                | Value   |
| --------------------- | ------- |
| `max_execution_time`  | `300`   |
| `memory_limit`        | `256M`  |
| `upload_max_filesize` | `64M`   |
| `post_max_size`       | `64M`   |

---

## 2. Create the database

hPanel → Databases → **MySQL Databases**. Create a database and a user, and give
that user all privileges on it.

Hostinger prefixes both with your account id, so you end up with something like
`u123456789_appdb` / `u123456789_admin`. Copy the name, user and password
somewhere — they go into `.env` at step 8.

Use `localhost` as the host, not `127.0.0.1`.

---

## 3. Build the frontend assets locally

Vue compiles to static files, and **nothing runs Node at runtime** — Vite's
output is plain JS and CSS that PHP serves like any other asset. So the build
happens once, wherever is convenient, and only the result needs to reach the
server.

Build it on your machine:

```bash
docker run --rm -v "$PWD":/app -w /app node:20-alpine sh -c "npm ci && npm run build"
```

That writes `public/build/` (~700 KB), which is what Laravel serves in
production. `resources/js/` is the source and is never read at runtime — but
upload it anyway, it costs nothing and it is the part of the project worth
showing.

> **Node is available on Business plans** (hPanel → Advanced → Node.js), so you
> *can* build on the server instead. It is rarely worth it here: building there
> means uploading or fetching ~40 MB of `node_modules` to produce 700 KB of
> output, on a box with less memory than your laptop. Build locally, upload the
> artifact.
>
> Do **not** register this project as a Node.js application in hPanel. That
> manager exists to run a Node process as your site — correct for a Next.js app,
> wrong here. This is a PHP site; Node is only a build tool.

Confirm before continuing:

```bash
ls public/build/manifest.json
```

If that file is missing, every page will 500 with a Vite manifest exception.

---

## 4. Prepare the upload

Two things must not be uploaded.

**`node_modules/`** (40 MB) is build-time only.

**710 `Zone.Identifier` files.** These are Windows "downloaded from the
internet" markers that came along when the project was unzipped. They are inert,
but they clutter every directory and one of them sits next to `.env`. Strip them:

```bash
find . -name '*Zone.Identifier*' -not -path './node_modules/*' -delete
```

Then build the archive:

```bash
zip -r ../community.zip . \
  -x 'node_modules/*' '.git/*' 'storage/logs/*' \
     'storage/framework/cache/data/*' \
     'storage/framework/sessions/*' 'storage/framework/views/*'
```

**About `vendor/` (106 MB):** leave it *in* the zip if you are unsure about SSH.
If you have SSH, exclude it (`-x 'vendor/*'`) and run `composer install` on the
server at step 7 — a far smaller upload and it resolves the right platform
builds for Hostinger's PHP.

---

## 5. Upload and extract

Upload `community.zip` via hPanel → Files → **File Manager**, or over SFTP
(credentials under Files → FTP Accounts).

Put the app **beside** `public_html`, not inside it. Target layout:

```
/home/u123456789/domains/your-domain.com/
├── app/            ← the Laravel project, extracted here
│   ├── app/
│   ├── public/
│   ├── vendor/
│   ├── .env
│   └── artisan
└── public_html/    ← the web root (step 6)
```

Extract into `app/` using File Manager's **Extract** action, or over SSH:

```bash
cd ~/domains/your-domain.com
mkdir -p app && cd app
unzip ~/community.zip && rm ~/community.zip
```

Nothing above `public/` is reachable over HTTP in this layout — which is the
point. `.env` holds your database password.

---

## 6. Point the web root at `public/`

**Preferred — symlink.** Replace `public_html` with a link to the app's
`public/`:

```bash
cd ~/domains/your-domain.com
rm -rf public_html
ln -s app/public public_html
ls -l public_html          # should print: public_html -> app/public
```

This keeps one copy of `public/`, so anything you add there later is live
immediately.

**If the site returns 403 after this**, the server is refusing to serve through
a symlinked root. Undo it and use the copy-and-repoint method instead:

```bash
cd ~/domains/your-domain.com
rm public_html && mkdir public_html
cp -r app/public/. public_html/
```

Then edit `public_html/index.php` and change the two paths that point back into
the app — they are the only two lines that need touching:

```php
require __DIR__.'/../app/vendor/autoload.php';
$app = require_once __DIR__.'/../app/bootstrap/app.php';
```

The trade-off is that `public_html` and `app/public` are now separate copies, so
re-copy after any future change to `public/`.

> **Do not use the third pattern** — extracting the whole project into
> `public_html` and letting the root `.htaccess` rewrite into `public/`. That
> file ships with the project and is what the original developer intended, but
> it puts `.env`, `composer.lock` and every PHP source file under the web root,
> one rewrite-rule mistake away from being served. Its own rules are already
> shaky: lines 12–13 test `%{REQUEST_FILE_NAME}`, which is not an Apache
> variable (the real one is `REQUEST_FILENAME`), so those two conditions never
> match anything.
>
> With the layout above, the root `.htaccess` and `server.php` are simply
> unused. Leave them; they do no harm outside the web root.

---

## 7. Install PHP dependencies

Over SSH, from `~/domains/your-domain.com/app`:

```bash
composer install --optimize-autoloader --no-dev
```

If Composer is killed part-way, it ran out of memory — retry with:

```bash
COMPOSER_MEMORY_LIMIT=-1 composer install --optimize-autoloader --no-dev
```

Skip this step entirely if you uploaded `vendor/`.

---

## 8. Configure `.env`

```bash
cp .env.example .env
php artisan key:generate
```

`.env.example` in this repo lists every key the application actually reads, with
a note on each — the stock Laravel one it replaced listed none of them.

Fill in at minimum:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_DATABASE=u123456789_appdb
DB_USERNAME=u123456789_admin
DB_PASSWORD=...
```

Three of these matter more than they look:

- **`APP_DEBUG=false`.** Left `true`, a stack trace on any error prints your
  database credentials to the public.
- **`APP_URL`** must be the real `https://` address. `asset()` and the Vite tag
  build every CSS, JS and image URL from it; get it wrong and the site loads
  unstyled.
- **`QUEUE_CONNECTION=database`** (already the default in `.env.example`). See
  step 10.

If you have no SSH, generate the key locally with
`php artisan key:generate --show` and paste the `base64:…` value into `.env`
by hand.

---

## 9. Migrate, seed, link storage

```bash
php artisan migrate --force
php artisan db:seed --force
```

The seed is **required, not optional** — it creates the US states list, the
service categories, and the first admin login. Without it the search filters are
empty and you cannot sign in.

```bash
php artisan storage:link
```

That links `public/storage` → `storage/app/public`, which is where every
uploaded logo, publication and generated PDF lives. Skip it and uploads appear
to succeed but every image 404s.

Then permissions:

```bash
chmod -R 775 storage bootstrap/cache
```

Finally, cache the framework's configuration:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> Re-run `php artisan config:cache` after **every** future `.env` edit. Until
> you do, the old values are still live — this is the single most common
> "I changed it and nothing happened" on Laravel.

---

## 10. Cron jobs

hPanel → Advanced → **Cron Jobs**. Two entries, both using the full path to
`artisan`.

**The scheduler**, every minute:

```
* * * * * /usr/bin/php /home/u123456789/domains/your-domain.com/app/artisan schedule:run >> /dev/null 2>&1
```

This drives `jobs:check` and the nightly organization link-validation job. If
your plan enforces a minimum interval longer than a minute, use the shortest it
allows — the daily job still fires, just checked less often.

**The queue worker**, every minute:

```
* * * * * /usr/bin/php /home/u123456789/domains/your-domain.com/app/artisan queue:work --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

Shared hosting has no Supervisor, so a long-running daemon is not an option.
`--stop-when-empty` drains the queue and exits; `--max-time=55` guarantees it
never overlaps the next tick.

This is what stops the admin's **Manual URL and email Verification** button from
timing out — it dispatches a job that pings every organization's website and
email in turn, which will not finish inside a web request.

Adjust `/usr/bin/php` if hPanel shows a versioned path such as
`/opt/alt/php82/usr/bin/php`.

---

## 11. Check it worked

Visit the site, then walk these five — each exercises a different piece of the
stack, and between them they catch nearly every deploy mistake:

| Check | What it proves |
| --- | --- |
| Home page is **styled**, no console 404s | `public/build` uploaded, `APP_URL` correct |
| Search resources returns rows, map draws | DB seeded, `GOOGLE_MAP_API_KEY` set |
| `/admin/login`, then the dashboard | Sessions writable, seeder ran |
| Edit an organization, upload a logo, reload | `storage:link` + `775` on `storage` |
| Admin → organizations → **Export** | `ext-zip` and `ext-gd` present |

The first four cover the Vue layer end to end: the home page mounts islands into
server-rendered HTML, while `/admin` is full Inertia. If the admin renders a
blank page but the public site is fine, that is the Inertia bundle failing to
load — check the browser console and confirm `public/build/manifest.json`
uploaded.

**The seeded admin is `admin@example.com` / `Admin@123`.** Sign in
with it once at `/admin/login`, then change both the address and the password
from the profile screen before the site is public — those credentials are
committed in `SuperAdminSeeder` and anyone with the source has them.

(The seeder only creates that account when no admin exists, so re-running
`db:seed` later will not resurrect it after you have changed it.)

---

## Appendix A — no SSH

Everything works except that you cannot run `artisan`. Adjust as follows:

- **Step 4:** keep `vendor/` in the zip. You cannot run `composer install`.
- **Step 6:** the symlink method needs a shell. Use the copy-and-repoint
  fallback, editing `index.php` in File Manager.
- **Step 8:** generate the key locally (`php artisan key:generate --show`) and
  paste it in. Create `.env` with File Manager — it hides dotfiles by default,
  so enable **Settings → Show hidden files**.
- **Step 9:** you cannot migrate, seed, or create the storage symlink from a
  browser. Add this to the bottom of `routes/web.php` *before* uploading:

  ```php
  Route::get('/setup-PASTE_A_LONG_RANDOM_STRING_HERE', function () {
      Artisan::call('migrate --force');
      Artisan::call('db:seed --force');
      Artisan::call('storage:link');
      return nl2br(e(Artisan::output()));
  });
  ```

  Paste a long random string directly into the route — do not read it from
  `env()`, which returns `null` as soon as anything caches the config and would
  leave the route sitting at the guessable path `/setup-`.

  Visit `https://your-domain.com/setup-<that-string>` once, read the output —
  then **delete the route and re-upload `web.php`**. It executes framework
  commands from an unauthenticated URL; the random path only buys you the
  window in which to use it.

  If `storage:link` fails (some shared hosts block `symlink()`), the fallback is
  to make `public/storage` a real directory and set `FILESYSTEM_DISK` to write
  there directly.

- **Step 10:** cron still works — it is configured in hPanel, not over SSH.

Given how much this appendix gives up, upgrading to a plan with SSH is usually
the cheaper move.

---

## Appendix B — updating a deployed site

```bash
cd ~/domains/your-domain.com/app
php artisan down
# upload changed files
composer install --optimize-autoloader --no-dev   # only if composer.json changed
php artisan migrate --force                        # only if migrations were added
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

Rebuild `public/build` and upload it whenever anything under `resources/js/` or
`resources/css/` changes. Editing a `.vue` file on the server changes nothing on
its own — the browser is served the compiled bundle, not the source.

---

## Known constraints

- **Laravel 10 is end-of-life.** It runs fine, but it stops receiving security
  patches. A framework upgrade is the first thing to plan after launch — see
  the note in `ARCHITECTURE.md`.
- **`API_VERIFICATION_TOKEN` should stay empty** unless you are running the
  companion WordPress site. Read the cleartext-cookie note in `ARCHITECTURE.md`
  first — the cookie is readable by any script on the page.
- **No Redis on shared hosting.** `CACHE_DRIVER` and `SESSION_DRIVER` stay
  `file`, which is why `storage/` must be writable.
