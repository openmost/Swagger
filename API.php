<?php

namespace Piwik\Plugins\Swagger;

use Piwik\API\DocumentationGenerator;
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

        $this->ensureAllPluginsRegistered();

        try {
            $openapi = [
                "openapi" => "3.1.0",
                "info" => $this->getInfo(),
                "externalDocs" => $this->getExternalDocs(),
                "servers" => $this->getServers(),
                "tags" => $this->getTags(),
                "paths" => $this->getPaths(),
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
        } catch (\Throwable $e) {
            return [
                "openapi" => "3.1.0",
                "info" => [
                    "title" => "Matomo API - Error",
                    "version" => "1.0.0",
                    "description" => "Error generating OpenAPI spec: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine()
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
                if ($actionName === '__documentation' || $actionName === 'usesAutoSanitizeInputParams') {
                    continue;
                }
                $method = "$moduleName.$actionName";
                $result[$method] = array(
                    'class' => $class,
                    'module' => $moduleName,
                    'action' => $actionName,
                    'method' => $method,
                    'parameters' => isset($infoMethod['parameters']) ? $infoMethod['parameters'] : array(),
                    'isDeprecated' => isset($infoMethod['isDeprecated']) ? $infoMethod['isDeprecated'] : false,
                );
            }
        }

        return $result;
    }

    private function getInfo()
    {
        return [
            "title" => "Matomo API",
            "summary" => "Matomo reporting API",
            "description" => "Complete Matomo reporting API documentation, dynamically generated from the activated plugins on this Matomo installation.",
            "version" => Version::VERSION,
            "contact" => [
                "name" => "Openmost",
                "url" => "https://openmost.io/products/swagger/",
                "email" => "ronan@openmost.io",
            ],
            "license" => [
                "name" => "GPL v3+",
                "url" => "https://www.gnu.org/licenses/gpl-3.0.html",
            ],
        ];
    }

    private function getExternalDocs()
    {
        return [
            "description" => "Official Matomo documentation",
            "url" => "https://developer.matomo.org/api-reference/reporting-api"
        ];
    }

    private function getServers()
    {
        $host = Url::getHost();
        $scheme = Url::getCurrentScheme();

        return [
            [
                "url" => "$scheme://$host",
                "description" => "This Matomo server",
            ],
            [
                "url" => "https://demo.matomo.cloud",
                "description" => "The Matomo demo server",
            ]
        ];
    }

    private function getTags()
    {
        $tags = [];

        foreach (Proxy::getInstance()->getMetadata() as $class => $info) {
            $moduleName = Proxy::getInstance()->getModuleNameFromClassName($class);
            $tag = ['name' => $moduleName];

            if (isset($info['__documentation']) && is_string($info['__documentation'])) {
                $description = trim(strip_tags($info['__documentation']));
                if ($description !== '') {
                    $tag['description'] = $description;
                }
            }

            $pluginInfo = $this->getPluginInformation($moduleName);
            if (!empty($pluginInfo['homepage'])) {
                $tag['externalDocs'] = [
                    'description' => 'Plugin homepage',
                    'url' => $pluginInfo['homepage'],
                ];
            }

            $tags[] = $tag;
        }

        return $tags;
    }

    private function getPaths()
    {
        $paths = [];
        $metadata = $this->getMetadata();
        $documentationGenerator = $this->buildDocumentationGenerator();

        foreach ($this->getAllApiMethods() as $method) {
            $operationId = str_replace('.', '_', $method['method']);
            $summary = $method['action'] . ' from ' . $method['module'];

            $description = '';
            foreach ($metadata as $class => $info) {
                if (Proxy::getInstance()->getModuleNameFromClassName($class) === $method['module']) {
                    if (isset($info[$method['action']]['description'])) {
                        $description = $info[$method['action']]['description'];
                    }
                    break;
                }
            }

            $properties = $this->getPostBodyProperties($method);
            $required = $this->getRequiredProperties($method);
            $example = $this->buildRequestBodyExample($method, $documentationGenerator);

            $bodySchema = [
                "type" => "object",
                "required" => $required,
                "properties" => $properties,
            ];

            $bodyContent = [
                "schema" => $bodySchema,
            ];
            if ($example !== null) {
                $bodyContent["example"] = $example;
            }

            $pathKey = '/index.php?method=' . $method['method'];
            $paths[$pathKey] = [
                "post" => [
                    "summary" => $summary,
                    "description" => $description ?: "Execute " . $method['method'] . " API method",
                    "operationId" => $operationId,
                    "tags" => [$method['module']],
                    "deprecated" => $method['isDeprecated'],
                    "requestBody" => [
                        "required" => false,
                        "content" => [
                            "application/x-www-form-urlencoded" => $bodyContent,
                            "application/json" => $bodyContent,
                        ]
                    ],
                    "responses" => [
                        "200" => $this->buildSuccessResponse(),
                    ]
                ],
            ];
        }

        return $paths;
    }

    private function buildDocumentationGenerator()
    {
        try {
            return new DocumentationGenerator();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function buildRequestBodyExample($method, $documentationGenerator)
    {
        if (!$documentationGenerator) {
            return null;
        }

        try {
            $exampleUrl = $documentationGenerator->getExampleUrl($method['class'], $method['action']);
        } catch (\Throwable $e) {
            return null;
        }

        if (!is_string($exampleUrl) || $exampleUrl === '') {
            return null;
        }

        $query = ltrim($exampleUrl, '?');
        parse_str($query, $exampleParams);

        unset($exampleParams['token_auth']);

        if (empty($exampleParams)) {
            return null;
        }

        if (!isset($exampleParams['format'])) {
            $exampleParams['format'] = 'json';
        }

        return $exampleParams;
    }

    private function getRequiredProperties($method)
    {
        $required = ['module', 'format'];

        if (!isset($method['parameters'])) {
            return $required;
        }

        foreach ($method['parameters'] as $parameter => $config) {
            if (strpos($parameter, '_') === 0) {
                continue;
            }
            if ($this->isRequiredParameter($config)) {
                $required[] = $parameter;
            }
        }

        return $required;
    }

    private function isRequiredParameter($config)
    {
        if (!array_key_exists('default', $config)) {
            return true;
        }
        return is_object($config['default'])
            && get_class($config['default']) === 'Piwik\\API\\NoDefaultValue';
    }

    private function mapPhpTypeToOpenApi($phpType)
    {
        switch ($phpType) {
            case 'int':
            case 'integer':
                return 'integer';
            case 'bool':
            case 'boolean':
                return 'boolean';
            case 'float':
            case 'double':
                return 'number';
            case 'array':
                return 'array';
            case 'string':
            default:
                return 'string';
        }
    }

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

        if (!isset($method['parameters'])) {
            return $properties;
        }

        foreach ($method['parameters'] as $parameter => $config) {
            if (strpos($parameter, '_') === 0) {
                continue;
            }
            $properties[$parameter] = $this->buildPropertySchema($config, $parameter);
        }

        return $properties;
    }

    private function buildPropertySchema($config, $name = null)
    {
        $override = $name !== null ? $this->getKnownParameterOverride($name) : null;

        if ($override !== null) {
            $prop = $override;
            $openApiType = $prop['type'] ?? 'string';
        } else {
            $openApiType = $this->mapPhpTypeToOpenApi($config['type'] ?? null);
            $prop = ['type' => $openApiType];

            if ($openApiType === 'array') {
                $prop['items'] = ['type' => 'string'];
            }
        }

        if (!empty($config['allowsNull']) && !isset($prop['nullable'])) {
            $prop['nullable'] = true;
        }

        $hasDefault = array_key_exists('default', $config);
        $isNoDefaultValue = $hasDefault
            && is_object($config['default'])
            && get_class($config['default']) === 'Piwik\\API\\NoDefaultValue';

        $defaultValue = $hasDefault ? $config['default'] : null;
        $hasMeaningfulDefault = $hasDefault
            && !$isNoDefaultValue
            && !is_object($defaultValue)
            && $defaultValue !== null;

        $overrideHasDefault = array_key_exists('default', $prop);

        if ($hasMeaningfulDefault) {
            $stringValue = is_array($defaultValue) ? '' : (string)$defaultValue;
            $isPlaceholder = $stringValue !== '' && strtolower(trim($stringValue)) === 'string';

            if ($isPlaceholder) {
                if (!$isNoDefaultValue && !$overrideHasDefault) {
                    $prop['default'] = $this->emptyDefaultFor($openApiType);
                }
            } elseif (!$overrideHasDefault) {
                $prop['default'] = $this->coerceDefault($openApiType, $defaultValue);
            }
        } elseif (!$isNoDefaultValue && !$overrideHasDefault) {
            $prop['default'] = $this->emptyDefaultFor($openApiType);
        }

        return $prop;
    }

    private function buildSuccessResponse()
    {
        return [
            "description" => "Successful response. The actual content type is selected by the `format` query/body parameter, not by Accept negotiation.",
            "content" => [
                "application/json" => [
                    "schema" => ["type" => "object"],
                ],
                "application/xml" => [
                    "schema" => ["type" => "string"],
                ],
                "text/csv" => [
                    "schema" => ["type" => "string"],
                ],
                "text/tab-separated-values" => [
                    "schema" => ["type" => "string"],
                ],
                "text/html" => [
                    "schema" => ["type" => "string"],
                ],
                "application/rss+xml" => [
                    "schema" => ["type" => "string"],
                ],
                "application/octet-stream" => [
                    "schema" => [
                        "type" => "string",
                        "description" => "Returned when format=original; the raw API return value, serialized.",
                    ],
                ],
            ],
        ];
    }

    private function getKnownParameterOverride($name)
    {
        switch ($name) {
            case 'period':
                return [
                    'type' => 'string',
                    'enum' => ['day', 'week', 'month', 'year', 'range'],
                    'default' => 'day',
                    'description' => 'Aggregation period for the report.',
                ];
            case 'date':
                return [
                    'type' => 'string',
                    'default' => 'today',
                    'description' => 'Date for the report. Accepts: YYYY-MM-DD, a range "YYYY-MM-DD,YYYY-MM-DD", or a relative keyword (today, yesterday, lastN, previousN).',
                    'example' => 'today',
                ];
            case 'idSite':
            case 'idsite':
                return [
                    'type' => 'string',
                    'description' => 'Site ID. Accepts a single integer, a comma-separated list (e.g. "1,2,3"), or "all".',
                    'pattern' => '^([0-9]+(,[0-9]+)*|all)$',
                    'example' => '1',
                ];
            case 'idSites':
                return [
                    'type' => 'string',
                    'description' => 'Comma-separated list of site IDs, a single integer, or "all".',
                    'pattern' => '^([0-9]+(,[0-9]+)*|all)$',
                    'example' => '1',
                ];
            case 'segment':
                return [
                    'type' => 'string',
                    'description' => 'Matomo segment expression, e.g. "browserCode==FF;countryCode==US". See https://developer.matomo.org/api-reference/reporting-api-segmentation',
                    'default' => '',
                ];
            case 'language':
                return [
                    'type' => 'string',
                    'description' => 'ISO 639-1 language code (e.g. "en", "fr").',
                    'pattern' => '^[a-z]{2}(-[A-Za-z]{2,4})?$',
                ];
            default:
                return null;
        }
    }

    private function emptyDefaultFor($openApiType)
    {
        if ($openApiType === 'array') {
            return [];
        }
        return '';
    }

    private function coerceDefault($openApiType, $value)
    {
        switch ($openApiType) {
            case 'integer':
                return (int)$value;
            case 'boolean':
                return (bool)$value;
            case 'number':
                return (float)$value;
            case 'array':
                return is_array($value) ? $value : [];
            default:
                return is_array($value) ? '' : (string)$value;
        }
    }
}
