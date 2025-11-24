# Matomo Swagger Plugin

## Description

Read and interact with Matomo API through Swagger UI with full OpenAPI 3.1.0 compliance.

This plugin brings the OpenAPI standard to your Matomo Instance, providing a complete, interactive API documentation that automatically discovers all installed plugins and their API methods.

## Features

- **OpenAPI 3.1.0 Compliant**: Full specification compliance with proper schemas, parameters, and responses
- **Dynamic API Discovery**: Automatically detects all installed and activated plugins
- **Interactive Documentation**: Test API calls directly from the Swagger UI
- **Modern Authentication**: Bearer token authentication support
- **Flexible Protocol Support**: Works with both HTTP and HTTPS installations
- **POST Method Support**: Compatible with POST-only API token restrictions
- **Real-time Updates**: No manual configuration needed when plugins are installed/removed
- **Clean UI**: Optimized interface for better user experience

## Requirements

- Matomo 4.0 or higher
- Super User access to view and use the Swagger UI
- Valid Matomo API token for authentication

## Quick Start

1. Install the plugin from the Matomo Marketplace
2. Navigate to **Administration > Platform > Swagger**
3. Click **Authorize** and enter your Matomo API token
4. Browse and test any API endpoint from your installed plugins

## Authentication

The plugin uses Bearer token authentication. To authenticate:

1. Click the **Authorize** button in Swagger UI
2. Enter your Matomo API token (found in **Administration > Platform > API**)
3. Click **Authorize** to save

Your token will be sent as: `Authorization: Bearer YOUR_TOKEN`