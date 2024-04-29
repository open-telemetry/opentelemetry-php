<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Baggage\Propagation;

use OpenTelemetry\API\Baggage\Baggage;
use OpenTelemetry\API\Baggage\BaggageBuilderInterface;
use OpenTelemetry\API\Baggage\Entry; /** @phan-suppress-current-line PhanUnreferencedUseNormal */
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextInterface;
use OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter;
use OpenTelemetry\Context\Propagation\PropagationGetterInterface;
use OpenTelemetry\Context\Propagation\PropagationSetterInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use function rtrim;
use function urlencode;

/**
 * @see https://www.w3.org/TR/baggage
 */
final class BaggagePropagator implements TextMapPropagatorInterface
{
    public const BAGGAGE = 'baggage';

    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function fields(): array
    {
        return [self::BAGGAGE];
    }

    public function inject(&$carrier, ?PropagationSetterInterface $setter = null, ?ContextInterface $context = null): void
    {
        $setter ??= ArrayAccessGetterSetter::getInstance();
        $context ??= Context::getCurrent();

        $baggage = Baggage::fromContext($context);

        if ($baggage->isEmpty()) {
            return;
        }

        $headerString = '';

        /** @var Entry $entry */
        foreach ($baggage->getAll() as $key => $entry) {
            $value = urlencode((string) $entry->getValue());
            $headerString.= "{$key}={$value}";

            if (($metadata = $entry->getMetadata()->getValue()) !== '' && ($metadata = $entry->getMetadata()->getValue()) !== '0') {
                $headerString .= ";{$metadata}";
            }

            $headerString .= ',';
        }

        if ($headerString !== '' && $headerString !== '0') {
            $headerString = rtrim($headerString, ',');
            $setter->set($carrier, self::BAGGAGE, $headerString);
        }
    }

    public function extract($carrier, ?PropagationGetterInterface $getter = null, ?ContextInterface $context = null): ContextInterface
    {
        $getter ??= ArrayAccessGetterSetter::getInstance();
        $context ??= Context::getCurrent();

        if (!$baggageHeader = $getter->get($carrier, self::BAGGAGE)) {
            return $context;
        }

        $baggageBuilder = Baggage::getBuilder();
        $this->extractValue($baggageHeader, $baggageBuilder);

        return $context->withContextValue($baggageBuilder->build());
    }

    private function extractValue(string $baggageHeader, BaggageBuilderInterface $baggageBuilder): void
    {
        (new Parser($baggageHeader))->parseInto($baggageBuilder);
    }
}
