<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Metrics\Providers;

use OpenTelemetry\Metrics as API;

/**
 * https://github.com/open-telemetry/opentelemetry-specification/blob/master/specification/metrics/api.md#global-meter-provider
 */
final class GlobalMeterProvider
{
    /**
     * @var API\MeterProvider $globalProvider
     */
    protected static $globalProvider;

    /**
     * Returns a global MeterProvider
     *
     * @access	public static
     * @return	API\MeterProvider
     */
    public static function getGlobalProvider(): API\MeterProvider
    {
        if (empty(static::$globalProvider)) {
            static::$globalProvider = new MeterProvider();
        }

        return static::$globalProvider;
    }

    /**
     * Sets a global instance of MeterProvider
     *
     * @access	public static
     * @param	API\MeterProvider $globalProvider
     * @return	void
     */
    public static function setGlobalProvider(API\MeterProvider $globalProvider): void
    {
        static::$globalProvider = $globalProvider;
    }

    public static function __callStatic($name, $arguments)
    {
        return static::getGlobalProvider()->$name(...$arguments);
    }
}
