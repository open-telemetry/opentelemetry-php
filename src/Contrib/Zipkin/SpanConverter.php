<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Zipkin;

use function max;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Contrib\Zipkin\SpanKind as ZipkinSpanKind;
use OpenTelemetry\SDK\Common\Time\Util as TimeUtil;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Trace\EventInterface;
use OpenTelemetry\SDK\Trace\SpanConverterInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SemConv\ResourceAttributes;

class SpanConverter implements SpanConverterInterface
{
    const STATUS_CODE_TAG_KEY = 'otel.status_code';
    const STATUS_DESCRIPTION_TAG_KEY = 'error';
    const KEY_INSTRUMENTATION_SCOPE_NAME = 'otel.scope.name';
    const KEY_INSTRUMENTATION_SCOPE_VERSION = 'otel.scope.version';
    const KEY_DROPPED_ATTRIBUTES_COUNT = 'otel.dropped_attributes_count';
    const KEY_DROPPED_EVENTS_COUNT = 'otel.dropped_events_count';
    const KEY_DROPPED_LINKS_COUNT = 'otel.dropped_links_count';

    const REMOTE_ENDPOINT_PREFERRED_ATTRIBUTE_TO_RANK_MAP = [
        'peer.service' => 1,
        'net.peer.name' => 2,
        'net.peer.ip' => 3,
        'peer.hostname' => 4,
        'peer.address' => 5,
        'http.host' => 6,
        'db.name' => 7,
    ];

    const NET_PEER_IP_KEY = 'net.peer.ip';

    private string $defaultServiceName;

    public function __construct()
    {
        $this->defaultServiceName = ResourceInfoFactory::defaultResource()->getAttributes()->get(ResourceAttributes::SERVICE_NAME);
    }

    private function sanitiseTagValue($value): string
    {
        // Casting false to string makes an empty string
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        // Zipkin tags must be strings, but opentelemetry
        // accepts strings, booleans, numbers, and lists of each.
        if (is_array($value)) {
            return implode(',', array_map(fn ($value) => $this->sanitiseTagValue($value), $value));
        }

        // Floats will lose precision if their string representation
        // is >=14 or >=17 digits, depending on PHP settings.
        // Can also throw E_RECOVERABLE_ERROR if $value is an object
        // without a __toString() method.
        // This is possible because OpenTelemetry\API\Trace\Span does not verify
        // setAttribute() $value input.
        return (string) $value;
    }

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
        $spanParent = $span->getParentContext();

        $startTimestamp = TimeUtil::nanosToMicros($span->getStartEpochNanos());
        $endTimestamp = TimeUtil::nanosToMicros($span->getEndEpochNanos());

        $serviceName =  $span->getResource()->getAttributes()->get(ResourceAttributes::SERVICE_NAME)
                        ??
                        $this->defaultServiceName;

        $row = [
            'id' => $span->getSpanId(),
            'traceId' => $span->getTraceId(),
            'localEndpoint' => [
                'serviceName' => $serviceName,
            ],
            'name' => $span->getName(),
            'timestamp' => $startTimestamp,
            'duration' => max(1, $endTimestamp - $startTimestamp),
            'tags' => [],
        ];

        $convertedKind = SpanConverter::toSpanKind($span);
        if ($convertedKind != null) {
            $row['kind'] = $convertedKind;
        }

        if ($spanParent->isValid()) {
            $row['parentId'] = $spanParent->getSpanId();
        }

        if ($span->getStatus()->getCode() !== StatusCode::STATUS_UNSET) {
            $row['tags'][self::STATUS_CODE_TAG_KEY] = $span->getStatus()->getCode();
        }

        if ($span->getStatus()->getCode() === StatusCode::STATUS_ERROR) {
            $row['tags'][self::STATUS_DESCRIPTION_TAG_KEY] = $span->getStatus()->getDescription();
        }

        if (!empty($span->getInstrumentationScope()->getName())) {
            $row[SpanConverter::KEY_INSTRUMENTATION_SCOPE_NAME] = $span->getInstrumentationScope()->getName();
        }

        if ($span->getInstrumentationScope()->getVersion() !== null) {
            $row[SpanConverter::KEY_INSTRUMENTATION_SCOPE_VERSION] = $span->getInstrumentationScope()->getVersion();
        }

        foreach ($span->getAttributes() as $k => $v) {
            $row['tags'][$k] = $this->sanitiseTagValue($v);
        }

        foreach ($span->getResource()->getAttributes() as $k => $v) {
            $row['tags'][$k] = $this->sanitiseTagValue($v);
        }
        foreach ($span->getInstrumentationScope()->getAttributes() as $k => $v) {
            $row['tags'][$k] = $this->sanitiseTagValue($v);
        }

        foreach ($span->getEvents() as $event) {
            $row['annotations'][] = SpanConverter::toAnnotation($event);
        }

        if ($span->getTotalDroppedEvents() > 0) {
            $row['tags'][self::KEY_DROPPED_EVENTS_COUNT] = $span->getTotalDroppedEvents();
        }

