<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dev\Compatibility;

class Util
{
    public static function triggerClassDeprecationNotice(string $className, string $alternativeClassName = null): void
    {
        $notice = sprintf(
            'Class "%s " is deprecated and will be removed in a future release. ',
            $className
        );

        if ($alternativeClassName !== null) {
            $notice .= sprintf('Please, use "%s" instead.', $alternativeClassName);
        }

        trigger_error($notice, E_USER_NOTICE);
    }
}
