<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Swagger\Widgets;

use Piwik\Piwik;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

/**
 * Swagger UI rendered client side, so it can be embedded with the Widgetize module.
 */
class GetSwaggerUi extends Widget
{
    public const COMPONENT_PLUGIN = 'Swagger';
    public const COMPONENT_NAME = 'SwaggerUi';

    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('About Matomo');
        $config->setName('Swagger_WidgetName');
        $config->setOrder(99);
        $config->setIsWide();
        $config->setClientSideComponent(self::COMPONENT_PLUGIN, self::COMPONENT_NAME);

        if (!Piwik::hasUserSuperUserAccess()) {
            $config->disable();
        }
    }
}
