# portfolio-wp

Nathan Dulkis's portfolio as a WordPress theme ("ND Dive" — an underwater theme), run locally with [Lando](https://lando.dev).

## Quick start

Requires Lando (and Docker).

```sh
lando start
lando install
```

Then open https://portfolio-wp.lndo.site (admin: `admin` / `admin`, local only).

`lando install` downloads WordPress core, writes `wp-config.php`, installs the site, activates the theme, sets pretty permalinks and seeds the pages, projects and example posts. It's safe to run again. Use `lando seed` to re-apply just the content.

## What's in the repo

```
.lando.yml                     Lando config (WordPress recipe, PHP 8.3, nginx, MySQL 8)
scripts/setup.sh               First-run install
scripts/seed.php               Content seed (real projects + placeholder posts)
wp-content/themes/nd-dive/     The theme
docs/design/                   Final design boards (reference)
docs/content-source/           Content from the previous Next.js site
CLAUDE.md                      Full project context and decisions
```

Only those paths are committed; WordPress core and uploads stay local.

## Editing content

- **Projects:** Projects → edit → "Project story" box. Featured image = card screenshot. Order via Page Attributes.
- **Posts:** pick the category (Notes / Project update / Release) and, for project updates or releases, the matching Template. Fill "Template details" for facts, version and download link.
- **About story:** edit the About page; add stops with the "Dive stop" block pattern.
- **Page headlines, intros and other fixed copy:** Appearance → Customize → Site content → (page). The Home, About and Posts edit screens have an "Edit page copy" button.
- **Links (GitHub, LinkedIn, email):** Appearance → Customize → Site content → Links & details.
