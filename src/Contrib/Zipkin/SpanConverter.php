<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Zipkin;

use function max;
use OpenTelemetry\API\Trace\EventInterface;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\SDK\Trace\AbstractClock;
use OpenTelemetry\SDK\Trace\Attribute;
use OpenTelemetry\SDK\Trace\SpanDataInterface;

class SpanConverter
{
    const STATUS_CODE_TAG_KEY = 'otel.status_code';
    const STATUS_DESCRIPTION_TAG_KEY = 'error';
    const KEY_INSTRUMENTATION_LIBRARY_NAME = 'otel.library.name';
    const KEY_INSTRUMENTATION_LIBRARY_VERSION = 'otel.library.version';

    const REMOTE_ENDPOINT_PREFERRED_ATTRIBUTE_TO_RANK_MAP = [
        "peer.service" => 1,
        "net.peer.name" => 2,
        "net.peer.ip" => 3,
        "peer.hostname" => 4,
        "peer.address" => 5,
        "http.host" => 6,
        "db.name" => 7
    ];

    /**
     * @var string
     */
    private $serviceName;

    public function __construct(string $serviceName)
    {
        $this->serviceName = $serviceName;
    }

    private function sanitiseTagValue($value)
    {
        // Casting false to string makes an empty string
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        // Zipkin tags must be strings, but opentelemetry
        // accepts strings, booleans, numbers, and lists of each.
        if (is_array($value)) {
            return implode(',', array_map([$this, 'sanitiseTagValue'], $value));
        }

        // Floats will lose precision if their string representation
        // is >=14 or >=17 digits, depending on PHP settings.
        // Can also throw E_RECOVERABLE_ERROR if $value is an object
        // without a __toString() method.
        // This is possible because OpenTelemetry\API\Trace\Span does not verify
        // setAttribute() $value input.
        return (string) $value;
    }

