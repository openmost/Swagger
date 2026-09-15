## Documentation

### 1 - Install the plugin

Install this plugin from the Marketplace as a Super User, or download it and copy it to the `/plugins` folder of your Matomo.

Then activate the plugin under **Administration > System > Plugins**.

### 2 - Open the API explorer

As a Super User, go to **Administration > Platform > Swagger**.

The page lists every Reporting API module of your Matomo. Use the **Filter by tag** field to find a module, then expand it to see its methods. The specification is generated from the activated plugins each time the page is opened: when you activate or deactivate a plugin, its methods appear or disappear automatically.

### 3 - Try an API method

1. Expand a method, for example `VisitsSummary.get`.
2. Click **Try it out**.
3. Fill in the required parameters, marked with a red asterisk. Examples are suggested for `idSite`, `period` and `date`.
4. Click **Execute** to send the request and read the response.

Report methods also document the generic parameters applied by Matomo to every report: `filter_limit`, `filter_offset`, `filter_sort_column`, `filter_sort_order`, `filter_pattern`, `showColumns`, `hideColumns`, `flat`, `format_metrics` and `percent_of_total`.

### 4 - Choose how requests are authenticated

**With your Matomo session (default)**

The **Send requests with my current Matomo session** option sends your requests the same way the Matomo interface calls the API. No token is needed.

The curl command displayed after a request then contains your session token. This token only works together with your browser session, but avoid sharing screenshots of it.

**With an API token**

To check what a specific token is allowed to do:

1. Untick **Send requests with my current Matomo session**.
2. Click **Authorize** and paste an API token. Tokens are created under **Administration > Personal > Security**.
3. Click **Authorize**, then **Close**.

The token is sent in the `Authorization: Bearer` header and is never stored by the plugin.

### 5 - Download the OpenAPI specification

Click **Download OpenAPI JSON** to save the specification, then import it in Postman, Insomnia, an API gateway or an SDK generator such as OpenAPI Generator.

The specification can also be requested with the Matomo API, using a Super User token:

```bash
curl -X POST "https://matomo.example.com/index.php" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d "module=API" \
  -d "method=Swagger.getOpenApi" \
  -d "format=json"
```

### 6 - Embed the explorer (optional)

The explorer is also available as the **Swagger API explorer** widget, in the **About Matomo** category of the dashboard. It can be embedded in another tool with the Widgetize module:

```html
<iframe
    src="https://matomo.example.com/index.php?module=Widgetize&action=iframe&moduleToWidgetize=Swagger&actionToWidgetize=getSwaggerUi&idSite=1&period=day&date=yesterday&disableLink=1&widget=1&token_auth=YOUR_API_TOKEN"
    width="100%"
    height="900"
    frameborder="0">
</iframe>
```

Only Super User tokens can load the widget. Anyone who can read the page embedding the iframe can use this token, so only embed it in private tools.

### How the specification is built

- Every API method is described as a POST request on `index.php`, with the method name in the path and the parameters in an `application/x-www-form-urlencoded` body.
- Parameter types come from the PHP signature of each method. Parameters without a default value are required.
- Array parameters are sent as `name[]` fields, the format PHP expects.
- Summaries and descriptions come from the documentation of each method in the source code. Deprecated methods are flagged.
- The server URL is the URL you use to open Matomo, so the explorer also works when Matomo is installed in a subfolder.
- Matomo returns API errors with a 200 status code and a `result: error` body.

### Troubleshooting

**The page shows "You can't access this resource"**

The Swagger page requires Super User access.

**A plugin's methods are missing**

Check that the plugin is activated. Only the API methods of activated plugins are listed.

**A request returns "token_auth is not valid"**

The token entered with **Authorize** is invalid or revoked, or the session option is unticked while no token is authorized.

**A request is slow or times out**

Heavy reports can take time to process. Try a shorter `period` and `date` first, or lower `filter_limit`.
