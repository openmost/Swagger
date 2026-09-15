## FAQ

**Which Matomo versions are supported?**

Version 6.x of the plugin supports Matomo 6. For Matomo 5, install version 5.x of the plugin.

**Who can use the API explorer?**

Only Super Users. The explorer gives access to every API method, including administration methods, so it is restricted like the other platform tools.

**Do I need an API token to try the API?**

No. By default, requests are sent with your current Matomo session. Use the **Authorize** button when you want to test a specific API token.

**Can I use a token restricted to POST requests?**

Yes. Every method is called with a POST request and the token is sent in the `Authorization: Bearer` header.

**Do I need to update anything when I install a new plugin?**

No. The specification is generated from the activated plugins each time the explorer is opened.

**Which OpenAPI version is generated?**

OpenAPI 3.1.0.

**Can I import the specification in Postman or generate an SDK?**

Yes. Click **Download OpenAPI JSON**, or request the `Swagger.getOpenApi` API method with a Super User token. See the documentation for an example.

**Why can't I send a JSON request body?**

The Matomo API reads its parameters from the query string and form fields only, so the specification describes form requests.

**Can I embed the explorer in another application?**

Yes, with the **Swagger API explorer** widget and the Widgetize module. See the documentation for an example.

**Does the plugin slow down Matomo?**

No. Swagger UI is only loaded on the explorer page, and the specification is only generated when the explorer is opened.

**Does the plugin store data?**

No. It creates no database table and never stores tokens.
