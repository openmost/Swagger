## Changelog

### v6.0.0

**Matomo 6 compatibility**

- Requires Matomo 6 (`>=6.0.0-b1,<7.0.0-b1`) and PHP 8.1+.
- The Swagger page is a Vue component built with Vite. Twig iframe templates and the vendored `swagger-ui/` folder are removed.
- Swagger UI updated from 3.34 to 5.32, bundled with the plugin and only loaded on the Swagger page.
- Swagger UI is rendered in a shadow root instead of an iframe: no more height polling, the Authorize modal is positioned normally, and Matomo and Swagger UI styles no longer conflict. Dark mode follows the Matomo theme.

**New features**

- "Try it out" requests use the current Matomo session by default, no API token needed. A token entered with Authorize still takes precedence.
- Download the OpenAPI JSON from the Swagger page.
- Search field to filter API modules.
- New "Swagger API explorer" widget (About Matomo category), embeddable with the Widgetize module.
- Operation summaries and descriptions read from the method docblocks.
- Generic report parameters documented on report methods (`filter_limit`, `filter_offset`, `filter_sort_column`, `filter_sort_order`, `filter_pattern`, `showColumns`, `hideColumns`, `flat`, `format_metrics`, `percent_of_total`).
- English and French translations.

**Fixes**

- `Swagger.getOpenApi` now requires Super User access. The specification was previously readable anonymously.
- Server URL now includes the Matomo subfolder, so "Try it out" works when Matomo is not installed at the domain root. Swagger UI assets no longer use root-absolute paths either.
- Removed the `application/json` request body: the Matomo API ignores parameters sent as JSON.
- Removed the demo.matomo.cloud server, which could not be called from the browser.
- Array parameters are sent as `name[]` fields so PHP reads them as arrays.
- Optional parameters no longer get empty default values, so Swagger UI does not send empty fields.
- Untyped flags use `0` / `1` values, since PHP reads the string `false` as true.
- Removed `nullable`, which is not part of OpenAPI 3.1.
- Generation errors are returned as API errors instead of an error document exposing file paths.
- Plugin homepage and contact updated to openmost.com.

**Breaking changes**

- The `index.php?module=Swagger&action=iframe` URL is removed. Embed the Swagger API explorer widget with the Widgetize module instead.
- `Swagger.getOpenApi` requires Super User access.

### v5.3.0

**Richer OpenAPI document**

- Tags now carry the module description from each plugin's API class and an `externalDocs` link to the plugin homepage when available.
- Parameter schemas use real PHP types (`int → integer`, `bool → boolean`, `float → number`, `array → array`) instead of treating every input as a string. Nullable parameters are flagged with `nullable: true`.
- Per-method request body examples are generated from Matomo's own `DocumentationGenerator::getExampleUrl()`, so "Try it out" comes prefilled with sensible values.
- Well-known Matomo parameters get domain-aware schemas: `period` is an `enum` (day/week/month/year/range), `idSite`/`idSites` get a regex matching integer / comma-separated list / "all", `date` and `segment` get human-readable descriptions of their accepted shapes, and `language` gets a locale pattern.
- The 200 response now lists every format Matomo can return (`json`, `xml`, `csv`, `tsv`, `html`, `rss`, `original`) instead of only `application/json`, with a description noting that the concrete content type is selected via the `format` parameter.
- `info.contact` and `info.license` populated from the plugin manifest.
- Internal parameters (names starting with `_`) are now hidden, matching Matomo's reference doc generator.
- Required parameter detection fixed (the previous check always evaluated to false because of an `is_object()` on a class-name string).

### v5.2.5

fix: scope CSP `frame-src 'self'` and `img-src validator.swagger.io` to the Swagger pages only. The previous global decoration applied them to every Matomo page and broke the Overlay feature, which iframes cross-origin tracked sites.

### v5.2.3

fix: Authorization modal position in iframe view

### v5.2.2

update: Swagger admin page title

### v5.2.1

fix: CSP issue

### v5.2.0

**Major Release: Full OpenAPI 3.1.0 Compliance & Dynamic API Discovery**

**New Features:**
- Dynamic version detection from Matomo installation
- Dynamic protocol detection (HTTP/HTTPS) based on server configuration
- Bearer token authentication (replaces deprecated token_auth query parameter)
- POST method support for all API endpoints (required for POST-only token restrictions)
- Mandatory `module` and `format` parameters for all API calls
- Clean tag generation without descriptions for better UI experience

**Improvements:**
- 100% dynamic module discovery from installed and activated plugins
- Enhanced OpenAPI 3.1.0 specification compliance
- Proper parameter definitions with required flags and default values
- Support for both `application/x-www-form-urlencoded` and `application/json` content types
- Method names visible in endpoint paths for better comprehensibility
- Lazy plugin registration to avoid container initialization errors
- Performance optimization: metadata loaded once instead of per-method

**Bug Fixes:**
- Fixed NoDefaultValue object handling preventing fatal conversion errors
- Fixed type checking before get_class() calls
- Filtered out translation key placeholders from descriptions
- Filtered out literal "string" placeholder values
- Set empty string defaults for optional parameters

**Breaking Changes:**
- API calls now use POST method instead of GET
- Authentication now uses Bearer token in Authorization header
- Tags no longer include plugin descriptions (names only)

**Technical Details:**
- All enhancements maintain backward compatibility with existing API endpoints
- No database migrations required
- Comprehensive error handling with graceful fallbacks

### v5.1.4

update: marketplace cover

### v5.1.3

update: marketplace category and cover

### v5.1.2

update: Swagger logo

### v5.1.1

Publish the plugin

### v5.1.0

setup: Plugin base
