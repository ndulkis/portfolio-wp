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
- **Projects:** CPT `project` (archive `/projects/`), taxonomy `project_type` (Client work · Apps & tools · Games). Story fields in post meta via the "Project story" meta box: status, collab, blurb, problem, built, learned, tags (a list, one per row; "a · b" text is split on save), link url/label. Order with Page Attributes → Order. Featured image = card screenshot. Projects use the classic edit screen (no body content).
- **Posts:** the category is the post kind and picks the layout (`nd_post_layouts()`): Notes → article (`single.php`), Project update → case study, Release → release note, Blog → personal blog post. There is no Template choice. Posts use the classic screen with one **Post details** box between the title and the body (`nd_top` context): Summary (the excerpt) for every post, then only the current layout's fields. Project update: **Project facts** and numbered **Sections** (`01 · heading` on the site); Release: version, **Changes** (New / Improved / Fixed, grouped on the site; the summary is the intro), download heading/details; both: button link + label. Facts, sections and changes are `rows` fields (add / ↑↓ / × in `assets/rows.js`, styled by `assets/admin.css`, stored as arrays). For those layouts the body editor hides while empty; any body text renders after the rows. Blog: log details (logged / from / on repeat rows) and the reply-by-email sign-off. Notes and Blog are written in the body ("Insert outline" adds that layout's `nd_post_outlines()` entry; Blog's has `.nd-note` side notes and `.nd-pull` lines; the first letter is a drop cap); a Blog post ends with three more Blog posts. Hidden fields are disabled so they aren't saved. Featured image is labelled "Cover image"; the byline is the post author.
- **Page copy (Pages → Edit → "Page copy" box):** each page's fixed headline/intro/CTA copy is post meta on that page. Projects (slug `projects`; its URL still serves the project archive) and 404 (slug `not-found`, private) exist as pages only to hold their copy. All five use the classic screen with no body editor; About's story stops, At a glance and How I work are `rows` lists there (add / ↑↓ / ×, `assets/rows.js`; read with `nd_copy_rows()`). Unset fields fall back to defaults. Home and 404 buttons are Label + Link lists (`nd_buttons()`, first one filled); single in-page links and CTA buttons are label + link pairs (`nd_link()`; empty label hides one). Email/LinkedIn buttons take only a label; their address comes from Links & details. **Customizer → Links & details** holds GitHub, LinkedIn, email (LinkedIn/email hidden until set). All defined once in `nd_copy_fields()`; templates read `nd_copy( 'id' )`.
- **Menus (Appearance → Menus):** locations "Header navigation" and "Footer links", seeded once as Header (Projects · Posts · About) and Footer (GitHub). Printed as flat links via `nd_menu_links()`; the current section's link gets `aria-current`. With no menu assigned, the header falls back to `nd_default_nav()` and the footer to Links & details.
- **Fields:** project/post meta fields are typed arrays (`text|textarea|url|select`) in `nd_project_fields()` / `nd_post_fields()`; `register_post_meta` sanitizes by type. Self-check: `lando wp eval-file scripts/check-fields.php`.
- **Head & sharing:** every page (not 404) gets a meta description and Open Graph/Twitter tags from copy you already write (`nd_meta_description()`: post Summary, page intro, project blurb). Share image = the post's cover image, else `assets/og-default.png` (1200×630; re-render if the home headline changes). Icons: `assets/favicon.svg` + `apple-touch-icon.png` unless a Site Icon is set. Comments are off site-wide (no form, no admin menu). WordPress's emoji script/styles are removed (browsers draw emoji natively).
- **Editor:** the block editor is off for every type (`use_block_editor_for_post_type`); everything is edited on the classic screen with fields in boxes. The post body editor (TinyMCE) loads `style.css` + `editor-style.css` so drafts look like the site.
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
