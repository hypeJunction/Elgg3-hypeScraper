# hypeScraper — Architecture (Elgg 5.x)

## Summary

hypeScraper is a URL extraction and embedding service plugin for Elgg. It scrapes metadata from URLs (title, description, images, oEmbed HTML), caches the results in a custom database table, and renders scrape results as embed cards or media players. It also optionally linkifies hashtags, URLs, usernames, and emails in HTML output.

## Entity Types

No custom entity types. Data is stored in the `scraper_data` custom table.

## Directory Structure

```
hypescraper/
├── elgg-plugin.php              — routes, actions, settings, upgrades
├── composer.json                — Elgg 5.x, PHP >=8.2
├── classes/hypeJunction/Scraper/
│   ├── Bootstrap.php            — plugin bootstrap (event registrations)
│   ├── ScraperService.php       — core scrape/parse/cache logic, DB access
│   ├── Extractor.php            — URL data extraction
│   ├── Router.php               — route handler
│   ├── Linkify.php              — URL/hashtag/mention linkification
│   ├── WebResource.php          — scraped resource value object
│   ├── WebLocation.php          — location value object
│   ├── WebLocationField.php     — form field for web location input
│   ├── FileCache.php            — local file-based cache
│   ├── HttpConfig.php           — HTTP client configuration
│   ├── Post.php                 — entity post integration
│   ├── AddFormField.php         — event: fields/object
│   ├── AddBookmarkProfilePreview.php — event: view_vars/object/elements/full
│   ├── AddBookmarkRiverPreview.php   — event: view_vars/river/elements/layout
│   ├── CardMenu.php             — event: register/menu:scraper:card
│   ├── EmbedMenu.php            — event: register/menu:embed
│   ├── EmbedRiverAttachment.php — event: view_vars/river/elements/layout (priority 999)
│   ├── FilteroEmbedHtml.php     — event: parse/framework:scraper (oEmbed domain allowlist)
│   ├── PageMenu.php             — event: register/menu:page
│   ├── PrepareEmbedCard.php     — event: format:src/embed
│   ├── PrepareHtmlOutput.php    — event: prepare/html (priority 100)
│   ├── ScrapeUrlMetadata.php    — event: extract:meta/all
│   ├── ScrapeUrlMetadata.php    — event: extract:qualifiers/all (static method)
│   ├── EmbedAction.php          — action handler for embed/player
│   └── Upgrade/
│       └── MigrateScraperDataToJson.php — upgrade batch: serialize→JSON re-encoding
├── actions/admin/scraper/
│   ├── edit.php, refetch.php, clear.php, timestamp_images.php
├── install/mysql.sql            — creates scraper_data table
├── lib/
│   ├── functions.php            — helper functions
│   └── whitelist.php            — default oEmbed domain allowlist
├── views/default/
│   ├── resources/scraper/card.php
│   ├── output/card.php, output/player.php
│   ├── embed/safe/player.php
│   └── framework/scraper/stylesheet.css, player.js
└── languages/en.php
```

## Registered Events (Elgg 5.x)

| Event | Type | Handler | Priority |
|-------|------|---------|----------|
| `format:src` | `embed` | `PrepareEmbedCard` | default |
| `extract:meta` | `all` | `ScrapeUrlMetadata` | default |
| `extract:qualifiers` | `all` | `[ExtractTokensFromText, 'extractTokens']` | default |
| `prepare` | `html` | `PrepareHtmlOutput` | 100 |
| `fields` | `object` | `AddFormField` | default |
| `view_vars` | `river/elements/layout` | `AddBookmarkRiverPreview` | default |
| `view_vars` | `object/elements/full` | `AddBookmarkProfilePreview` | default |
| `parse` | `framework:scraper` | `FilteroEmbedHtml` | default |
| `register` | `menu:scraper:card` | `CardMenu` | default |
| `register` | `menu:page` | `PageMenu` | default |
| `register` | `menu:embed` | `EmbedMenu` | default (shortcodes only) |
| `view_vars` | `river/elements/layout` | `EmbedRiverAttachment` | 999 (shortcodes only) |

## Routes

| Name | Path | Resource view |
|------|------|--------------|
| `scraper:card` | `/scraper` | `resources/scraper/card` |

## Admin Actions

| Action | Access |
|--------|--------|
| `admin/scraper/edit` | admin |
| `admin/scraper/refetch` | admin |
| `admin/scraper/clear` | admin |
| `admin/scraper/timestamp_images` | admin |

## Dependencies

- **hypejunction/http-parser** — HTTP parsing and oEmbed extraction
- Optional: `bookmarks` plugin (Elgg core) — bookmark river/profile previews
- Optional: `hypeshortcode` / `shortcodes` service — embed player shortcode + menu

## Custom Database Table

`scraper_data` — columns: `url`, `hash` (SHA1), `data` (JSON from 5.x; was PHP serialize in 4.x).

## Migration Notes (4.x → 5.x)

- All `elgg_register_plugin_hook_handler()` → `elgg_register_event_handler()`
- All handler `__invoke(Hook $hook)` → `__invoke(Event $event)`
- All `elgg_trigger_plugin_hook()` → `elgg_trigger_event_results()`
- `\Elgg\Hook` → `\Elgg\Event` in all type hints
- `AddBookmarkProfilePreview` and `AddBookmarkRiverPreview`: old 4-arg signatures fully updated to single `Event $event` arg; `elgg_instanceof()` replaced with `instanceof` checks
- Plugin settings IDs corrected from camelCase `'hypeScraper'` to lowercase `'hypescraper'`
- `serialize()` → `json_encode()` for new DB saves; `MigrateScraperDataToJson` upgrade batch re-encodes existing rows
- PHP bumped to 8.2, `composer.json` `psr-0` → `psr-4`
- Docker stack upgraded from PHP 7.4/Elgg 4.3.6 to PHP 8.2/Elgg 5.1.4
