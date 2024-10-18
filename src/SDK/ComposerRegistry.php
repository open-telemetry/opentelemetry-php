<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use Composer\Script\Event;
use Composer\Util\Filesystem;

class ComposerRegistry
{
    public const FILENAME = 'opentelemetry_registry.json';

    /**
     * Generate a JSON file for the SDK registry from `composer.extra.opentelemetry.registry`, so that
     * all required factories etc can be registered immediately.
     */
    public static function generate(Event $event): void
    {
        $composer = $event->getComposer();
        $extra = $composer->getPackage()->getExtra();
        $json = json_encode($extra['opentelemetry']['registry'], JSON_PRETTY_PRINT);
        $filesystem = new Filesystem();
        $vendorDir = $filesystem->normalizePath($composer->getConfig()->get('vendor-dir'));
        $filesystem->ensureDirectoryExists($vendorDir . '/composer');
        $filesystem->filePutContentsIfModified($vendorDir . '/composer/' . self::FILENAME, $json);
    }
}
