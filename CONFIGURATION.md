# Optional connections and secure configuration

The theme installs with email, AI and Tawk loading disabled. These are external integrations; successful website setup does not authenticate a mailbox or an AI provider.

## WordPress settings

Settings > Fera Foundry Connections:

- Enable email only after testing your Microsoft 365 SMTP/OAuth connector and actual inbox receipt. Configure authorized From and Force From in that connector; customers remain Reply-To.
- AI provider: OpenAI or Gemini. Enable only after adding the server credential and testing privacy/cost/failure behavior.
- Tawk owner: none, one theme-owned loader on handoff, or an existing external plugin/embed. Public property/widget IDs are safe configuration; do not duplicate scripts.
- Google/Bing verification tokens: use the theme when no recognized SEO plugin owns output. Tokens are public verification metadata.
- Recover stopped request locks: administrator-only, nonce-protected. Use only after confirming no request remains active.

## Server secrets — optional AI only

FERA_OPENAI_API_KEY or FERA_GEMINI_API_KEY must be configured securely through your host/server environment or an existing private configuration. Never place the real value in JavaScript, Elementor, HTML, this ZIP or repository. No PHP edit is required for the website itself; host assistance may be needed to configure a server environment secret.

Optional supported constants, if your existing secure server configuration supplies them:

- FERA_OPENAI_MODEL: gpt-4.1-mini (default) or gpt-4.1-nano.
- FERA_GEMINI_MODEL: gemini-2.5-flash-lite (default) or gemini-2.5-flash.
- FERA_MAIL_DAILY_REQUESTS: default 50, allowed range 0–500.
- FERA_AI_DAILY_REQUESTS: default 10, allowed range 0–100.
- FERA_AI_MONTHLY_REQUESTS: default 100, allowed range 0–1000.
- FERA_AI_MONTHLY_BUDGET_CENTS: default 500, allowed range 0–5000.

Defaults fail safely. Requests reserve five cents locally, not actual provider charges; use provider-side billing limits too. Provider/model availability and actual credentials require staging verification. No custom authentication, payment processor, security scanner or production demo database is bundled.

## Metadata ownership and hosting

WordPress core provides XML sitemaps when no plugin replaces them. Business pages receive theme metadata only if no recognized Yoast, Rank Math, SEOPress, All in One SEO or SEO Framework owner is present. Configure that plugin’s titles/descriptions and demo sitemap exclusions when active. Demo responses remain noindex and have descriptive sharing metadata.

Staging stays noindex/disallowed through environment/search visibility. Final-domain robots, canonicals, sitemap, TLS, cookie settings, CSP compatibility and Bluehost caching must be reviewed before launch. Theme headers add nosniff, same-origin framing and referrer/base/object restrictions; no HSTS or server configuration is changed blindly.
