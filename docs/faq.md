## FAQ

### Installation & Setup

**How do I install this plugin?**

This plugin is available in the official Matomo Marketplace:

1. Go to **Administration > Platform > Marketplace**
2. Select **Plugins** from the dropdown
3. Search for **Swagger**
4. Click **Install** and then **Activate**
5. Access it from **Administration > Platform > Swagger**

**Where do I find my API token?**

Your Matomo API token is located at:
**Administration > Platform > API > User Authentication**

You can create a new token or use an existing one with appropriate permissions.

**Can I restrict the API token to POST requests only?**

Yes! The Swagger plugin fully supports POST-only API tokens. When creating or editing an API token, you can restrict it to POST requests for enhanced security. The Swagger UI will use POST methods for all API calls.

### Access & Permissions

**Who can access the Swagger UI?**

Only Super Users have access to the Swagger plugin. This is a security measure since the Swagger UI provides access to all API methods, including administrative functions.

**Can I give access to non-Super Users?**

Not directly through this plugin. However, you can:
- Export the OpenAPI JSON file and host it on a separate Swagger UI instance
- Grant users the specific API tokens with limited permissions
- Use Matomo's built-in role management for API access

### Usage

**What does this plugin do?**

The Swagger plugin generates a complete OpenAPI 3.1.0 compliant documentation for your Matomo API based on your installed plugins. It provides:
- Interactive API documentation
- Ability to test API calls directly from the browser
- Automatic discovery of all available API methods
- Real-time parameter validation
- Response previews with examples

**Do I need to update the plugin when I install new Matomo plugins?**

No! The Swagger plugin automatically discovers all installed and activated plugins. When you install or activate a new plugin with API methods, they will automatically appear in the Swagger UI without any manual configuration.

**Why are some API methods not showing up?**

API methods will only appear if:
- The plugin is activated in Matomo
- The plugin has an API class with public methods
- The plugin API is properly registered

Try deactivating and reactivating the plugin if methods are missing.

**Can I use the Swagger UI to test API calls?**

Yes! The Swagger UI is fully interactive:
1. Click **Authorize** and enter your API token
2. Browse to any API endpoint
3. Click **Try it out**
4. Fill in the required parameters
5. Click **Execute** to see the response

### Technical Questions

**What OpenAPI version does this plugin support?**

The plugin generates OpenAPI 3.1.0 compliant specifications, the latest version of the OpenAPI standard.

**Can I export the OpenAPI specification?**

Yes! The OpenAPI JSON file is available at:
```
POST /index.php
Body: module=API&format=json&method=Swagger.getOpenApi&token_auth=YOUR_TOKEN
```

Or access it through the API with your token.

**Does this plugin work with HTTP and HTTPS?**

Yes! The plugin automatically detects your server's protocol (HTTP or HTTPS) and configures the API endpoints accordingly. It works with:
- Standard HTTPS installations
- HTTP development environments
- Reverse proxy configurations

**What content types are supported?**

The Swagger UI supports both:
- `application/x-www-form-urlencoded` (standard form data)
- `application/json` (JSON request bodies)

You can switch between them in the Swagger UI for each request.

**Are there any breaking changes from previous versions?**

Version 5.2.0 introduces some changes:
- API calls now use POST method instead of GET (more secure)
- Authentication uses Bearer token in Authorization header (modern standard)
- Plugin descriptions removed from tags (cleaner UI)

All existing API endpoints remain compatible.

### Development & Contribution

**How can I contribute to this plugin?**

Contributions are welcome! You can:
- Report issues on the project repository
- Submit pull requests with improvements
- Suggest new features
- Help improve documentation
- Test the plugin with different Matomo configurations

**How long will this plugin be maintained?**

This plugin is actively maintained. As the developer uses Matomo across multiple projects, updates and bug fixes are prioritized to ensure compatibility with new Matomo versions.

**Can I embed the Swagger UI in my own application?**

Yes! See the [documentation](index.md#embed-the-swagger-optional) for details on embedding the Swagger UI via iframe.

### Troubleshooting

**I'm getting authentication errors**

Make sure:
- Your API token is valid and not expired
- You're using a Super User token
- The token is sent as POST parameter or Bearer header
- You've clicked **Authorize** in the Swagger UI

**The Swagger UI is not loading**

Check that:
- You have Super User access
- The plugin is activated
- Your browser allows JavaScript execution
- There are no conflicting plugins

**API calls are failing with 403 errors**

This usually means:
- Your API token doesn't have sufficient permissions
- The token is expired or invalid
- You're not authenticated in Matomo

**I see "No operations defined in spec" error**

This indicates an error generating the OpenAPI specification. Check:
- Matomo error logs for details
- That all plugins are properly activated
- That there are no PHP errors in the Matomo installation
