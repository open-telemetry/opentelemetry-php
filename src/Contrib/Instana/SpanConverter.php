<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Instana;

use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Contrib\Instana\SpanKind as InstanaSpanKind;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Trace\EventInterface;
use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;

use Exception;
use function max;

class SpanConverter implements SpanConverterInterface
{
    const OTEL_KEY_STATUS_CODE = 'status_code';
    const OTEL_KEY_STATUS_DESCRIPTION = 'error';
    const OTEL_KEY_INSTRUMENTATION_SCOPE_NAME = 'scope.name';
    const OTEL_KEY_INSTRUMENTATION_SCOPE_VERSION = 'scope.version';
    const OTEL_KEY_DROPPED_ATTRIBUTES_COUNT = 'dropped_attributes_count';
    const OTEL_KEY_DROPPED_EVENTS_COUNT = 'dropped_events_count';
    const OTEL_KEY_DROPPED_LINKS_COUNT = 'dropped_links_count';

    public function __construct(
        private ?string $agentUuid = null,
        private ?string $agentPid = null
    ) {}

    public function convert(iterable $spans): array
    {
        $aggregate = [];
        foreach ($spans as $span) {
            $aggregate[] = $this->convertSpan($span);
        }

        return $aggregate;
    }

    private function convertSpan(SpanDataInterface $span): array
    {
        $startTimestamp = self::nanosToMillis($span->getStartEpochNanos());
        $endTimestamp = self::nanosToMillis($span->getEndEpochNanos());

        if (is_null($this->agentUuid) || is_null($this->agentPid)) {
            throw new Exception('Failed to get agentUuid or agentPid');
        }

        $instanaSpan = [
            'f' => array('e' => $this->agentPid, 'h' => $this->agentUuid),
            's' => $span->getSpanId(),
            't' => $span->getTraceId(),
            'ts' => $startTimestamp,
            'd' => max(0, $endTimestamp - $startTimestamp),
            'n' => $span->getName(),
            'data' => []
        ];

        if ($span->getParentContext()->isValid()) {
            $instanaSpan['p'] = $span->getParentSpanId();
        }

        $convertedKind = SpanConverter::toSpanKind($span);
        if (!is_null($convertedKind)) {
            $instanaSpan['k'] = $convertedKind;
        }

        self::insertSpanData($instanaSpan['data'], $span->getAttributes());
        self::insertSpanData($instanaSpan['data'], $span->getResource()->getAttributes());
        if (array_key_exists('service', $instanaSpan['data'])) {
            self::setOrAppend('otel', $instanaSpan['data'], array('service' => $instanaSpan['data']['service']));
        }
        $instanaSpan['data']['service'] = $span->getName();

        self::insertSpanData($instanaSpan['data'], $span->getInstrumentationScope()->getAttributes());

        if ($span->getStatus()->getCode() !== StatusCode::STATUS_UNSET) {
            self::setOrAppend('otel', $instanaSpan['data'], array(self::OTEL_KEY_STATUS_CODE => $span->getStatus()->getCode()));
        }

        if ($span->getStatus()->getCode() === StatusCode::STATUS_ERROR) {
            self::setOrAppend('otel', $instanaSpan['data'], array(self::OTEL_KEY_STATUS_DESCRIPTION => $span->getStatus()->getDescription()));
        }

        if (!empty($span->getInstrumentationScope()->getName())) {
            self::setOrAppend('otel', $instanaSpan['data'], array(self::OTEL_KEY_INSTRUMENTATION_SCOPE_NAME => $span->getInstrumentationScope()->getName()));
        }

        if ($span->getInstrumentationScope()->getVersion() !== null) {
            self::setOrAppend('otel', $instanaSpan['data'], array(self::OTEL_KEY_INSTRUMENTATION_SCOPE_VERSION => $span->getInstrumentationScope()->getVersion()));
        }

        foreach ($span->getEvents() as $event) {
            self::setOrAppend('events', $instanaSpan['data'], array($event->getName() => self::convertEvent($event)));
        }

        $droppedAttributes = $span->getAttributes()->getDroppedAttributesCount()
            + $span->getInstrumentationScope()->getAttributes()->getDroppedAttributesCount()
            + $span->getResource()->getAttributes()->getDroppedAttributesCount();

        if ($droppedAttributes > 0) {
            self::setOrAppend('otel', $instanaSpan['data'], array(self::OTEL_KEY_DROPPED_ATTRIBUTES_COUNT => $droppedAttributes));
        }

        if ($span->getTotalDroppedEvents() > 0) {
            self::setOrAppend('otel', $instanaSpan['data'], array(self::OTEL_KEY_DROPPED_EVENTS_COUNT => $span->getTotalDroppedEvents()));
        }

        if ($span->getTotalDroppedLinks() > 0) {
            self::setOrAppend('otel', $instanaSpan['data'], array(self::OTEL_KEY_DROPPED_LINKS_COUNT => $span->getTotalDroppedLinks()));
        }

        if (empty($instanaSpan['data'])) {
            unset($instanaSpan['data']);
        }

        return $instanaSpan;
    }

    private static function toSpanKind(SpanDataInterface $span): ?int
    {
        return match ($span->getKind()) {
            SpanKind::KIND_SERVER => InstanaSpanKind::ENTRY,
            SpanKind::KIND_CLIENT => InstanaSpanKind::EXIT,
            SpanKind::KIND_PRODUCER => InstanaSpanKind::EXIT,
            SpanKind::KIND_CONSUMER => InstanaSpanKind::ENTRY,
            SpanKind::KIND_INTERNAL => InstanaSpanKind::INTERMEDIATE,
            default => null,
        };
    }

    private static function nanosToMillis(int $nanoseconds): int
    {
        return intdiv($nanoseconds, ClockInterface::NANOS_PER_MILLISECOND);
    }

    private static function insertSpanData(array &$data, AttributesInterface $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $arr = explode('.', $key, 2);
            if (count($arr) < 2) {
                $data += array($arr[0] => $value);
            } else {
                self::setOrAppend($arr[0], $data, array($arr[1] => $value));
            }
        }
    }

    private static function setOrAppend(string $key, array &$arr, array $value): void
    {
        if (array_key_exists($key, $arr)) {
            $arr[$key] += $value;
        } else {
            $arr[$key] = $value;
        }
    }

    private static function convertEvent(EventInterface $event): string
    {
        if (count($event->getAttributes()) === 0) {
            return "{}";
        }

        $res = json_encode(array(
            'value' => $event->getAttributes()->toArray(),
            'timestamp' => self::nanosToMillis($event->getEpochNanos())
        ));

        return ($res === false) ? "{}" : $res;
    }
}
