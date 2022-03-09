<?php

declare(strict_types=1);

namespace OpenTelemetry\EventHandler;

class Dispatcher
{
    private static array $listeners = [];
    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Dispatcher();
        }

        return self::$instance;
    }

    public function listen(string $event, callable $listener):void
    {
        self::$listeners[$event] = $listener;
    }

    public function dispatch(EventInterface $event):void
    {
        $eventClassname = $event->getClassName();
        $listenerCallable = self::$listeners[$eventClassname];
        if ($listenerCallable == null) {
            return;
        }
        call_user_func($listenerCallable, $event);
    }

    public function getListener(string $event):string
    {
        return self::$listeners[$event];
    }
}
