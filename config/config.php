<?php

return array(
    \Piwik\View\SecurityPolicy::class => Piwik\DI::decorate(function ($previous) {
        /** @var \Piwik\View\SecurityPolicy $previous */

        if (!\Piwik\SettingsPiwik::isMatomoInstalled()) {
            return $previous;
        }

        $previous->addPolicy('img-src', 'validator.swagger.io');
        return $previous;
    }),
);
