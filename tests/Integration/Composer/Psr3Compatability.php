<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\Composer;

use Composer\Script\Event;

final class Psr3Compatability
{
    public static function run(Event $event): void
    {
        require_once $event->getComposer()->getConfig()->get('vendor-dir') . '/autoload.php';
    }
}
