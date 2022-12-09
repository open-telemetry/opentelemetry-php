<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dev\Compatibility;

class Util
{
    public const E_NONE = 0;
    public const DEFAULT_ERROR_LEVEL = E_USER_NOTICE;
    public const ERROR_LEVELS = [
        self::E_NONE,
        E_USER_DEPRECATED,
        E_USER_NOTICE,
        E_USER_WARNING,
        E_USER_ERROR,
    ];

    private static int $errorLevel = E_USER_NOTICE;

    public static function setErrorLevel(int $errorLevel = E_USER_NOTICE): void
    {
        self::validateErrorLevel($errorLevel);

        self::$errorLevel = $errorLevel;
    }

    public static function getErrorLevel(): int
    {
        return self::$errorLevel;
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public static function triggerClassDeprecationNotice(string $className, string $alternativeClassName = null): void
    {
        if (self::getErrorLevel() === self::E_NONE) {
            return;
        }

        $notice = sprintf(
            'Class "%s" is deprecated and will be removed in a future release. ',
            $className
        );

        if ($alternativeClassName !== null) {
            $notice .= sprintf('Please, use "%s" instead.', $alternativeClassName);
        }

        trigger_error($notice, self::$errorLevel);
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public static function triggerMethodDeprecationNotice(
        string $methodName,
        string $alternativeMethodName = null,
        string $alternativeClassName = null
    ): void {
        if (self::getErrorLevel() === self::E_NONE) {
            return;
        }

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

        trigger_error($notice, self::$errorLevel);
    }

    private static function validateErrorLevel(int $errorLevel): void
    {
        if (!in_array($errorLevel, self::ERROR_LEVELS, true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Error level must be one of "%s"',
                    implode('", "', self::ERROR_LEVELS)
                ),
            );
        }
    }
}
