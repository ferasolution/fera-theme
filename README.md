# Fera Solution Foundry — complete installation

Install fera-solution-foundry.zip. Dashboard name: Fera Solution Foundry. Theme directory: fera-foundry. The ZIP contains source PHP, all page content, images, local fonts, SVG artwork, styles, scripts, synthetic sample data and 29 interactive applications. No old theme, old page ID, npm, Node.js, manual copying or PHP editing is required to install the website. WordPress 6.5+ and PHP 8.1+ are minimum code requirements; use supported WordPress/PHP versions on hosting. Free Elementor is optional for native content editing. No companion plugin or separate import package is required.

## 1. Back up and use staging

Back up your existing files and database, media, themes, plugins, menus, Reading settings, current URLs and Elementor exports. Use a clean WordPress installation or Bluehost staging first. Do not delete the old theme or pages before this backup and staging verification. This package performs no production deployment or deletion.

## 2. Upload the single ZIP

WordPress Admin > Appearance > Themes > Add New Theme > Upload Theme. Select fera-solution-foundry.zip, Install Now, then Activate. Upload the ZIP itself, not the entire workspace. The archive has the required fera-foundry/style.css structure.

Activation creates eleven complete business pages, a demo hub and 29 real WordPress demo pages using bundled content. No old Fera content or preview server is consulted. Spectrum is excluded from the new pages and primary navigation. The root homepage renders immediately through front-page.php.

## 3. Complete first-run setup

Open Appearance > Fera Foundry Setup. Click Use the finalized Foundry homepage. This selects the new Fera Foundry Home, backs up previous Reading settings and enables native Elementor documents when Elementor is installed. Verify Settings > Reading > A static page > Homepage: Fera Foundry Home.

The fallback navigation works immediately. To use a custom menu, assign it to Fera primary navigation and select NEW Foundry pages from the Setup links. The menu must retain Home, Software, Websites, Automation, Cybersecurity, Marketing, Live Demos, About and Contact.

Setup is repeatable: owned page IDs and edited content are retained rather than duplicated or overwritten. Missing/deleted owned pages can be recreated by rerunning Setup. Old unrelated pages, media, menus and Elementor data are preserved. If an old page occupies a requested slug, WordPress may add a suffix; links are resolved by the new theme’s own IDs. Review important URL mappings before retiring old pages. Do not manually rename/delete active pages until a reviewed migration plan is ready.

## 4. Permalinks and caches

Save Settings > Permalinks using your normal structure, preferably Post name. Routes are actual WordPress pages, including nested demo pages; plain page-ID links also work. Rewrite rules are flushed only during activation/setup.

If pretty URLs return Nothing Found while page-ID links work, save permalinks and ask Bluehost to check its standard WordPress rewrite configuration for that staging directory. Do not edit core or paste guessed server rules.

Purge staging Bluehost/WordPress caches, regenerate Elementor CSS/data using the tools available in your installed Elementor version, then reload in a private window. Do not disable production caching globally or add a second caching plugin blindly.

## 5. Review and edit content

Use the new page links in Foundry Setup. Pages include Home, Software Development, Website Development, Web Applications & Automation, Cybersecurity, Digital Marketing, Live Demos / Our Work, About, Contact, Privacy Policy and Terms and Conditions.

Native Elementor containers, headings, text and buttons hold editable copy. Five Foundry widgets supply animation, visual, inquiry form, replay and demo showcase. Theme header/footer use the new navigation and bundled branding. Free Elementor is sufficient; the public website also renders without it. If Elementor is installed after theme activation, run Setup again to enable editing.

Quote CTAs lead to the current page’s #inquiry form. Privacy/Terms and demo applications lead to the Contact form. The Contact page remains in the header and footer. Sticky-header scroll offsets and keyboard focus are included. Every service has its relevant working demos; the dedicated Live Demos page remains intact.

About includes the founder story supplied by you: Faizan Hasan’s freelance work developed into Fera Solution in 2025, plus founder introduction, vision, mission, values, process and expertise. No invented qualifications, clients or awards are included. Cloud platform expertise and security-review deliverables remain scope discussions until confirmed. Review privacy and terms against actual operations before publication.

## 6. Configure Microsoft 365 email

The form uses secure POST handling and requires phone, name, email, project details and consent. Technology preference is optional. Sales inquiries route to sales@ferasolution.com, Support to support@ferasolution.com and General to contact@ferasolution.com.

Connect your Microsoft 365 mailbox through your authenticated SMTP/OAuth plugin, such as the already configured FluentSMTP connection. Use an authorized mailbox as From and force that From address in the connector; the customer’s email is Reply-To. Send a test email and confirm actual inbox receipt, then enable email in Settings > Fera Foundry Connections. Test a fictional inquiry in each department and check SMTP logs and Microsoft message trace when needed.

