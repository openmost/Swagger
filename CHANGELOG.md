## Changelog

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