        if ($span->getTotalDroppedLinks() > 0) {
            $row['tags'][self::KEY_DROPPED_LINKS_COUNT] = $span->getTotalDroppedLinks();
        }

        $droppedAttributes = $span->getAttributes()->getDroppedAttributesCount()
            + $span->getInstrumentationScope()->getAttributes()->getDroppedAttributesCount()
            + $span->getResource()->getAttributes()->getDroppedAttributesCount();

        if ($droppedAttributes > 0) {
            $row['tags'][self::KEY_DROPPED_ATTRIBUTES_COUNT] = $droppedAttributes;
        }

        if (($span->getKind() === SpanKind::KIND_CLIENT) || ($span->getKind() === SpanKind::KIND_PRODUCER)) {
            $remoteEndpointData = SpanConverter::toRemoteEndpoint($span);
            if ($remoteEndpointData !== null) {
                $row['remoteEndpoint'] = $remoteEndpointData;
            }
        }

        if (empty($row['tags'])) {
            unset($row['tags']);
        }

        return $row;
    }

    private static function toSpanKind(SpanDataInterface $span): ?string
    {
        switch ($span->getKind()) {
          case SpanKind::KIND_SERVER:
            return ZipkinSpanKind::SERVER;
          case SpanKind::KIND_CLIENT:
            return ZipkinSpanKind::CLIENT;
          case SpanKind::KIND_PRODUCER:
            return ZipkinSpanKind::PRODUCER;
          case SpanKind::KIND_CONSUMER:
            return ZipkinSpanKind::CONSUMER;
          case SpanKind::KIND_INTERNAL:
            return null;
        }

        return null;
    }

    private static function toAnnotation(EventInterface $event): array
    {
        $eventName = $event->getName();
        $attributesAsJson = SpanConverter::convertEventAttributesToJson($event);

        $value = ($attributesAsJson !== null) ? sprintf('"%s": %s', $eventName, $attributesAsJson) : sprintf('"%s"', $eventName);

        $annotation = [
            'timestamp' => TimeUtil::nanosToMicros($event->getEpochNanos()),
            'value' => $value,
        ];
        if ($event->getAttributes()->getDroppedAttributesCount() > 0) {
            $annotation[self::KEY_DROPPED_ATTRIBUTES_COUNT] = $event->getAttributes()->getDroppedAttributesCount();
        }

        return $annotation;
    }

    private static function convertEventAttributesToJson(EventInterface $event): ?string
    {
        if (count($event->getAttributes()) === 0) {
            return null;
        }

        $attributesAsJson = json_encode($event->getAttributes()->toArray());
        if (($attributesAsJson === false)) {
            return null;
        }

        return $attributesAsJson;
    }

    private static function toRemoteEndpoint(SpanDataInterface $span): ?array
    {
        $attribute = SpanConverter::findRemoteEndpointPreferredAttribute($span);
        if (!$attribute) {
            return null;
        }

        [$key, $value] = $attribute;
        if (!is_string($value)) {
            return null;
        }

        switch ($key) {
            case SpanConverter::NET_PEER_IP_KEY:
                return SpanConverter::getRemoteEndpointDataFromIpAddressAndPort(
                    $value,
                    SpanConverter::getPortNumberFromSpanAttributes($span)
                );
            default:
                return [
                    'serviceName' => $value,
                ];
        }
    }

    private static function findRemoteEndpointPreferredAttribute(SpanDataInterface $span): ?array
    {
        $preferredAttrRank = null;
        $preferredAttr = null;

        foreach ($span->getAttributes() as $key => $value) {
            if (array_key_exists($key, SpanConverter::REMOTE_ENDPOINT_PREFERRED_ATTRIBUTE_TO_RANK_MAP)) {
                $attrRank = SpanConverter::REMOTE_ENDPOINT_PREFERRED_ATTRIBUTE_TO_RANK_MAP[$key];

                if (($preferredAttrRank === null) || ($attrRank <= $preferredAttrRank)) {
                    $preferredAttr = [$key, $value];
                    $preferredAttrRank = $attrRank;
                }
            }
        }

        return $preferredAttr;
    }

    private static function getRemoteEndpointDataFromIpAddressAndPort(string $ipString, ?int $portNumber): ?array
    {
        if (!filter_var($ipString, FILTER_VALIDATE_IP)) {
            return null;
        }

        $remoteEndpointArr = [
            'serviceName' => 'unknown',
        ];

        if (filter_var($ipString, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $remoteEndpointArr['ipv4'] = ip2long($ipString);
        }

        if (filter_var($ipString, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $remoteEndpointArr['ipv6'] = inet_pton($ipString);
        }

        $remoteEndpointArr['port'] = $portNumber ?? 0;

        return $remoteEndpointArr;
    }

    private static function getPortNumberFromSpanAttributes(SpanDataInterface $span): ?int
    {
        if (($portVal = $span->getAttributes()->get('net.peer.port')) !== null) {
            $portInt = (int) $portVal;

            if (($portInt > 0) && ($portInt < pow(2, 16))) {
                return $portInt;
            }
        }

        return null;
    }
}
