<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Swagger;

use Piwik\Piwik;
use Piwik\Plugins\Swagger\OpenApi\SpecGenerator;

/**
 * Serves the OpenAPI specification of the Matomo Reporting API.
 */
class API extends \Piwik\Plugin\API
{
    /**
     * Returns the OpenAPI 3.1 specification of the Reporting API, generated from the activated plugins.
     *
     * @return array
     */
    public function getOpenApi()
    {
        Piwik::checkUserHasSuperUserAccess();

        return (new SpecGenerator())->generate();
    }
}
