## Documentation

### 1 - Install the plugin

Install this plugin from the Marketplace as a Super User, or download it and copy it to the `/plugins` folder of your Matomo.

Then activate the plugin under **Administration > System > Plugins**.

### 2 - Open the API explorer

As a Super User, go to **Administration > Platform > Swagger**.

The page lists every Reporting API module of your Matomo, each with the description of its plugin. Expand a module to see its methods. The specification is generated from the activated plugins each time the page is opened: when you activate or deactivate a plugin, its methods appear or disappear automatically.

### 3 - Authorize an API token

1. Click **Authorize**.
2. Paste an API token. Tokens are created under **Administration > Personal > Security**, and can be restricted to POST requests.
3. Click **Authorize**, then **Close**.

The token is sent in the `Authorization: Bearer` header and is never stored by the plugin.

### 4 - Try an API method

1. Expand a method, for example `VisitsSummary.get`.
2. Click **Try it out**.
3. Fill in the required parameters, marked with a red asterisk. Examples are suggested for `idSite`, `period` and `date`.
4. Keep the `application/x-www-form-urlencoded` request body: the Matomo API reads its parameters from form fields, not from a JSON body.
5. Click **Execute** to send the request and read the response.

### 5 - Export the OpenAPI specification

The specification is returned by the `Swagger.getOpenApi` API method, which requires a Super User token. Import it in Postman, Insomnia, an API gateway or an SDK generator such as OpenAPI Generator:

```bash
curl -X POST "https://matomo.example.com/index.php" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d "module=API" \
  -d "method=Swagger.getOpenApi" \
  -d "format=json"
```

### How the specification is built

- Every API method is described as a POST request on `index.php`, with the method name in the path.
- Parameter types come from the PHP signature of each method. Parameters without a default value are required. Internal parameters (starting with `_`) are hidden.
- `period`, `idSite` / `idSites`, `date`, `segment` and `language` get Matomo-specific schemas and descriptions.
- Request examples come from Matomo's own API documentation generator.
- The server URL is the URL you use to open Matomo, so the explorer also works when Matomo is installed in a subfolder.
- The response documents every format Matomo can return: `json`, `xml`, `csv`, `tsv`, `html`, `rss` and `original`.

### Troubleshooting

**The page shows "You can't access this resource"**

The Swagger page requires Super User access.

**The page stays blank**

Another plugin or a custom Content Security Policy may override `frame-src`. The plugin only allows `frame-src 'self'` on its own page, to display Swagger UI in a same-origin frame.

**A plugin's methods are missing**

Check that the plugin is activated. Only the API methods of activated plugins are listed.

**A request returns "token_auth is not valid"**

The token entered with **Authorize** is invalid, revoked, or lacks access to the requested site.

**A request is slow or times out**

Heavy reports can take time to process. Try a shorter `period` and `date` first, or lower `filter_limit`.
