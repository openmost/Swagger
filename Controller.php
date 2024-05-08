<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Swagger;


use Piwik\API\DocumentationGenerator;
use Piwik\API\Proxy;
use Piwik\API\Request;
use Piwik\Piwik;

/**
 * A controller lets you for example create a page that can be added to a menu. For more information read our guide
 * http://developer.piwik.org/guides/mvc-in-piwik or have a look at the our API references for controller and view:
 * http://developer.piwik.org/api-reference/Piwik/Plugin/Controller and
 * http://developer.piwik.org/api-reference/Piwik/View
 */
class Controller extends \Piwik\Plugin\Controller
{

    public function __construct()
    {
        parent::__construct();

        $plugins = \Piwik\Plugin\Manager::getInstance()->getLoadedPluginsName();
        foreach ($plugins as $plugin) {
            try {
                $className = Request::getClassNameAPI($plugin);
                Proxy::getInstance()->registerClass($className);
            } catch (\Exception $e) {
            }
        }
    }

    public function index()
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

        $this->getMetadata();
        $this->getAllApiMethods();

        return json_encode($openapi);

        // Render the Twig template templates/index.twig and assign the view variable answerToLife to the view.
        return $this->renderTemplate('index', array(
            'openapi' => $openapi,
        ));

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
            foreach ($info as $methodName => $infoMethod) {
                $result[] = array($class, $moduleName, $methodName);
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
            $tags[] = Proxy::getInstance()->getModuleNameFromClassName($class);
        }

        return $tags;
    }

    public function getPaths()
    {
        $paths = [];

        foreach ($this->getAllApiMethods() as $method) {
            $paths['index.php?method='.$method[1].'.'.$method[2]] = [
                "post" => [
                    "tags" => [
                        $method[1],
                    ],
                    "requestBody" => [
                        "content" => [
                            "application/json" => [
                                "schema" => [
                                    "required" => [
                                        "module",
                                        "format",
                                        "method",
                                        "token_auth",
                                    ],
                                    //"properties" => $params,
                                ]
                            ]
                        ]
                    ]
                ],
            ];;
        }

        return $paths;
    }


}
