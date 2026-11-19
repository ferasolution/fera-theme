# Performance findings and fixes

The supplied PageSpeed reports were inspected on 11 October 2026. These are production baseline measurements, not measurements of this package:

| Metric | Desktop | Mobile |
|---|---:|---:|
| Performance score | 64 | 49 |
| FCP | 0.6 s | 2.9 s |
| LCP | 1.1 s | 4.7 s |
| Total blocking time | 770 ms | 1,020 ms |
| CLS | 0.003 | 0.020 |
| Speed index | 2.9 s | 8.6 s |

Reports: https://pagespeed.web.dev/analysis/https-ferasolution-com/eofd0k5vvp?form_factor=desktop and https://pagespeed.web.dev/analysis/https-ferasolution-com/eofd0k5vvp?form_factor=mobile . No field Core Web Vitals data was available. Lab TBT is not INP.

## Changes in this package

The approved artwork, layouts and interactive workflows remain. Responsive copies of the same images are supplied; originals are retained. Header logo delivery is 1,516 bytes at 1x or 3,470 bytes at 2x instead of 72,345 bytes. The hero has 480/768/1100/1536-pixel candidates; the 768-pixel candidate is 50,500 bytes instead of the 142,078-byte original. Product, food and architecture images also have responsive candidates. Explicit dimensions, eager/high-priority hero loading and lazy below-the-fold loading reduce unnecessary transfers and shifts.

The main theme now loads two consolidated CSS files totalling 26,718 bytes before HTTP compression, compared with six reference CSS files totalling 59,645 bytes (55.2% reduction). This is an asset-size comparison, not a speed-score guarantee. All frontend JavaScript/CSS has bundled minified delivery files; readable originals remain in the ZIP. No build tools are required on WordPress.

Theme scripts defer until HTML is parsed. Nonessential canvas initialization waits for idle time. Hero rendering stops when offscreen, hidden or reduced motion is requested; resizing is debounced. Network geometry is precomputed instead of recalculating trigonometry for every node each frame. Reduced-motion behavior and working controls are retained.

Manrope is bundled locally. On theme-owned pages only, duplicate Elementor Google Font requests and WordPress emoji extras are avoided. Contact Form 7 assets are avoided only where no CF7 content/widget is detected; authored forms are preserved. Elementor assets and jQuery are not blindly stripped.

Tawk is loaded when human handoff is requested, with a single ownership setting and duplicate-script checks. No new tracking scripts are added. Demo assets are loaded on demo views rather than across every main page.

## Production findings requiring staging access

Mobile execution cost included Google Tag Manager/Google Ads (698 ms CPU), Facebook (430 ms), DoubleClick (301 ms), ConsentManager (270 ms), FundingChoices (166 ms), jQuery (269 ms), and a Luxonix-core D3 dependency. These plugins/tag configurations are not contained in this theme. Inventory actual tracking ownership and retain only justified tags, respecting consent. Remove obsolete theme/plugin assets after a reviewed dependency check; do not dequeue D3/jQuery blindly.

The report contains a Tawk embed CORS/network failure. Verify the configured public property/widget IDs and remove duplicate plugin embeds on staging. Do not disable browser CORS or proxy the widget to hide this error. Test the vendor connection after correcting configuration.

Server response time, Bluehost caching, Jetpack image CDN, compression and third-party cache lifetimes require hosting inspection. Do not add competing caching plugins. Configure long caching for versioned static assets only, preserve no-store bootstrap/error responses and bypass personalized/admin responses.

## Verification and release gate

The actual ZIP was installed into clean WordPress, not a preview server. See TEST-REPORT.md for executed checks. No post-change Lighthouse or production Core Web Vitals score is claimed. Run desktop and mobile Lighthouse on Bluehost staging after clearing caches; compare identical pages and configurations with three runs each. Record LCP resource, long tasks, layout shifts, transfer sizes and request ownership. Then retest chat, forms, every demo and Elementor before production deployment. Targets remain LCP <=2.5 s, INP <=200 ms and CLS <=0.1; they are not guarantees.
