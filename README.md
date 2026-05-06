# Matomo Swagger Plugin

## Description

Explore and try every Matomo Reporting API method straight from your admin panel, powered by Swagger UI and a fully OpenAPI 3.1.0 compliant specification.

The plugin scans your Matomo installation in real time, picks up every activated plugin's API methods, and exposes them as an interactive, browsable documentation. No static files to maintain, no manual sync after installing or removing a plugin: the spec is generated dynamically from the live container.

It is designed for developers integrating against Matomo, support engineers debugging a customer's setup, and anyone who would rather click "Try it out" than craft `curl` commands by hand.

## What you get

- A `Swagger` entry under **Administration → Platform**, restricted to Super Users.
- An OpenAPI 3.1.0 document served at `?module=API&format=json&method=Swagger.getOpenApi`, suitable for importing into Postman, Insomnia, or any OpenAPI tooling.
- Bearer token authentication that mirrors how Matomo expects API tokens to be sent today (the deprecated `token_auth` query parameter is not used).
- POST as the default verb for every endpoint, so the plugin keeps working even when your token is restricted to POST-only.
- Method names visible inside endpoint paths, so the operation list reads like a table of contents instead of a wall of identical URLs.
- Tags annotated with each plugin's own description and homepage link, so you can tell at a glance what a module does.
- Parameter schemas typed from the real PHP signatures (`int`, `bool`, `array`, …) rather than treated as strings, with `nullable` flagged where it applies.
- Domain-aware schemas for the parameters that have a Matomo-specific shape: `period` is an enum, `idSite`/`idSites` accept "1" / "1,2,3" / "all" via a regex, `date` documents its relative-keyword and range syntax, `segment` and `language` carry usable descriptions.
- Responses describe every format Matomo can return (`json`, `xml`, `csv`, `tsv`, `html`, `rss`, `original`), making the OpenAPI document accurate when imported into client generators.
- Per-method request body examples generated from Matomo's reference documentation generator — "Try it out" opens prefilled with sensible defaults instead of an empty form.

## Requirements

- Matomo 5.0 or later (tested up to the current 5.x line).
- A Super User account: lower roles will get a 401 when opening the Swagger page.
- A Matomo API token with the permissions matching the calls you want to make.

## Installation

Install from the Matomo Marketplace, or drop the plugin folder into `plugins/Swagger` and activate it from **Administration → Platform → Plugins**. No database migration runs; deactivating the plugin leaves no residue.

## Using it

1. Open **Administration → Platform → Swagger**.
2. Click **Authorize** and paste a Matomo API token (find one under **Administration → Personal → Security**).
3. Pick an endpoint, expand it, fill in the parameters, and hit **Try it out**.

The token is sent as `Authorization: Bearer <your token>` on every request. It stays in browser memory for the session and is not persisted by the plugin.

## How it works

The Swagger page is a thin admin shell that embeds Swagger UI in a same-origin iframe. The iframe loads the bundled Swagger UI assets (under `swagger-ui/`) and points them at the dynamically generated OpenAPI document.

The OpenAPI document is built on demand by walking Matomo's API proxy: every loaded plugin contributes its public methods, with parameter names, defaults, and required flags inferred from method signatures and the existing API documentation generator. Because discovery happens at request time, enabling a plugin makes its methods appear immediately, with no rebuild step.

A small CSP adjustment is applied only on the two Swagger controller actions: `frame-src 'self'` for the parent admin page (so the same-origin iframe is allowed) and `img-src validator.swagger.io` for the iframe (so the Swagger UI validator badge can load). These directives are scoped per-response and do not leak onto other Matomo pages.

## Security notes

- The plugin never stores tokens. Anything you type into the Authorize dialog lives in the browser tab.
- All page actions check `Piwik::checkUserHasSuperUserAccess()` before rendering.
- The OpenAPI document itself describes the API surface only; it does not expose credentials, configuration values, or per-site data.

## Troubleshooting

- **Blank iframe or "refused to connect"**: another plugin or a custom CSP rule has overridden `frame-src`. Check `core/View/SecurityPolicy.php` decorators in your other plugins.
- **401 on every call**: the token used in Authorize has been revoked or lacks permissions for the target site. Generate a new one under **Personal → Security**.
- **A plugin's methods are missing**: confirm the plugin is activated. The OpenAPI document only lists methods from plugins currently registered with Matomo's plugin manager.
- **Endpoint hangs in "Try it out"**: usually a server-side timeout on a heavy report. Try the same call with a narrower `period`/`date` first.

## Support

- Issues and feature requests: https://github.com/openmost/Swagger/issues
- Commercial support and integration help: ronan@openmost.io
- Plugin homepage: https://openmost.io/products/swagger/

## License

GPL v3 or later. Swagger UI is bundled under its own Apache 2.0 license; see `swagger-ui/` for details.
