# portfolio-wp — Nathan Dulkis portfolio (WordPress + Lando)

Rebuild of Nathan's portfolio (previously Next.js in `ndulkis/portfolio`) as a classic WordPress theme, run locally with Lando.

## Run it

```sh
lando start
lando install     # downloads WP core, installs, activates theme, seeds content (safe to re-run)
lando seed      # re-run just the content seed
```

Site: https://portfolio-wp.lndo.site · admin: `admin` / `admin` (local only). WP-CLI: `lando wp …`.

The repo tracks only our code and the plugins (`.gitignore` is a whitelist): `wp-content/themes/nd-dive/`, `wp-content/plugins/`, `scripts/`, `docs/`, `.lando.yml`. WordPress core, `wp-config.php` and uploads stay local.

## Design source of truth

- Live design canvas: https://claude.ai/artifact/FUf9poBNM2LsXpXoc19CSL (pages: Final design · Tablet & mobile · Explorations).
- Static copies of the final boards: `docs/design/*.dc.html` (open in a browser to read markup; they reference a canvas runtime so they won't render standalone).
- Board → template map:

| Design board | Theme file |
|---|---|
| `UnderwaterDive.dc.html` (Home) | `front-page.php` |
| `About.dc.html` | `page-about.php` (story stops, At a glance, How I work = Page copy lists) |
| `Projects.dc.html` | `archive-project.php` (+ `taxonomy-project_type.php`) |
| `PostsIndex.dc.html` | `home.php` (+ `category.php`) |
| `PostArticle.dc.html` | `single.php` (default post layout) |
| `PostCaseStudy.dc.html` | `templates/post-case-study.php` (posts in category "Project update") |
| `PostRelease.dc.html` | `templates/post-release.php` (posts in category "Release") |
| `PostBlog.dc.html` | `templates/post-blog.php` (posts in category "Blog") |
| `NotFound.dc.html` | `404.php` |
| `Header.dc.html` / `Footer.dc.html` | `header.php` / `footer.php` (+ `parts/seafloor.php`) |

## Visual system ("underwater dive")

- Deep-sea gradient `#0F5266 → #0A3446 → #05202E → #02111A`, faint bubble dots, soft surface light top-right.
- Text `#E6F6F7`, muted `#B4D9DD`, faint `#9CC3C8`; single accent aqua `#6FEBD7` (hover `#B5F7EC`, text on accent `#04222E`). All tokens are CSS vars at the top of `style.css`.
- Type: Inter (UI/body) + JetBrains Mono (eyebrows, tags, dates). Self-hosted in `assets/fonts/` (latin, variable weight, OFL), `@font-face` at the top of `style.css`; no Google Fonts requests. Headlines use `clamp()` so they scale down on phones.
- Cards: `rgba(4,34,46,.72)` with 1px `rgba(111,235,215,.2)` border, 16–20px radius. Buttons/chips are pills, min 44–48px tall.
- Seafloor SVG closes every page (in `footer.php`).

## The diver character (important)

- Nathan appears only as an illustrated deep-sea diver (dark swept hair, stubble, green jacket over grey tee, brass helmet). **Never use real photos of Nathan on the site** — link LinkedIn instead.
- One distinct pose per page so nothing repeats: Home = tilted, waving, happy · About = cross-legged typing on a laptop · Projects = holding an open treasure chest · Posts = message in a bottle, wink · 404 = confused, map upside down. SVGs live in `assets/divers/`.
- Post templates intentionally have no big character (reading comfort).

## Motion & decor rules

- CSS-only animation, no JS. Everything stops under `prefers-reduced-motion`.
- Floating sea life (`nd_decor()` — sub, fish, jelly, school, angler) is positioned per page and **hidden ≤1024px** so it never overlaps text on tablet/phone. Keep it sparse — the user explicitly doesn't want clutter.

## Content model

- **Pages:** Home (front page), Posts (posts page), About (slug `about`).
- **Projects:** CPT `project` (archive `/projects/`), taxonomy `project_type` (Client work · Apps & tools · Games). Story fields in post meta via the "Project story" meta box: status, collab, blurb, problem, built, learned, tags (a list, one per row; "a · b" text is split on save), link url/label. Order with Page Attributes → Order (1 = first; saving renumbers all projects 1…n, moving the others at or below that number down one; empty = last). Featured image = card screenshot. Projects use the classic edit screen (no body content).
- **Posts:** the category is the post kind and picks the layout (`nd_post_layouts()`): Notes → article (`single.php`), Project update → case study, Release → release note, Blog → personal blog post. There is no Template choice. Posts use the classic screen with one **Post details** box between the title and the body (`nd_top` context): Summary (the excerpt) for every post, then only the current layout's fields. Project update: **Project facts** and numbered **Sections** (`01 · heading` on the site); Release: version, **Changes** (New / Improved / Fixed, grouped on the site; the summary is the intro), download heading/details; both: button link + label. Facts, sections and changes are `rows` fields (add / ↑↓ / × in `assets/rows.js`, styled by `assets/admin.css`, stored as arrays). For those layouts the body editor hides while empty; any body text renders after the rows. Blog: log details (logged / from / on repeat rows) and the reply-by-email sign-off. Notes and Blog are written in the body ("Insert outline" adds that layout's `nd_post_outlines()` entry; Blog's has `.nd-note` side notes and `.nd-pull` lines; the first letter is a drop cap); a Blog post ends with three more Blog posts. Hidden fields are disabled so they aren't saved. Featured image is labelled "Cover image"; the byline is the post author.
- **Page copy (Pages → Edit → "Page copy" box):** each page's fixed headline/intro/CTA copy is post meta on that page. Projects (slug `projects`; its URL still serves the project archive) and 404 (slug `not-found`, private) exist as pages only to hold their copy. All five use the classic screen with no body editor; About's story stops, At a glance and How I work are `rows` lists there (add / ↑↓ / ×, `assets/rows.js`; read with `nd_copy_rows()`). Unset fields fall back to defaults. Home and 404 buttons are Label + Link lists (`nd_buttons()`, first one filled); single in-page links and CTA buttons are label + link pairs (`nd_link()`; empty label hides one). Email/LinkedIn buttons take only a label; their address comes from Links & details. **Customizer → Links & details** holds GitHub, LinkedIn, email (LinkedIn/email hidden until set). All defined once in `nd_copy_fields()`; templates read `nd_copy( 'id' )`.
- **Menus (Appearance → Menus):** locations "Header navigation" and "Footer links", seeded once as Header (Projects · Posts · About) and Footer (GitHub). Printed as flat links via `nd_menu_links()`; the current section's link gets `aria-current`. With no menu assigned, the header falls back to `nd_default_nav()` and the footer to Links & details.
- **Fields:** project/post meta fields are typed arrays (`text|textarea|url|select`) in `nd_project_fields()` / `nd_post_fields()`; `register_post_meta` sanitizes by type. Self-check: `lando wp eval-file scripts/check-fields.php`.
- **Head & sharing:** every page (not 404) gets a meta description and Open Graph/Twitter tags from copy you already write (`nd_meta_description()`: post Summary, page intro, project blurb). Share image = the post's cover image, else `assets/og-default.png` (1200×630; re-render if the home headline changes). Icons: `assets/favicon.svg` + `apple-touch-icon.png` unless a Site Icon is set. Comments are off site-wide (no form, no admin menu). WordPress's emoji script/styles are removed (browsers draw emoji natively).
- **Editor:** the block editor is off for every type (`use_block_editor_for_post_type`); everything is edited on the classic screen with fields in boxes. The post body editor (TinyMCE) loads `style.css` + `editor-style.css` so drafts look like the site.
- `scripts/seed.php` holds all current real content (from the old site's `src/data/*`, copied to `docs/content-source/`).

## Plugins & security

- **Plugins are tracked in git** (`wp-content/plugins/`), so the live site is exactly what's in the repo. `lando install` activates whatever is there. The theme itself must keep working with no plugins.
- **Installed:** Two Factor 0.17.0 (2FA: set it up per user in Users → Profile, and save the backup codes) and Limit Login Attempts Reloaded 3.4.7 (locks an IP out for 20 min after 4 failed logins; its paid "cloud" features are optional and unused). Check files against wordpress.org with `lando wp plugin verify-checksums --all`.
- **Cloudflare Turnstile** (`wp-content/mu-plugins/nd-turnstile.php`, must-use, tracked): a human check on the login and lost-password forms, verified with Cloudflare before login. On only when `ND_TURNSTILE_SITE_KEY` and `ND_TURNSTILE_SECRET` are in `wp-config.php`: setup puts Cloudflare's always-pass **test** keys there locally (the widget says "For testing only"); the live site gets real keys from Cloudflare → Turnstile (never commit them). Fails closed; a failed check counts as a failed login for the rate limiter. Locked out on the live site? Remove the two constants.
- **Locked out locally?** Everything reaches Lando through one proxy IP, so a few bad logins lock out the whole local site. Clear it: `lando wp option update limit_login_lockouts '[]' --format=json` (same for `limit_login_retries`). On the live site behind Cloudflare/a proxy, set the plugin's "Trusted IP origins" to the forwarded header, or it will see every visitor as the proxy.
- **Updating (plugins and core): always through a branch.** `git switch -c update/<what>` → `lando wp plugin update --all` (see what's waiting with `lando wp plugin list --update=available`, or the local dashboard); for core, set `WP_VERSION` in `scripts/deploy.sh` and run `lando wp core update --version=<it>` → click through the site → commit → push → deploy. Never update on the live site: the next deploy would put the old version back.
- **Live site must block dashboard updates and file edits** (part of the deploy, in its `wp-config.php`): `define( 'DISALLOW_FILE_MODS', true );` (also hides update notices there, so check updates locally) and leave plugin auto-updates off.
- **Theme hardening** (functions.php, "Hardening"): no author archives (`?author=` → home), REST `/wp/v2/users` hidden from visitors, no users sitemap, no author in oEmbed, one login error for wrong username or password, XML-RPC refused (403), no WordPress version in the page. Login rate limiting / 2FA come from plugins; a firewall (host or Cloudflare) belongs to the deploy.

## Deploy (SpinupWP)

- Hosted on a VPS managed by SpinupWP. The site is created with "Clone a Git Repository" (SSH URL, read-only deploy key, branch `production`, Push to Deploy webhook on GitHub). The repo checks out into the web root (`files/`); untracked/ignored files there (core, `wp-config.php`, uploads) are left alone.
- Deployment script (Git tab): `sh scripts/deploy.sh` — pins WordPress core (`WP_VERSION`; change it on an update branch), updates the DB, activates plugins, flushes rewrites and caches. It never seeds; `setup.sh`/`seed.php` are local only (the PHP scripts exit unless run by WP-CLI).
- Live `wp-config.php` (on the server, never in git): `DISALLOW_FILE_MODS`, `WP_ENVIRONMENT_TYPE` `production`, real `ND_TURNSTILE_SITE_KEY`/`ND_TURNSTILE_SECRET`.
- Nginx on the server denies `/docs/`, `/scripts/`, `*.md`, `.lando.yml` (they sit in the web root): `/etc/nginx/sites-available/<domain>/server/nd-deny.conf` with `location ^~ /docs/`, `location ^~ /scripts/` (`^~` so PHP never runs them), `location ~ \.md$`, `location = /.lando.yml`, each `{ deny all; }`; then `sudo nginx -t && sudo service nginx reload`. Add it on every site.
- Server: SpinupWP on DigitalOcean (SFO3, 2 GB), hostname `sfo1.nathandulkis.com`. Staging = `staging.nathandulkis.dev` (branch `develop`, site user `staging`, database `nd_staging`, `wp-config.php` one level above `files/`, `blog_public` 0, no page cache). Production = `nathandulkis.dev` (branch `production`). DNS on Cloudflare (SSL Full (strict)).
- First launch: content goes up once from local (`lando wp db export`, import, `wp search-replace` the domain, copy `wp-content/uploads`); after that, live is the source of content and local is for code.

## Still open (placeholders shown as `[brackets]`)

- About: "Based in" and "Off the clock" are empty (hidden) until Nathan fills them in.
- Projects: "Learned" for all six; "Problem" for PokeGuess and Habit Tracker; screenshots (featured images).
- Posts: the seeded posts are local placeholders and get **deleted before launch**; real posts are published over time. With no posts, Home's Dive log and the Posts page show an editable empty line (Page copy: "…when there are no posts yet").
- Settings: admin email (ndulkis@gmail.com) and timezone (America/Los_Angeles) are set by setup.
- LinkedIn URL and email (Customizer).
- Not built yet: Résumé page (old site had `/resume`; résumé PDF is uploaded to the Media Library and linked from About if set).

## Conventions

- Keep it a classic PHP theme, no build step; the theme never requires a plugin (meta boxes are native), plugins are only add-ons like security. Don't add ACF/page builders without asking.
- Escape all output (`esc_html`, `esc_url`, `esc_attr`); sanitize meta on save.
- Design changes go to the canvas first when they're visual; keep `docs/design` in sync if boards change.
