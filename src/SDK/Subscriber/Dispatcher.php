<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Subscriber;

class Dispatcher
{
    private static array $Listener = [];
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

    public function setListener(string $event, string $listener):void
    {
        self::$Listener[$event] = $listener;
    }

    public function dispatch(EventInterface $event):void
    {
        $eventClassname = get_class($event);
        $eventClassname = str_replace('OpenTelemetry\\SDK\\Subscriber\\Event\\', '', $eventClassname);
        $listenerClassName = self::$Listener[$eventClassname];
        $listenerClassName = 'OpenTelemetry\\SDK\\Subscriber\\Listener\\' . $listenerClassName;
        $listener = new $listenerClassName();
        $listener->takeAction($event->getObject());
    }

    public function getListener(string $event):string
    {
        return self::$Listener[$event];
    }
}