A wp_mail success result means acceptance by the mail connection, not confirmed inbox delivery. The form states this honestly. No separate lead database or queue is silently added. Errors preserve entered details for retry and give direct-contact alternatives. Agree retention/recovery before adding lead storage. SMTP logs may contain inquiry details; review access and retention.

## 7. Configure one chat entry point

Settings > Fera Foundry Connections offers no Tawk connection, one theme-owned lazy loader, or use of an existing external Tawk plugin/embed. Select external ownership if another integration already loads Tawk. Do not paste a duplicate script into Elementor/header/footer. Public Property and Widget IDs are configuration, not API secrets.

Let’s Talk is the primary floating launcher. Tawk is loaded through the theme only on human handoff when configured. Its own bubble is hidden when the API is ready; the Fera launcher hides while human chat is maximized and returns on minimize. Test actual vendor behavior and consent-banner/mobile placement on staging.

AI is optional; otherwise the panel is clearly a scripted service guide. Choose OpenAI or Gemini and configure FERA_OPENAI_API_KEY or FERA_GEMINI_API_KEY through secure hosting/server environment configuration. No real keys are bundled. Never place secrets in Elementor, frontend code or public files. Optional model/budget constants are documented in CONFIGURATION.md. Use provider-side spending limits as well: local cost reservations are not billing enforcement. Review privacy disclosures before enabling providers.

## 8. Verify demos, SEO and security

All 29 demonstrations use bundled synthetic data, not real payments, scans, customers or production integrations. New standalone apps save non-sensitive demo changes locally; original retained apps explicitly reset on reload. Embedded apps use an opaque-origin sandbox and may have temporary state. Reset controls restore sample data. Do not enter sensitive or personal data.

The demo URL tokens are opaque identifiers, not encryption or access control. On a clean install, established demo tokens remain available beneath /experience/. Use the actual gallery links if WordPress adds suffixes due to existing content. No admin routes are repurposed and no custom publicly writable database is introduced.

The theme supplies unique business metadata, sharing tags and accurate Organization/WebSite/Service/Breadcrumb schema when no recognized SEO plugin owns output. Demo pages have unique descriptive metadata but remain noindex to avoid indexing thin simulations. Core sitemaps exclude demos. If a recognized SEO plugin is active, review its page metadata and sitemap exclusions to avoid conflicts; real plugin interoperability remains a staging gate.

Keep staging private/noindex through Bluehost and WordPress search visibility. Confirm final-domain canonical URLs, robots and sitemaps before production approval. Google/Bing verification tokens are public configuration in Foundry Connections, or use your active SEO plugin.

Public inquiry and AI endpoints enforce validation, nonces, payload limits, per-IP and global allowances, serialized option locks and safe errors. A process crash fails closed; an administrator can recover stopped locks in Connections settings after checking no request remains active. Hosting proxy IP handling, object caches, email/provider connectivity and plugin conflicts require staging review. No complete penetration test or zero-vulnerability claim is made.

## 9. Retire the old site safely

After staging passes, export/back up the old pages and record their URLs. Review which old pages correspond to new Foundry pages and approve any redirects or slug changes. Move only reviewed obsolete pages to Trash first; do not permanently delete them or old theme files until rollback is proven. Your clean-install package works without them, but their removal on production is a separate action requiring your approval.

No production deployment is authorized or performed by this package. Follow PAGE-CHECKLIST.md and the accompanying TEST-REPORT.md before launch.

## Rollback

Foundry Setup > Restore previous Reading settings, then reactivate the previous theme. Old content is preserved and new Foundry pages are retained. If you changed content, URLs or plugins, restore matching database/files backups and clear caches. On a clean test installation, keep a full pre-install snapshot for equivalent rollback.

## Troubleshooting

- Old home: check active theme/domain, select Fera Foundry Home, then purge caches.
- Missing menu: use fallback navigation or assign the correct new menu location/pages.
- Nothing Found: use Setup/gallery links and verify WordPress permalinks/host rewrites.
- Dark text: regenerate Elementor CSS and remove inherited page-level color overrides on staging; use the new bundled pages. Do not mix old templates into new pages.
- No email: check recipient, authenticated From/SMTP logs and Microsoft trace; the theme toggle does not connect a mailbox.
- Duplicate chat: inventory external plugins/snippets and select the correct loader ownership.
- Blank demo: inspect console/network errors and use its real WordPress route rather than an old theme-file URL.
