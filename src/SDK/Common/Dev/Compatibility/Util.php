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

        trigger_error($notice, \E_USER_DEPRECATED);
    }

    public static function triggerMethodDeprecationNotice(
        string $methodName,
        string $alternativeMethodName = null,
        string $alternativeClassName = null
    ): void {
        $notice = sprintf(
            'Method "%s " is deprecated and will be removed in a future release. ',
            $methodName
        );

        if ($alternativeMethodName !== null) {
            $method = $alternativeClassName === null
                ? $alternativeMethodName
                : sprintf('%s::%s', $alternativeClassName, $alternativeMethodName);

            $notice .= sprintf('Please, use "%s" instead.', $method);
        }

        trigger_error($notice, \E_USER_DEPRECATED);
    }
}
