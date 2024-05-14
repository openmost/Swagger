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

    public function index()
    {
        Piwik::checkUserHasSuperUserAccess();

        return $this->renderTemplate('index');

    }

    public function iframe()
    {
        Piwik::checkUserHasSuperUserAccess();

        $openapi_url = "/index.php?module=API&format=json&method=Swagger.getOpenApi";

        return $this->renderTemplate('iframe', array(
            'openapi_url' => $openapi_url,
        ));
    }
}
