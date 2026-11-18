# Fera Theme

Custom WordPress theme for **ferasolution.com** — Fera Solution LLC, a USA-based digital agency (Herndon, Virginia) serving clients worldwide.

## What's inside

| Path | What it is |
|---|---|
| `front-page.php`, `page-{about,services,work,contact}.php`, `single-fera_service.php` | Design templates. Content lives in `partials/*.html` |
| `page.php`, `single.php`, `index.php`, `404.php` | Normal pages, blog posts, archives (Elementor-built pages still work) |
| `inc/setup.php` | Services post type (`/services/<slug>/`), first-run pages, template routing |
| `inc/enquiry.php` | Contact/project forms → saved under **Enquiries** in wp-admin + emailed (via FluentSMTP) |
| `inc/seo.php` | Schema (ProfessionalService, Service, FAQ). Titles/meta/sitemaps come from All in One SEO |
| `inc/assets.php` | CSS/JS loading, font preloads, head clean-up |
| `src/` | Source JS/CSS. `npm run build` → `assets/js`, `assets/css` |

## First activation (staging first!)

On activation the theme creates — only if missing — the pages **About, Services, Work, Contact**, the eight **Services** posts, and (if the front page still shows latest posts) a **Home** + **Blog** page set under Settings → Reading. Nothing existing is overwritten.

After activating:
1. Settings → Permalinks → **Save** once (refreshes `/services/...` URLs).
2. Settings → General → **Fera enquiries email** (default `sales@ferasolution.com`).
3. All in One SEO → set titles/descriptions per page; Search Appearance → Organization.
4. Tawk.to plugin stays active — the site's "Let's talk" button opens the Tawk chat.

## Deploy (GitHub → Bluehost)

Push to `main` → GitHub Actions builds and uploads the theme to **staging** over FTPS.
To go live: **Actions → Deploy Fera Theme → Run workflow → target: production**.

Repository secrets (Settings → Secrets and variables → Actions):

| Secret | Value |
|---|---|
| `FTP_SERVER` | Bluehost FTP host, e.g. `ftp.ferasolution.com` |
| `STAGING_FTP_USERNAME` / `STAGING_FTP_PASSWORD` | FTP account whose folder is the staging site's `wp-content/themes/fera-theme/` |
| `PROD_FTP_USERNAME` / `PROD_FTP_PASSWORD` | FTP account whose folder is the live site's `wp-content/themes/fera-theme/` |
| `STAGING_FTP_DIR` / `PROD_FTP_DIR` | Optional — only if the FTP account's root is not the theme folder (e.g. `/public_html/staging/1234/wp-content/themes/fera-theme/`) |

## Local development

```bash
npm ci
npm run build      # or: npm run watch
```

Testing tip: the 3D logo is skipped on software-only GPUs (keeps PageSpeed scores high). Add `?fera-gl=force` to a URL to force it on.
