<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Event;

use OpenTelemetry\SDK\Common\Event\Handler\DebugEventHandler;
use OpenTelemetry\SDK\Common\Event\Handler\ErrorEventHandler;
use OpenTelemetry\SDK\Common\Event\Handler\WarningEventHandler;
use Psr\EventDispatcher\EventDispatcherInterface;

class Dispatcher
{
    private static ?EventDispatcherInterface $instance = null;

    public static function getInstance(): EventDispatcherInterface
    {
        if (self::$instance === null) {
            $dispatcher = new SimpleDispatcher(new SimpleListenerProvider());
            $dispatcher->listen(EventType::ERROR, new ErrorEventHandler());
            $dispatcher->listen(EventType::WARNING, new WarningEventHandler());
            $dispatcher->listen(EventType::DEBUG, new DebugEventHandler());

            self::$instance = $dispatcher;
        }

        return self::$instance;
    }

    public static function setInstance(EventDispatcherInterface $dispatcher): void
    {
        self::$instance = $dispatcher;
    }

    public static function unset(): void
    {
        self::$instance = null;
    }
}
