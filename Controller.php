<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Swagger;

use Piwik\Piwik;
use Piwik\Plugins\Swagger\Widgets\GetSwaggerUi;

class Controller extends \Piwik\Plugin\ControllerAdmin
{
    public function index()
    {
        Piwik::checkUserHasSuperUserAccess();

        return $this->renderTemplate('index', [
            'widget' => [
                'uniqueId' => 'swaggerAdminPage',
                'name' => Piwik::translate('Swagger_PageTitle'),
                'isWide' => true,
                'clientComponent' => [
                    'plugin' => GetSwaggerUi::COMPONENT_PLUGIN,
                    'name' => GetSwaggerUi::COMPONENT_NAME,
                    'props' => ['showTitle' => true],
                ],
            ],
        ]);
    }
}
