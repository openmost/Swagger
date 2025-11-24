<?php

namespace Piwik\Plugins\Swagger;

use Piwik\API\Proxy;
use Piwik\API\Request;
use Piwik\Piwik;
use Piwik\Url;
use Piwik\Version;

class API extends \Piwik\Plugin\API
{
    private function ensureAllPluginsRegistered()
    {
        try {
            $plugins = \Piwik\Plugin\Manager::getInstance()->getLoadedPluginsName();
            foreach ($plugins as $plugin) {
                try {
                    $className = Request::getClassNameAPI($plugin);
                    Proxy::getInstance()->registerClass($className);
                } catch (\Exception $e) {
                    // Skip plugins without API class
                }
            }
        } catch (\Exception $e) {
            // If plugin registration fails, continue anyway
        }
    }

    public function getOpenApi()
    {
       // Piwik::checkUserHasSuperUserAccess();

        // Ensure all plugins are registered before generating the spec
        $this->ensureAllPluginsRegistered();

        try {
            $info = $this->getInfo();
            $externalDocs = $this->getExternalDocs();
            $servers = $this->getServers();
            $tags = $this->getTags();
            $paths = $this->getPaths();

            $openapi = [
                "openapi" => "3.1.0",
                "info" => $info,
                "externalDocs" => $externalDocs,
                "servers" => $servers,
                "tags" => $tags,
                "paths" => $paths,
                "components" => [
                    "securitySchemes" => [
                        "BearerAuth" => [
                            "type" => "http",
                            "scheme" => "bearer",
                            "description" => "Matomo API token authentication. Use your Matomo API token as the Bearer token."
                        ]
                    ]
                ],
                "security" => [
                    [
                        "BearerAuth" => [],
                    ]
                ],
            ];

            return $openapi;
        } catch (\Exception $e) {
            return [
                "openapi" => "3.1.0",
                "info" => [
                    "title" => "Matomo API - Error",
                    "version" => "1.0.0",
                    "description" => "Error generating OpenAPI spec: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine()
                ],
                "paths" => []
            ];
        } catch (\Throwable $e) {
            return [
                "openapi" => "3.1.0",
                "info" => [
                    "title" => "Matomo API - Fatal Error",
                    "version" => "1.0.0",
                    "description" => "Fatal error: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine()
                ],
                "paths" => []
            ];
        }
    }


    private function getMetadata()
    {
        return Proxy::getInstance()->getMetadata();
    }

    private function getPluginInformation($moduleName)
    {
        try {
            $pluginManager = \Piwik\Plugin\Manager::getInstance();
            $plugin = $pluginManager->getLoadedPlugin($moduleName);
            if ($plugin) {
                return $plugin->getInformation();
            }
        } catch (\Exception $e) {
            // Plugin might not have metadata
        }
        return null;
    }


