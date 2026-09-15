<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Swagger;

class Swagger extends \Piwik\Plugin
{
    public function registerEvents()
    {
        return [
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        ];
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'Swagger_PageTitle';
        $translationKeys[] = 'Swagger_Intro';
        $translationKeys[] = 'Swagger_UseSession';
        $translationKeys[] = 'Swagger_UseSessionHelp';
        $translationKeys[] = 'Swagger_DownloadSpec';
        $translationKeys[] = 'Swagger_LoadingSpec';
        $translationKeys[] = 'General_ErrorRequest';
    }

    public function shouldLoadUmdOnDemand()
    {
        // The UMD bundles Swagger UI, keep it out of the global Matomo assets
        return true;
    }
}
