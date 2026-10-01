# Swagger

Explore and try every Matomo API method from your admin panel, with Swagger UI and an OpenAPI 3.1 specification generated live from your activated plugins.

## Features

- **Interactive API explorer** under **Administration > Platform > Swagger**, powered by Swagger UI and displayed in a same-origin frame.
- **Always in sync**: the OpenAPI 3.1 document is generated on demand from the plugins activated on your Matomo, so installing or removing a plugin updates it immediately. No static file to maintain.
- **Bearer token authentication**: click **Authorize** and paste a Matomo API token, it is sent as an `Authorization: Bearer` header, never as a `token_auth` query parameter.
- **Accurate documentation**: modules described with each plugin's own description and homepage link, parameters typed from the PHP signatures (`int`, `bool`, `float`, `array`) with required flags and defaults, and request examples from Matomo's own API documentation generator.
- **Matomo-aware parameters**: `period`, `idSite` / `idSites` (`1`, `1,2,3` or `all`), `date` keywords and ranges, `segment` and `language` are documented.
- **Works with POST-only tokens**: every method is described as a POST request, and every response format Matomo can return is documented (`json`, `xml`, `csv`, `tsv`, `html`, `rss`, `original`).
- **Importable OpenAPI document**, served by the `Swagger.getOpenApi` API method (`?module=API&format=json&method=Swagger.getOpenApi`) with a Super User token, for Postman, Insomnia or an SDK generator.
- **Works from a subdirectory**: the server URL and the Swagger UI assets keep the path Matomo is installed in.

The session-based "Try it out", the search field, the one-click download, the widget, dark mode and the translations are only available in Swagger 6.x, for Matomo 6.

## Requirements

- Matomo 5.10.0 or higher (`>=5.10.0,<6.0.0-b1`)
- A Super User account
- A Matomo API token with the permissions matching the calls you want to make

## Installation / Configuration

1. Install the plugin from the Matomo Marketplace (**Administration > Marketplace**), or copy it to `plugins/Swagger` and activate it under **Administration > System > Plugins**. The plugin creates no database table and leaves no data behind when deactivated.
2. Open **Administration > Platform > Swagger**.
3. Click **Authorize** and paste a token created under **Administration > Personal > Security**.
4. Expand an API module, open a method, click **Try it out**, fill in the parameters and click **Execute**.

There is no setting. The plugin only relaxes the Content Security Policy of its own two pages (`frame-src 'self'` for the frame, `img-src validator.swagger.io` for the Swagger UI validator badge).

## Privacy and data

- The Swagger page and the `Swagger.getOpenApi` method require Super User access.
- The plugin never stores tokens: a token entered with **Authorize** only lives in the browser tab.
- The OpenAPI document describes the API methods only. It contains no credentials, configuration values or website data.
- "Try it out" requests go to your own Matomo server. The document also lists the public demo.matomo.cloud server as an alternative target, and the Swagger UI validator badge is loaded from validator.swagger.io.

## Need help with Matomo?

Openmost is an official Matomo Implementation Partner. When you are ready to connect Matomo to your CMS, CRM, BI tools or data warehouse, we build [Matomo integrations](https://openmost.com/matomo/services/integration?utm_source=matomo_marketplace&utm_medium=referral&utm_campaign=services&utm_content=swagger) on the official APIs, with documented data flows your team can take over.

## Support

- Homepage: <https://openmost.com/matomo/extensions/swagger>
- Email: [ronan@openmost.com](mailto:ronan@openmost.com)
- Source code and issues: <https://github.com/openmost/Swagger>

## Screenshots

See the `screenshots/` folder: the API explorer generated from your plugins, authorization with a Bearer API token, and the typed parameters with the Matomo documentation.

## License

GPL v3 or later. Swagger UI is bundled under the Apache 2.0 license, see `swagger-ui/`.