    private function getAllApiMethods()
    {
        $result = array();

        foreach (Proxy::getInstance()->getMetadata() as $class => $info) {
            $moduleName = Proxy::getInstance()->getModuleNameFromClassName($class);
            foreach ($info as $actionName => $infoMethod) {
                if ($actionName !== '__documentation' && $actionName !== 'usesAutoSanitizeInputParams') {
                    $method = "$moduleName.$actionName";
                    $result[$method] = array(
                        'module' => $moduleName,
                        'action' => $actionName,
                        'method' => $method,
                        'parameters' => isset($infoMethod['parameters']) ? $infoMethod['parameters'] : array(),
                        'isDeprecated' => isset($infoMethod['isDeprecated']) ? $infoMethod['isDeprecated'] : false,
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Get OpenAPI info section with dynamic Matomo version
     *
     * @return array
     */
    private function getInfo()
    {
        $info = [
            "title" => "Matomo API",
            "summary" => "Matomo reporting API",
            "description" => "Complete Matomo reporting API documentation",
            "version" => Version::VERSION  // Dynamically retrieved from Matomo installation
        ];

        return $info;
    }

    private function getExternalDocs()
    {
        $externalDocs = [
            "description" => "Official Matomo documentation",
            "url" => "https://developer.matomo.org/api-reference/reporting-api"
        ];

        return $externalDocs;
    }

    /**
     * Get OpenAPI servers list with dynamic protocol detection
     *
     * @return array
     */
    private function getServers()
    {
        $host = Url::getHost();
        $scheme = Url::getCurrentScheme();

        $servers = [
            [
                "url" => "$scheme://$host",
                "description" => "This Matomo server",
            ],
            [
                "url" => "https://demo.matomo.cloud",
                "description" => "The Matomo demo server",
            ]
        ];

        return $servers;
    }

    /**
     * Generate OpenAPI tags dynamically from installed plugins
     *
     * This method generates tags based on the loaded plugins in Matomo,
     * ensuring all modules are discovered dynamically from the current installation.
     *
     * @return array Array of tag definitions with module names
     */
    private function getTags()
    {
        $tags = [];

        foreach (Proxy::getInstance()->getMetadata() as $class => $info) {
            $moduleName = Proxy::getInstance()->getModuleNameFromClassName($class);
            $tag = [
                'name' => $moduleName,
            ];

            $tags[] = $tag;
        }

        return $tags;
    }

    /**
     * Generate OpenAPI paths dynamically from all available API methods
     *
     * This method generates API paths based on all registered API methods from loaded plugins.
     * It includes proper OpenAPI 3.1.0 structure with parameters, responses, and documentation.
     *
     * @return array Array of path definitions
     */
    private function getPaths()
    {
        $paths = [];
        $metadata = $this->getMetadata(); // Get metadata once, not in every iteration

        foreach ($this->getAllApiMethods() as $method) {
            $operationId = str_replace('.', '_', $method['method']);
            $summary = $method['action'] . ' from ' . $method['module'];

            // Get method documentation if available from API metadata
            $description = '';
            foreach ($metadata as $class => $info) {
                if (Proxy::getInstance()->getModuleNameFromClassName($class) === $method['module']) {
                    if (isset($info[$method['action']]['description'])) {
                        $description = $info[$method['action']]['description'];
                    }
                    break;
                }
            }

            // Build path with method in query string for clarity
            $pathKey = '/index.php?method=' . $method['method'];
            $paths[$pathKey] = [
                "post" => [
                    "summary" => $summary,
                    "description" => $description ?: "Execute " . $method['method'] . " API method",
                    "operationId" => $operationId,
                    "tags" => [
                        $method['module'],
                    ],
                    "deprecated" => $method['isDeprecated'],
                    "requestBody" => [
                        "required" => false,
                        "content" => [
                            "application/x-www-form-urlencoded" => [
                                "schema" => [
                                    "type" => "object",
                                    "required" => ["module", "format"],
                                    "properties" => $this->getPostBodyProperties($method)
                                ]
                            ],
                            "application/json" => [
                                "schema" => [
                                    "type" => "object",
                                    "required" => ["module", "format"],
                                    "properties" => $this->getPostBodyProperties($method)
                                ]
                            ]
                        ]
                    ],
                    "responses" => [
                        "200" => [
                            "description" => "Successful response",
                            "content" => [
                                "application/json" => [
                                    "schema" => [
                                        "type" => "object"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
            ];
        }

        return $paths;
    }

    private function getRequiredProperties($method)
    {
        $required = [];

        if (isset($method['parameters'])) {
            foreach ($method['parameters'] as $parameter => $config) {
                if ($config['default'] === is_object('Piwik\API\NoDefaultValue')) {
                    $required[] = $parameter;
                }
            }
        }

        return $required;
    }

    /**
     * Generate OpenAPI parameter definitions for a specific API method
     *
     * Converts Matomo API method parameters into OpenAPI 3.1.0 parameter format,
     * including proper types, required flags, descriptions, and default values.
     *
     * @param array $method Method information including parameters
     * @return array Array of parameter definitions
     */
    private function getParametersArray($method)
    {
        $parameters = [
            [
                "name" => "method",
                "in" => "query",
                "required" => true,
                "description" => "API method to call",
                "schema" => [
                    "type" => "string",
                    "example" => $method["method"]
                ]
            ],
            [
                "name" => "format",
                "in" => "query",
                "required" => false,
                "description" => "Response format",
                "schema" => [
                    "type" => "string",
                    "enum" => ["json", "xml", "csv", "tsv", "html", "rss", "original"],
                    "default" => "json"
                ]
            ],
        ];

        if (isset($method['parameters'])) {
            foreach ($method['parameters'] as $parameter => $config) {
                // Check if parameter is required (has NoDefaultValue or no default at all)
                $hasDefault = isset($config['default']);
                $isNoDefaultValue = $hasDefault && is_object($config['default']) &&
                                   get_class($config['default']) === 'Piwik\API\NoDefaultValue';
                $isRequired = !$hasDefault || $isNoDefaultValue;

                $param = [
                    'name' => $parameter,
                    'in' => 'query',
                    'required' => $isRequired,
                    'schema' => [
                        'type' => 'string',
                    ]
                ];

                // Add description if available and not a translation key
                if (isset($config['description']) &&
                    !empty($config['description']) &&
                    !preg_match('/^[A-Z][a-zA-Z]+_[a-zA-Z_]+$/', $config['description'])) {
                    $param['description'] = $config['description'];
                }

                // Add default value: use meaningful value if exists, otherwise empty string for optional params
                if ($hasDefault &&
                    !is_object($config['default']) &&
                    $config['default'] !== null &&
                    trim((string)$config['default']) !== '' &&
                    strtolower(trim((string)$config['default'])) !== 'string') {
                    // Has a meaningful default value
                    $param['schema']['default'] = $config['default'];
                } elseif (!$isRequired) {
                    // Optional parameter with no meaningful default - set empty string
                    $param['schema']['default'] = '';
                }

                $parameters[] = $param;
            }
        }

        return $parameters;
    }

    /**
     * Generate OpenAPI POST body properties for a specific API method
     *
     * Creates properties for request body parameters in POST requests.
     * Includes format parameter and all method-specific parameters.
     *
     * @param array $method Method information including parameters
     * @return array Array of property definitions for request body
     */
    private function getPostBodyProperties($method)
    {
        $properties = [
            "module" => [
                "type" => "string",
                "enum" => ["API"],
                "default" => "API",
                "description" => "API module name (required)"
            ],
            "format" => [
                "type" => "string",
                "enum" => ["json", "xml", "csv", "tsv", "html", "rss", "original"],
                "default" => "json",
                "description" => "Response format (required)"
            ],
        ];

        if (isset($method['parameters'])) {
            foreach ($method['parameters'] as $parameter => $config) {
                // Check if parameter is required (has NoDefaultValue or no default at all)
                $hasDefault = isset($config['default']);
                $isNoDefaultValue = $hasDefault && is_object($config['default']) &&
                                   get_class($config['default']) === 'Piwik\API\NoDefaultValue';

                $prop = [
                    'type' => 'string',
                ];

                // Add description if available and not a translation key
                if (isset($config['description']) &&
                    !empty($config['description']) &&
                    !preg_match('/^[A-Z][a-zA-Z]+_[a-zA-Z_]+$/', $config['description'])) {
                    $prop['description'] = $config['description'];
                }

                // Add default value: use meaningful value if exists, otherwise empty string for optional params
                if ($hasDefault &&
                    !is_object($config['default']) &&
                    $config['default'] !== null &&
                    trim((string)$config['default']) !== '' &&
                    strtolower(trim((string)$config['default'])) !== 'string') {
                    // Has a meaningful default value
                    $prop['default'] = $config['default'];
                } elseif (!$isNoDefaultValue) {
                    // Optional parameter with no meaningful default - set empty string
                    $prop['default'] = '';
                }

                $properties[$parameter] = $prop;
            }
        }

        return $properties;
    }

    private function getProperties($method)
    {
        $properties = [
            "module" => [
                "name" => "module",
                "in" => "query",
                "examples" => ["API"],
                "schema" => [
                    "type" => "string",
                ]
            ],
            "format" => [
                "name" => "format",
                "in" => "query",
                "examples" => [
                    "json",
                    "xml",
                    "csv",
                    "tsv",
                    "html",
                    "rss",
                    "original"
                ],
                "schema" => [
                    "type" => "string",
                ]
            ],
            "method" => [
                "name" => "method",
                "in" => "query",
                "examples" => [
                    $method["method"]
                ],
                "schema" => [
                    "type" => "string",
                ]
            ],
        ];

        if (isset($method['parameters'])) {
            foreach ($method['parameters'] as $parameter => $config) {
                $properties[$parameter] = [
                    'name' => $parameter,
                    'in' => 'query',
                    'examples' => [
                        '', '',
                    ],
                    'schema' => [
                        'type' => 'string',
                    ]
                ];
            }
        }

        return $properties;
    }
}
