<?php
/*
 * To be removed - added to simplify updates of metrics implementation.
 */
/** @noinspection PhpMultipleClassesDeclarationsInOneFile */
/** @noinspection PhpIllegalPsrClassPathInspection */
/** @noinspection PhpUnusedParameterInspection */

namespace OpenTelemetry;

use OpenTelemetry\API\Metrics\Observer;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use function class_alias;

// TODO Replace usage with ResourceInfo
class_alias(ResourceInfo::class, SDK\Resource::class);
// TODO Replace usage with Observer
class_alias(Observer::class, Metrics\Observer::class);

// TODO Replace usage with Context\Context
class Context {

    /** @var class-string|self */
    public const static = self::class;

    private function __construct(
        public Context\Context $context,
    ) {}

    public static function empty(): Context {
        return new self(Context\Context::getRoot());
    }

    public static function current(): Context {
        return new self(Context\Context::getCurrent());
    }
}

namespace OpenTelemetry\SDK;

use OpenTelemetry\API\Trace\ClockInterface;
use function iterator_to_array;

// TODO Replace usage with ClockInterface::now()
class Clock {

    public function __construct(
        private ClockInterface $clock,
    ) {}

    public function nanotime(): int {
        return $this->clock->now();
    }
}

// TODO Add implementation
class AttributesFactory {

    public function builder(iterable $attributes = []): AttributesBuilder {
        return new AttributesBuilder(new Attributes($attributes));
    }
}

// TODO Split Attributes implementation into mutable builder and readonly attributes
class AttributesBuilder {

    private Attributes $attributes;

    public function __construct(Attributes $attributes) {
        $this->attributes = $attributes;
    }

    public function build(): Attributes {
        return $this->attributes;
    }
}

// TODO Move Attributes out of \Trace namespace
class Attributes extends Trace\Attributes {

    public function toArray(): array {
        return iterator_to_array($this);
    }
}
