## Documentation

### Overview

The Swagger plugin provides a complete, interactive OpenAPI 3.1.0 compliant documentation for your Matomo API. It automatically discovers all installed plugins and their API methods, providing a user-friendly interface to explore and test the Matomo API.

### How to Access the Swagger UI

The Swagger UI is available only to Super Users for security reasons.

**Access Path:**
1. Log in to Matomo as a Super User
2. Navigate to **Administration > Platform > Swagger**
3. The Swagger UI will load with all available API endpoints

### Authentication

The plugin uses modern Bearer token authentication for security.

**Setup Authentication:**
1. Click the **Authorize** button (lock icon) in the top-right of Swagger UI
2. Enter your Matomo API token in the value field
3. Click **Authorize** to save
4. Click **Close** to return to the API documentation

**Finding Your API Token:**
- Go to **Administration > Platform > API**
- Under **User Authentication**, create a new token or copy an existing one
- Optionally restrict the token to POST requests only for enhanced security

**Token Format:**
```
Authorization: Bearer YOUR_API_TOKEN
```

### Using the Swagger UI

**Browsing API Methods:**
- API methods are organized by plugin (tags on the left sidebar)
- Click on any plugin name to expand its available methods
- Each method shows its description, parameters, and response format

**Testing an API Call:**
1. Click on any API endpoint to expand it
2. Click the **Try it out** button
3. Fill in the required parameters (marked with red asterisk)
4. Optionally fill in optional parameters
5. Click **Execute** to send the request
6. View the response below, including status code, headers, and body

**Understanding Parameters:**

- **module**: Always set to "API" (pre-filled, read-only)
- **format**: Response format (json, xml, csv, tsv, html, rss, original) - defaults to json
- **Method-specific parameters**: Each API method has its own parameters documented in the UI

**Response Formats:**

The API supports multiple response formats:
- `json` - JSON format (default, recommended)
- `xml` - XML format
- `csv` - Comma-separated values
- `tsv` - Tab-separated values
- `html` - HTML table format
- `rss` - RSS feed format
- `original` - Original PHP serialized format

### Dynamic API Discovery

The plugin automatically discovers all API methods from:
- **Core Matomo plugins**: Actions, API, Dashboard, Goals, etc.
- **Premium plugins**: AbTesting, Cohorts, Funnels, Heatmaps, etc.
- **Custom plugins**: Any plugin with an API class

**No Manual Configuration Required:**
- Install a new plugin → API methods appear automatically
- Activate a plugin → Methods become available
- Deactivate a plugin → Methods are hidden
- Always in sync with your Matomo installation

### Advanced Features

#### POST Method Support

All API calls use POST methods for enhanced security:
- Compatible with POST-only API token restrictions
- Prevents token exposure in server logs
- Better suited for authenticated API calls
- Supports both form-urlencoded and JSON request bodies

#### Content Type Options

Choose between two request formats:
- **application/x-www-form-urlencoded**: Standard form data (default)
- **application/json**: JSON request bodies

Switch between them in the "Request body" section of each endpoint.

#### Protocol Detection

The plugin automatically detects your server configuration:
- **HTTPS installations**: Uses https:// for all endpoints
- **HTTP installations**: Uses http:// for all endpoints
- **Reverse proxy setups**: Respects X-Forwarded-Proto headers

No manual configuration needed for different environments.

### Embed the Swagger UI (Optional)

You can embed the Swagger UI in your own tools or dashboards using an iframe.

**Basic Embed:**
```html
<iframe src="/index.php?module=Swagger&action=iframe" width="100%" height="800px"></iframe>
```

**Auto-Resize Iframe:**
```html
<!-- Auto resize iframe height (optional) -->
<script>
    function resizeIframe(obj) {
        setInterval(() => {
            obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
        }, 500);
    }
</script>

<!-- Swagger UI (required) -->
<iframe
    src="/index.php?module=Swagger&action=iframe"
    width="100%"
    onload="resizeIframe(this);"
    frameborder="0">
</iframe>
```

**Important Notes:**
- The iframe inherits authentication from the parent Matomo session
- Users must be logged in as Super User to view the embedded Swagger UI
- The iframe URL must be from the same Matomo instance (CORS restrictions apply)

### Export OpenAPI Specification (Optional)

The OpenAPI JSON specification file is accessible via the Matomo API for external tools.

**API Endpoint:**
```
POST /index.php
Content-Type: application/x-www-form-urlencoded

module=API&format=json&method=Swagger.getOpenApi&token_auth=YOUR_TOKEN
```

**Using cURL:**
```bash
curl -X POST "https://your-matomo-domain/index.php" \
  -d "module=API" \
  -d "format=json" \
  -d "method=Swagger.getOpenApi" \
  -d "token_auth=YOUR_API_TOKEN"
```

**Use Cases:**
- Import into external API testing tools (Postman, Insomnia)
- Generate client SDKs using OpenAPI Generator
- Create custom documentation websites
- Integrate with API gateways
- Automated API testing and validation

**OpenAPI File Structure:**
```json
{
  "openapi": "3.1.0",
  "info": {
    "title": "Matomo API",
    "version": "5.6.0"
  },
  "servers": [...],
  "tags": [...],
  "paths": {...},
  "components": {
    "securitySchemes": {
      "BearerAuth": {...}
    }
  }
}
```

### Version Information

The OpenAPI specification automatically includes:
- **Matomo Version**: Dynamically detected from your installation
- **API Version**: Reflects your current Matomo version
- **Plugin Versions**: All installed plugin information

No need to manually update version numbers - it's always accurate.

### Security Considerations

**Access Control:**
- Only Super Users can access the Swagger UI
- Regular users cannot view or use the plugin
- API tokens can be restricted to specific permissions

**Token Security:**
- Use POST-only tokens when possible
- Create separate tokens for Swagger UI testing
- Regularly rotate API tokens
- Never commit tokens to version control
- Tokens are sent in Authorization header (not URL)

**Best Practices:**
- Test API calls in development environment first
- Use read-only methods when exploring the API
- Be careful with DELETE and UPDATE operations
- Monitor API usage in Matomo logs

### Troubleshooting

**Common Issues:**

1. **"You can't access this resource" error**
   - Ensure you're logged in as Super User
   - Check that the plugin is activated

2. **"Unable to authenticate" error**
   - Verify your API token is valid
   - Ensure the token is for a Super User account
   - Try using POST method for token submission

3. **"No operations defined in spec" error**
   - Check Matomo error logs for details
   - Verify all plugins are properly activated
   - Look for PHP errors in the installation

4. **API methods missing**
   - Ensure the plugin is activated
   - Check that the plugin has an API class
   - Try deactivating and reactivating the plugin

5. **Swagger UI not loading**
   - Clear browser cache
   - Check browser console for JavaScript errors
   - Verify no plugin conflicts
   - Ensure JavaScript is enabled

For more help, see the [FAQ](faq.md) or check the [Matomo forums](https://forum.matomo.org/).

### Additional Resources

- [Matomo API Reference](https://developer.matomo.org/api-reference/reporting-api)
- [OpenAPI Specification](https://spec.openapis.org/oas/latest.html)
- [Swagger UI Documentation](https://swagger.io/tools/swagger-ui/)
- [Plugin Changelog](../CHANGELOG.md)
- [Technical Implementation Details](../ENHANCEMENTS.md)
