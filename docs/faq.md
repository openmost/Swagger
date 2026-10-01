## FAQ

**Which Matomo versions are supported?**

Version 5.x of the plugin supports Matomo 5.10.0 or higher. For Matomo 6, install version 6.x of the plugin, which also lets you try the API with your Matomo session, without a token.

**Who can use the API explorer?**

Only Super Users. The explorer gives access to every API method, including administration methods, so it is restricted like the other platform tools.

**Do I need an API token to try the API?**

Yes. Click **Authorize** and paste a token created under **Administration > Personal > Security**.

**Can I use a token restricted to POST requests?**

Yes. Every method is called with a POST request and the token is sent in the `Authorization: Bearer` header.

**Do I need to update anything when I install a new plugin?**

No. The specification is generated from the activated plugins each time the explorer is opened.

**Which OpenAPI version is generated?**

OpenAPI 3.1.0.

**Can I import the specification in Postman or generate an SDK?**

Yes. Request the `Swagger.getOpenApi` API method with a Super User token. See the documentation for an example.

**Why should I not send a JSON request body?**

The Matomo API reads its parameters from the query string and form fields only. Keep the `application/x-www-form-urlencoded` request body in Swagger UI.

**Does the plugin work when Matomo is installed in a subfolder?**

Yes. The server URL and the Swagger UI assets keep the path Matomo is installed in.

**Does the plugin slow down Matomo?**

No. Swagger UI is only loaded on the explorer page, and the specification is only generated when the explorer is opened.

**Does the plugin store data?**

No. It creates no database table and never stores tokens.
