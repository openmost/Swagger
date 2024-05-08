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
use Piwik\Exception\Exception;
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
        $plugins = \Piwik\Plugin\Manager::getInstance()->getLoadedPluginsName();
        foreach ($plugins as $plugin) {
            try {
                $className = Request::getClassNameAPI($plugin);
                Proxy::getInstance()->registerClass($className);
            } catch (Exception $e) {
                // Handle errors
            }
        }
    }


    public function index()
    {
        $this->loadPLugins();
        $this->getMetadata();

        /*
        $result = array();

        foreach (Proxy::getInstance()->getMetadata() as $class => $info) {
            $moduleName = Proxy::getInstance()->getModuleNameFromClassName($class);
            foreach ($info as $methodName => $infoMethod) {
                $result[] = array($class, $moduleName, $methodName);
            }
        }

        // Render the Twig template templates/index.twig and assign the view variable answerToLife to the view.
        return $this->renderTemplate('index', array(
            'openapi' => 'test'
        ));
        */
    }

    public function loadPLugins()
    {
        $plugins = \Piwik\Plugin\Manager::getInstance()->getLoadedPluginsName();
        foreach ($plugins as $plugin) {
            try {
                $className = Request::getClassNameAPI($plugin);
                Proxy::getInstance()->registerClass($className);
            } catch (Exception $e) {
                // Handle errors
            }
        }
    }

    public function getMetadata()
    {
        dd(Proxy::getInstance()->getMetadata());
    }

    private function getAllApiMethods()
    {
        $result = array();

        $ApiDocumentation = new DocumentationGenerator();

        foreach (Proxy::getInstance()->getMetadata() as $class => $info) {
            $moduleName = Proxy::getInstance()->getModuleNameFromClassName($class);
            foreach ($info as $methodName => $infoMethod) {
                $result[] = array($class, $moduleName, $methodName);
            }
        }

        return $result;
    }

}
