<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use function base64_decode;
use function bin2hex;
use Exception;
use Google\Protobuf\Descriptor;
use Google\Protobuf\DescriptorPool;
use Google\Protobuf\FieldDescriptor;
use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\Message;
use InvalidArgumentException;
use function json_decode;
use function json_encode;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use function lcfirst;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use function property_exists;
use function sprintf;
use function ucwords;

/**
 * @internal
 * @psalm-type SUPPORTED_CONTENT_TYPES = ContentTypes::PROTOBUF|ContentTypes::JSON|ContentTypes::NDJSON
 */
final class ProtobufSerializer
{
    use LogsMessagesTrait;

    private function __construct(private readonly string $contentType)
    {
    }

    public static function getDefault(): ProtobufSerializer
    {
        return new self(ContentTypes::PROTOBUF);
    }

    /**
     * @psalm-param TransportInterface<SUPPORTED_CONTENT_TYPES> $transport
     */
    public static function forTransport(TransportInterface $transport): ProtobufSerializer
    {
        return match ($contentType = $transport->contentType()) {
            ContentTypes::PROTOBUF, ContentTypes::JSON, ContentTypes::NDJSON => new self($contentType),
            default => throw new InvalidArgumentException(sprintf('Not supported content type "%s"', $contentType)),
        };
    }

    public function serializeTraceId(string $traceId): string
    {
        // @phpstan-ignore-next-line
        return match ($this->contentType) {
            ContentTypes::PROTOBUF => $traceId,
            ContentTypes::JSON, ContentTypes::NDJSON => base64_decode(bin2hex($traceId)),
        };
    }

    public function serializeSpanId(string $spanId): string
    {
        // @phpstan-ignore-next-line
        return match ($this->contentType) {
            ContentTypes::PROTOBUF => $spanId,
            ContentTypes::JSON, ContentTypes::NDJSON => base64_decode(bin2hex($spanId)),
        };
    }

    public function serialize(Message $message): string
    {
        // @phpstan-ignore-next-line
        return match ($this->contentType) {
            ContentTypes::PROTOBUF => $message->serializeToString(),
            ContentTypes::JSON => self::serializeToJsonString($message),
            ContentTypes::NDJSON => self::serializeToJsonString($message) . "\n",
        };
    }

    /**
     * @phan-suppress PhanParamTooManyInternal (@see https://github.com/phan/phan/pull/4840)
     * @throws Exception
     */
    public function hydrate(Message $message, string $payload): void
    {
        // @phpstan-ignore-next-line
        match ($this->contentType) {
            ContentTypes::PROTOBUF => $message->mergeFromString($payload),
            ContentTypes::JSON, ContentTypes::NDJSON => $message->mergeFromJsonString($payload, true),
        };
    }

    /**
     * [JSON Protobuf Encoding](https://opentelemetry.io/docs/specs/otlp/#json-protobuf-encoding):
     * > Values of enum fields MUST be encoded as integer values.
     *
     * @see https://github.com/open-telemetry/opentelemetry-php/issues/978
     * @see https://github.com/protocolbuffers/protobuf/pull/12707
     */
    private static function serializeToJsonString(Message $message): string
    {
        // @phan-suppress-next-line PhanUndeclaredClassReference
        if (\class_exists(\Google\Protobuf\PrintOptions::class)) {
            try {
                /** @psalm-suppress TooManyArguments @phan-suppress-next-line PhanParamTooManyInternal,PhanUndeclaredClassConstant */
                return $message->serializeToJsonString(\Google\Protobuf\PrintOptions::ALWAYS_PRINT_ENUMS_AS_INTS);
            } catch (\TypeError) {
                // google/protobuf ^4.31 w/ ext-protobuf <4.31 installed
            }
        }

        $payload = $message->serializeToJsonString();
        $pool = DescriptorPool::getGeneratedPool();
        $desc = $pool->getDescriptorByClassName($message::class);
        if (!$desc instanceof Descriptor) {
            return $payload;
        }

        $data = json_decode((string) $payload);
        unset($payload);
        self::traverseDescriptor($data, $desc);

        $encoded = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        assert($encoded !== false);

        return $encoded;
    }

    private static function traverseDescriptor(object $data, Descriptor $desc): void
    {
        for ($i = 0, $n = $desc->getFieldCount(); $i < $n; $i++) {
            // @phan-suppress-next-line PhanParamTooManyInternal
            $field = $desc->getField($i);
            $name = lcfirst(strtr(ucwords((string) $field->getName(), '_'), ['_' => '']));
            if (!property_exists($data, $name)) {
                continue;
            }

            if ($field->isRepeated()) {
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