    public function convert(SpanDataInterface $span)
    {
        $spanParent = $span->getParentContext();

        $startTimestamp = AbstractClock::nanosToMicro($span->getStartEpochNanos());
        $endTimestamp = AbstractClock::nanosToMicro($span->getEndEpochNanos());

        $row = [
            'id' => $span->getSpanId(),
            'traceId' => $span->getTraceId(),
            'localEndpoint' => [
                'serviceName' => $this->serviceName,
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

        if (!empty($span->getInstrumentationLibrary()->getName())) {
            $row[SpanConverter::KEY_INSTRUMENTATION_LIBRARY_NAME] = $span->getInstrumentationLibrary()->getName();
        }

        if ($span->getInstrumentationLibrary()->getVersion() !== null) {
            $row[SpanConverter::KEY_INSTRUMENTATION_LIBRARY_VERSION] = $span->getInstrumentationLibrary()->getVersion();
        }

        foreach ($span->getAttributes() as $k => $v) {
            $row['tags'][$k] = $this->sanitiseTagValue($v->getValue());
        }

        foreach ($span->getEvents() as $event) {
            $row['annotations'][] = SpanConverter::toAnnotation($event);
        }

        if (($span->getKind() === SpanKind::KIND_CLIENT) || ($span->getKind() === SpanKind::KIND_PRODUCER))
        {
            $remoteEndpointData = SpanConverter::toRemoteEndpoint($span);
            if ($remoteEndpointData != null) {
                $row["remoteEndpoint"] = $remoteEndpointData;
            }
        }

        if (empty($row['tags'])) {
            unset($row['tags']);
        }

        return $row;
    }

    private static function toSpanKind(SpanDataInterface $span): ?int
    {
        switch ($span->getKind()) {
          case SpanKind::KIND_SERVER:
            return SpanKind::KIND_SERVER;
          case SpanKind::KIND_CLIENT:
            return SpanKind::KIND_CLIENT;
          case SpanKind::KIND_PRODUCER:
            return SpanKind::KIND_PRODUCER;
          case SpanKind::KIND_CONSUMER:
            return SpanKind::KIND_CONSUMER;
          case SpanKind::KIND_INTERNAL:
            return null;
        }
        
        return null;
    }

    private static function toAnnotation(EventInterface $event): array {
        $value = $event->getName();

        if (count($event->getAttributes()) > 0) {
            $attributesArray = [];
            foreach ($event->getAttributes() as $attr) {
                $attributesArray[$attr->getKey()] = $attr->getValue();
            }
            
            $attributesAsJson = json_encode($attributesArray);
            if (($attributesAsJson !== false) && (strlen($attributesAsJson) > 0)) {
                $value = '"' . $event->getName() . '"' . ': ' . $attributesAsJson; //TODO - clean this up and make sure it's spec compliant
            }
        }

        $annotation = [
            'timestamp' => AbstractClock::nanosToMicro($event->getEpochNanos()),
            'value' => $value,
        ];

        return $annotation;
    }

    private static function toRemoteEndpoint(SpanDataInterface $span): ?array {
        $preferredAttr = SpanConverter::findRemoteEndpointPreferredAttribute($span);

        if ($preferredAttr === null) {
            return null;
        }

        if (!is_string($preferredAttr->getValue())) {
            return null;
        }

        switch($preferredAttr->getKey()) {
            case "net.peer.ip":
                return SpanConverter::getRemoteEndpointDataFromIpAddressAndPort(
                    $preferredAttr, 
                    SpanConverter::getPortNumberFromSpanAttributes($span)
                );
            default:
                return [
                    'serviceName' => $preferredAttr->getValue(),
                ];
        }
    }

    private static function findRemoteEndpointPreferredAttribute(SpanDataInterface $span): ?Attribute {
        $preferredAttrRank = null;
        $preferredAttr = null;

        foreach ($span->getAttributes() as $attr) {
            if (array_key_exists($attr->getKey(), SpanConverter::REMOTE_ENDPOINT_PREFERRED_ATTRIBUTE_TO_RANK_MAP)) {
                $attrRank = SpanConverter::REMOTE_ENDPOINT_PREFERRED_ATTRIBUTE_TO_RANK_MAP[$attr->getKey()];

                if (($preferredAttrRank === null) || ($attrRank <= $preferredAttrRank)) {
                    $preferredAttr = $attr;
                    $preferredAttrRank = $attrRank;
                }
            }
        }

        return $preferredAttr;
    }

    private static function getRemoteEndpointDataFromIpAddressAndPort(Attribute $preferredAttr, ?int $portNumber): ?array {
        $ipString = $preferredAttr->getValue();

        if (!filter_var($ipString, FILTER_VALIDATE_IP)) {
            return null;
        }

        $remoteEndpointArr = [
            'serviceName' => "unknown"
        ];

        if (filter_var($ipString, FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)) {
            $remoteEndpointArr["ipv4"] = ip2long($ipString);
        }

        if (filter_var($ipString, FILTER_VALIDATE_IP,FILTER_FLAG_IPV6)) {
            //if (defined('AF_INET6')) { //TODO - figure out why this was never getting dropped into during the tests
                //This won't work (oddly) if ipv6 has been disabled for PHP and the server it's running on
                //Where this idea came from - https://www.php.net/manual/en/function.inet-pton.php#104917
                $remoteEndpointArr["ipv6"] = inet_pton($ipString);
            //}
            //TODO - does the else case need handling here?
        }

        $remoteEndpointArr["port"] = $portNumber ?? 0;

        return $remoteEndpointArr;
    }

    private static function getPortNumberFromSpanAttributes(SpanDataInterface $span): ?int {
        foreach ($span->getAttributes() as $attr) {
            if ($attr->getKey() === "net.peer.port") {
                $portVal = $attr->getValue();
                $portInt = intval($portVal);

                if (($portInt > 0) && ($portInt < pow(2,16))) {
                    return $portInt;
                }
            }
        }

        return null;
    }
}
