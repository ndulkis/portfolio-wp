# portfolio-wp — Nathan Dulkis portfolio (WordPress + Lando)

Rebuild of Nathan's portfolio (previously Next.js in `ndulkis/portfolio`) as a classic WordPress theme, run locally with Lando.

## Run it

```sh
lando start
lando install     # downloads WP core, installs, activates theme, seeds content (safe to re-run)
lando seed      # re-run just the content seed
```

Site: https://portfolio-wp.lndo.site · admin: `admin` / `admin` (local only). WP-CLI: `lando wp …`.

The repo tracks only our code (`.gitignore` is a whitelist): `wp-content/themes/nd-dive/`, `scripts/`, `docs/`, `.lando.yml`. WordPress core, `wp-config.php` and uploads stay local.

## Design source of truth

- Live design canvas: https://claude.ai/artifact/FUf9poBNM2LsXpXoc19CSL (pages: Final design · Tablet & mobile · Explorations).
- Static copies of the final boards: `docs/design/*.dc.html` (open in a browser to read markup; they reference a canvas runtime so they won't render standalone).
- Board → template map:

| Design board | Theme file |
|---|---|
| `UnderwaterDive.dc.html` (Home) | `front-page.php` |
| `About.dc.html` | `page-about.php` (story stops = page content, "Dive stop" block pattern) |
| `Projects.dc.html` | `archive-project.php` (+ `taxonomy-project_type.php`) |
| `PostsIndex.dc.html` | `home.php` (+ `category.php`) |
| `PostArticle.dc.html` | `single.php` (default post layout) |
| `PostCaseStudy.dc.html` | `templates/post-case-study.php` (Post → Template: "Case study") |
| `PostRelease.dc.html` | `templates/post-release.php` (Post → Template: "Release note") |
| `NotFound.dc.html` | `404.php` |
| `Header.dc.html` / `Footer.dc.html` | `header.php` / `footer.php` (+ `parts/seafloor.php`) |

## Visual system ("underwater dive")

- Deep-sea gradient `#0F5266 → #0A3446 → #05202E → #02111A`, faint bubble dots, soft surface light top-right.
- Text `#E6F6F7`, muted `#B4D9DD`, faint `#9CC3C8`; single accent aqua `#6FEBD7` (hover `#B5F7EC`, text on accent `#04222E`). All tokens are CSS vars at the top of `style.css`.
- Type: Inter (UI/body) + JetBrains Mono (eyebrows, tags, dates). Headlines use `clamp()` so they scale down on phones.
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
- **Projects:** CPT `project` (archive `/projects/`), taxonomy `project_type` (Client work · Apps & tools · Games). Story fields in post meta via the "Project story" meta box: status, collab, blurb, problem, built, learned, tags, link url/label. Order with Page Attributes → Order. Featured image = card screenshot. Projects use the classic edit screen (no body content).
- **Posts:** category is the post kind (Notes · Project update · Release). Layout chosen per post via Template. Case-study/release facts come from the "Template details" meta box, which only shows the chosen template's fields (`assets/post-fields.js`; hidden fields are disabled so they aren't saved).
- **Customizer → Site content:** a section per page (Home, About, Projects, Posts, 404) holds the fixed headline/intro/CTA copy, plus Links & details (GitHub, LinkedIn, email; LinkedIn/email links are hidden until set). Defined once in `nd_copy_fields()`; templates read `nd_copy( 'id' )`. Opening a section jumps the preview to that page; Home/About/Posts edit screens link to their section.
- **Fields:** project/post meta fields are typed arrays (`text|textarea|url|select`) in `nd_project_fields()` / `nd_post_fields()`; `register_post_meta` sanitizes by type. Self-check: `lando wp eval-file scripts/check-fields.php`.
- **Editor:** the block editor loads `style.css` + `editor-style.css` (dark canvas, prose and dive-stop styles) so drafts look like the site.
- `scripts/seed.php` holds all current real content (from the old site's `src/data/*`, copied to `docs/content-source/`).

## Still open (placeholders shown as `[brackets]`)

- About: where Nathan grew up, the moment he got hooked, city, hobbies.
- Projects: "Learned" for all six; "Problem" for PokeGuess and Habit Tracker; screenshots (featured images).
- Posts: real dates, excerpts, bodies. Case study role/timeline.
- LinkedIn URL and email (Customizer).
- Not built yet: Résumé page (old site had `/resume`; `assets/resume.pdf` is linked from About), hosting/deploy choice (WordPress chosen partly because it's cheaper to host than Drupal).

## Conventions

- Keep it a classic PHP theme, no build step, no plugins required (meta boxes are native). Don't add ACF/page builders without asking.
- Escape all output (`esc_html`, `esc_url`, `esc_attr`); sanitize meta on save.
- Design changes go to the canvas first when they're visual; keep `docs/design` in sync if boards change.
