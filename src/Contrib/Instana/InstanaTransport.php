<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Instana;

use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\ErrorFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

use Psr\Http\Message\ResponseInterface;

use BadMethodCallException;
use Exception;
use RuntimeException;

class InstanaTransport implements TransportInterface
{
    use LogsMessagesTrait;

    const CONTENT_TYPE = 'application/json';
    const ATTEMPTS = 3;

    private Client $client;
    private ?string $agent_uuid = null;
    private ?int $pid = null;
    private array $secrets = [];
    private array $tracing = [];

    private bool $closed = true;
    private array $headers = [];

    public function __construct(
        private readonly string $endpoint,
        private readonly float $timeout = 0.0
    ) {
        $this->headers += ['Content-Type' => self::CONTENT_TYPE];
        if ($timeout > 0.0) {
            $this->headers += ['timeout' => $timeout];
        }

        $this->client = new Client(['base_uri' => $endpoint]);

        for ($attempt = 0; $attempt < self::ATTEMPTS && !$this->announce(); $attempt++) {
            self::logDebug("Discovery request failed, attempt " . $attempt);
            sleep(5);
        }

        if (is_null($this->agent_uuid) || is_null($this->pid)) {
            throw new Exception('Failed announcement in transport');
        }
    }

    public function contentType(): string
    {
        return self::CONTENT_TYPE;
    }

    public function send(string $payload, ?CancellationInterface $cancellation = null): FutureInterface
    {
        if ($this->closed) {
            return new ErrorFuture(new BadMethodCallException('Transport closed'));
        }

        $response = $this->sendPayload($payload);

        $code = $response->getStatusCode();
        if ($code != 204 && $code != 307) {
            self::logDebug("Sending failed with code " . $code);
            return new ErrorFuture(new RuntimeException('Payload failed to send with code ' . $code));
        }

        return new CompletedFuture('Payload successfully sent');
    }

    private function sendPayload(string $payload): ResponseInterface
    {
        return $this->client->sendRequest(
            new Request(
                method: 'POST',
                uri: new Uri('/com.instana.plugin.php/traces.' . $this->pid),
                headers: $this->headers,
                body: $payload
            )
        );
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        if ($this->closed) {
            return false;
        }

        return $this->closed = true;
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return !$this->closed;
    }

    private function announce(): bool
    {
        self::logDebug("Announcing to " . $this->endpoint);

        // Phase 1) Host lookup.
        $response = $this->client->sendRequest(
            new Request(method: 'GET', uri: new Uri('/'), headers: $this->headers)
        );

        $code = $response->getStatusCode();
        $msg = $response->getBody()->getContents();

        if ($code != 200 && !array_key_exists('version', json_decode($msg, true))) {
            self::LogError("Failed to lookup host. Received code " . $code . " with message: " . $msg);
            $this->closed = true;
            return false;
        }

        self::logDebug("Phase 1 announcement response code " . $code);

        // Phase 2) Announcement.
        $response = $this->client->sendRequest(
            new Request(
                method: 'PUT',
                uri: new Uri('/com.instana.plugin.php.discovery'),
                headers: $this->headers,
                body: $this->getAnnouncementPayload()
            )
        );

        $code = $response->getStatusCode();
        $msg = $response->getBody()->getContents();

        self::logDebug("Phase 2 announcement response code " . $code);

        if ($code < 200 || $code >= 300) {
            self::LogError("Failed announcement. Received code " . $code . " with message: " . $msg);
            $this->closed = true;
            return false;
        }

        $content = json_decode($msg, true);
        if (!array_key_exists('pid', $content)) {
            self::LogError("Failed to receive a pid from agent");
            $this->closed = true;
            return false;
        }

        $this->pid = $content['pid'];
        $this->agent_uuid = $content['agentUuid'];

        // Optional values that we may receive from the agent.
        if (array_key_exists('secrets', $content)) $this->secrets = $content['secrets'];
        if (array_key_exists('tracing', $content)) $this->tracing = $content['tracing'];

        // Phase 3) Wait for the agent ready signal.
        for ($retry = 0; $retry < 5; $retry++) {
            if ($retry) self::logDebug("Agent not yet ready, attempt " . $retry);

            $response = $this->client->sendRequest(
                new Request(
                    method: 'HEAD',
                    uri: new Uri('/com.instana.plugin.php.' . $this->pid),
                    headers: $this->headers
                )
            );

            $code = $response->getStatusCode();
            self::logDebug("Phase 3 announcement endpoint status " . $code);
            if ($code >= 200 && $code < 300) {
                $this->closed = false;
                return true;
            }

            sleep(1);
        }

        $this->closed = true;
        return false;
    }

    private function getAnnouncementPayload(): string
    {
        $cmdline_args = file_get_contents("/proc/self/cmdline");
        $cmdline_args = explode("\0", $cmdline_args);
        $cmdline_args = array_slice($cmdline_args, 1, count($cmdline_args) - 2);

        return json_encode(array(
            "pid" => getmypid(),
            "pidFromParentNS" => false,
            "pidNamespace" => readlink("/proc/self/ns/pid"),
            "name" => readlink("/proc/self/exe"),
            "args" => $cmdline_args,
            "cpuSetFileContent" => "/",
            "fd" => null,
            "inode" => null
        ));
    }

    public function getPid(): ?string
    {
        return is_null($this->pid) ? null : strval($this->pid);
    }

    public function getUuid(): ?string
    {
        return $this->agent_uuid;
    }
}
