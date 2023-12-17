<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use AssertionError;
use function base64_decode;
use function bin2hex;
use Exception;
use function get_class;
use Google\Protobuf\Descriptor;
use Google\Protobuf\DescriptorPool;
use Google\Protobuf\FieldDescriptor;
use Google\Protobuf\Internal\GPBLabel;
use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\Message;
use InvalidArgumentException;
use function json_decode;
use function json_encode;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use function lcfirst;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use function property_exists;
use function sprintf;
use function ucwords;

/**
 * @internal
 *
 * @psalm-type SUPPORTED_CONTENT_TYPES = self::PROTOBUF|self::JSON|self::NDJSON
 */
final class ProtobufSerializer
{
    private const PROTOBUF = 'application/x-protobuf';
    private const JSON = 'application/json';
    private const NDJSON = 'application/x-ndjson';

    private string $contentType;

    private function __construct(string $contentType)
    {
        $this->contentType = $contentType;
    }

    public static function getDefault(): ProtobufSerializer
    {
        return new self(self::PROTOBUF);
    }

    /**
     * @psalm-param TransportInterface<SUPPORTED_CONTENT_TYPES> $transport
     */
    public static function forTransport(TransportInterface $transport): ProtobufSerializer
    {
        switch ($contentType = $transport->contentType()) {
            case self::PROTOBUF:
            case self::JSON:
            case self::NDJSON:
                return new self($contentType);
            default:
                throw new InvalidArgumentException(sprintf('Not supported content type "%s"', $contentType));
        }
    }

    public function serializeTraceId(string $traceId): string
    {
        switch ($this->contentType) {
            case self::PROTOBUF:
                return $traceId;
            case self::JSON:
            case self::NDJSON:
                return base64_decode(bin2hex($traceId));
            default:
                throw new AssertionError();
        }
    }

    public function serializeSpanId(string $spanId): string
    {
        switch ($this->contentType) {
            case self::PROTOBUF:
                return $spanId;
            case self::JSON:
            case self::NDJSON:
                return base64_decode(bin2hex($spanId));
            default:
                throw new AssertionError();
        }
    }

    public function serialize(Message $message): string
    {
        switch ($this->contentType) {
            case self::PROTOBUF:
                return $message->serializeToString();
            case self::JSON:
                return self::postProcessJsonEnumValues($message, $message->serializeToJsonString());
            case self::NDJSON:
                return self::postProcessJsonEnumValues($message, $message->serializeToJsonString()) . "\n";
            default:
                throw new AssertionError();
        }
    }

    /**
     * @throws Exception
     */
    public function hydrate(Message $message, string $payload): void
    {
        switch ($this->contentType) {
            case self::PROTOBUF:
                $message->mergeFromString($payload);

                break;
            case self::JSON:
            case self::NDJSON:
                // @phan-suppress-next-line PhanParamTooManyInternal
                $message->mergeFromJsonString($payload, true);

                break;
            default:
                throw new AssertionError();
        }
    }

    /**
     * Workaround until protobuf exposes `FormatEnumsAsIntegers` option.
     *
     * [JSON Protobuf Encoding](https://opentelemetry.io/docs/specs/otlp/#json-protobuf-encoding):
     * > Values of enum fields MUST be encoded as integer values.
     *
     * @see https://github.com/open-telemetry/opentelemetry-php/issues/978
     * @see https://github.com/protocolbuffers/protobuf/pull/12707
     */
    private static function postProcessJsonEnumValues(Message $message, string $payload): string
    {
        $pool = DescriptorPool::getGeneratedPool();
        $desc = $pool->getDescriptorByClassName(get_class($message));
        if (!$desc instanceof Descriptor) {
            return $payload;
        }

        $data = json_decode($payload);
        unset($payload);
        self::traverseDescriptor($data, $desc);

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private static function traverseDescriptor(object $data, Descriptor $desc): void
    {
        for ($i = 0, $n = $desc->getFieldCount(); $i < $n; $i++) {
            // @phan-suppress-next-line PhanParamTooManyInternal
            $field = $desc->getField($i);
            $name = lcfirst(strtr(ucwords($field->getName(), '_'), ['_' => '']));
            if (!property_exists($data, $name)) {
                continue;
            }

            if ($field->getLabel() === GPBLabel::REPEATED) {
                foreach ($data->$name as $key => $value) {
                    $data->$name[$key] = self::traverseFieldDescriptor($value, $field);
                }
            } else {
                $data->$name = self::traverseFieldDescriptor($data->$name, $field);
            }
        }
    }

    private static function traverseFieldDescriptor($data, FieldDescriptor $field)
    {
        switch ($field->getType()) {
            case GPBType::MESSAGE:
                self::traverseDescriptor($data, $field->getMessageType());

                break;
            case GPBType::ENUM:
                $enum = $field->getEnumType();
                for ($i = 0, $n = $enum->getValueCount(); $i < $n; $i++) {
                    if ($data === $enum->getValue($i)->getName()) {
                        return $enum->getValue($i)->getNumber();
                    }
                }

                break;
        }

        return $data;
    }
}
