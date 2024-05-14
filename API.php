<?php

namespace Piwik\Plugins\Swagger;

use Piwik\API\Proxy;
use Piwik\API\Request;

class API extends \Piwik\Plugin\API
{
    public function __construct()
    {
        $plugins = \Piwik\Plugin\Manager::getInstance()->getLoadedPluginsName();
        foreach ($plugins as $plugin) {
            try {
                $className = Request::getClassNameAPI($plugin);
                Proxy::getInstance()->registerClass($className);
            } catch (\Exception $e) {
            }
        }
    }

    public function getOpenApi()
    {
        $openapi = [
            "openapi" => "3.1.0",
            "info" => $this->getInfo(),
            "externalDocs" => $this->getExternalDocs(),
            "servers" => $this->getServers(),
            "tags" => $this->getTags(),
            "paths" => $this->getPaths(),
            "components" => [
                "securitySchemes" => [
                    "TokenAuth" => [
                        "type" => "apiKey",
                        "in" => "query",
                        "name" => "auth_token",
                    ]
                ]
            ],
            "security" => [
                [
                    "TokenAuth" => [],
                ]
            ],
        ];

        return $openapi;
    }


    public function getMetadata()
    {
        return Proxy::getInstance()->getMetadata();
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

    public function getInfo()
    {
        $info = [
            "title" => "Matomo API",
            "summary" => "Matomo reporting API",
            "description" => "Complete Matomo reporting API documentation",
            "version" => "5.0.0"
        ];

        return $info;
    }

    public function getExternalDocs()
    {
        $externalDocs = [
            "description" => "Official Matomo doc",
            "url" => "https://developer.matomo.org/api-reference/reporting-api"
        ];

        return $externalDocs;
    }

    public function getServers()
    {
        $servers = [
            [
                "url" => "https://demo.matomo.cloud/",
                "description" => "The Matomo demo server",
            ]
        ];

        return $servers;
    }

    public function getTags()
    {
        $tags = [];
        foreach (Proxy::getInstance()->getMetadata() as $class => $info) {
            $tags[] = [
                'name' => Proxy::getInstance()->getModuleNameFromClassName($class),
                'description' => isset($info['__documentation']) ? $info['__documentation'] : '',
            ];
        }

        return $tags;
    }

    public function getPaths()
    {
        $paths = [];

        foreach ($this->getAllApiMethods() as $method) {
            $paths['/index.php?method=' . $method['method']] = [
                "post" => [
                    "tags" => [
                        $method['module'],
                    ],
                    "requestBody" => [
                        "content" => [
                            "application/json" => [
                                "schema" => [
                                    "required" => $this->getRequiredProperties($method),
                                    "properties" => $this->getProperties($method),
                                ]
                            ]
                        ]
                    ]
                ],
            ];
        }


        return $paths;
    }

    public function getRequiredProperties($method)
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

    public function getProperties($method)
    {
        $properties = [];

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
