# Swagger

Explore and try every Matomo API method from your admin panel, with Swagger UI and an OpenAPI 3.1 specification generated live from your activated plugins.

## Features

- **Interactive API explorer** under **Administration > Platform > Swagger**, powered by Swagger UI 5, with a search field to filter the API modules and dark mode that follows the Matomo theme.
- **Always in sync**: the OpenAPI 3.1 document is generated on demand from the plugins activated on your Matomo, so installing or removing a plugin updates it immediately. No static file to maintain.
- **No token needed to try the API**: "Try it out" requests use your current Matomo session. Untick the session option and use **Authorize** when you want to test a specific API token.
- **Accurate documentation**: summaries, descriptions and deprecation flags read from the method docblocks, parameters typed from the PHP signatures (`int`, `bool`, `float`, `array`) with required flags and defaults, and request examples from Matomo's own API documentation generator.
- **Matomo-aware parameters**: `period`, `idSite` (`1`, `1,2,3` or `all`), `date` keywords and ranges, `segment` and `language` are documented, and the generic report parameters (`filter_limit`, `filter_offset`, `filter_sort_column`, `filter_sort_order`, `filter_pattern`, `showColumns`, `hideColumns`, `flat`, `format_metrics`, `percent_of_total`) are listed on every report method.
- **Works with POST-only tokens**: every method is described as a POST request, and every response format Matomo can return is documented (`json`, `xml`, `csv`, `tsv`, `html`, `rss`, `original`).
- **Download the OpenAPI JSON** in one click, for Postman, Insomnia or an SDK generator. The document is also served by the `Swagger.getOpenApi` API method.
- **Works from a subdirectory**: the server URL keeps the path Matomo is installed in.
- **Embeddable**: a "Swagger API explorer" widget (About Matomo category) can be added to a dashboard or embedded with the Widgetize module.
- **12 languages**: English, Arabic, Chinese (Simplified), Chinese (Traditional), Dutch, French, German, Italian, Japanese, Polish, Portuguese and Spanish.

## Requirements

- Matomo 6 (`>=6.0.0-b1,<7.0.0-b1`)
- A Super User account

## Installation / Configuration

1. Install the plugin from the Matomo Marketplace (**Administration > Marketplace**), or copy it to `plugins/Swagger` and activate it under **Administration > System > Plugins**. The plugin creates no database table and leaves no data behind when deactivated.
2. Open **Administration > Platform > Swagger**.
3. Filter or expand an API module, open a method, click **Try it out**, fill in the parameters and click **Execute**.

To test with an API token instead of your session, untick **Send requests with my current Matomo session**, click **Authorize** and paste a token created under **Administration > Personal > Security**.

There is no setting.

## Privacy and data

- The Swagger page, the widget and the `Swagger.getOpenApi` method require Super User access.
- The plugin never stores tokens: a token entered with **Authorize** only lives in the browser tab.
- The OpenAPI document describes the API methods only. It contains no credentials, configuration values or website data.
- "Try it out" requests go to your own Matomo server only.

## Need help with Matomo?

Openmost is an official Matomo Implementation Partner. When you are ready to connect Matomo to your CMS, CRM, BI tools or data warehouse, we build [Matomo integrations](https://openmost.com/matomo/services/integration?utm_source=matomo_marketplace&utm_medium=referral&utm_campaign=services&utm_content=swagger) on the official APIs, with documented data flows your team can take over.

## Support

- Homepage: <https://openmost.com/matomo/extensions/swagger>
- Email: [ronan@openmost.com](mailto:ronan@openmost.com)
- Source code and issues: <https://github.com/openmost/Swagger>

## Screenshots

See the `screenshots/` folder: the API explorer generated from your plugins, "Try it out" with your Matomo session, and the typed parameters with the Matomo documentation.

## License

GPL v3 or later. Swagger UI is bundled under the Apache 2.0 license.
