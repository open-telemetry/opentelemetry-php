<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\PhpVersionSupport;

use Nyholm\Psr7\Response;
use OpenTelemetry\DevTools\Console\Command\Php\PhpSupportIssuesCommand;
use OpenTelemetry\DevTools\Package\Php\IssueTemplate;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * The monthly PHP version support workflow opens issues from these templates. A template it cannot
 * render would only show as a failed run once a month, so the command renders them here, with the
 * values it gives them.
 */
#[CoversNothing]
final class IssueTemplatesTest extends TestCase
{
    private const TEMPLATES = __DIR__ . '/../../../.github/php-version-support';

    private string $project;

    #[\Override]
    protected function setUp(): void
    {
        //a project that needs the minimum, 8.2, to be dropped, and 8.4 to be added, on 2027-11-15
        $this->project = sys_get_temp_dir() . '/otel-php-version-support-' . bin2hex(random_bytes(4));
        mkdir("{$this->project}/.github/workflows", 0777, true);
        file_put_contents("{$this->project}/composer.json", '{"require": {"php": "^8.2"}}');
        file_put_contents("{$this->project}/.github/workflows/php.yml", "jobs:\n  php:\n    strategy:\n      matrix:\n        php-version: ['8.2', '8.3']\n");
    }

    #[\Override]
    protected function tearDown(): void
    {
        unlink("{$this->project}/.github/workflows/php.yml");
        unlink("{$this->project}/composer.json");
        rmdir("{$this->project}/.github/workflows");
        rmdir("{$this->project}/.github");
        rmdir($this->project);
    }

    public function test_the_command_renders_both_templates(): void
    {
        $tester = new CommandTester(new PhpSupportIssuesCommand($this->php()));

        $status = $tester->execute([
            '--path' => $this->project,
            '--templates' => self::TEMPLATES,
            '--date' => '2027-11-15',
            '--dry-run' => true,
        ]);

        $display = $tester->getDisplay();
        $this->assertSame(Command::SUCCESS, $status, $display);
        $this->assertStringContainsString('Would open an issue: Drop support for PHP 8.2 (support ends 2027-12-31) [php-version-support]', $display);
        $this->assertStringContainsString('Would open an issue: Add PHP 8.4 to CI [php-version-support]', $display);
        $this->assertStringNotContainsString('{{', $display);
    }

    #[DataProvider('templates')]
    public function test_the_template_has_the_label_the_issues_are_found_by(string $name): void
    {
        $template = IssueTemplate::fromFile(self::TEMPLATES . "/{$name}");

        $this->assertSame(['php-version-support'], $template->labels);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function templates(): iterable
    {
        yield 'drop' => ['drop.md'];
        yield 'add' => ['add.md'];
    }

    /**
     * A php.net that lists 8.2 to 8.4, all released.
     */
    private function php(): ClientInterface
    {
        $state = static fn (string $released, string $securityEnd): array => [
            'initial_release' => "{$released}T00:00:00+00:00",
            'security_support_end' => "{$securityEnd}T00:00:00+00:00",
        ];
        $body = (string) json_encode(['8' => [
            '8.4' => $state('2024-11-21', '2028-12-31'),
            '8.3' => $state('2023-11-23', '2027-12-31'),
            '8.2' => $state('2022-12-08', '2026-12-31'),
        ]]);

        return new class($body) implements ClientInterface {
            public function __construct(private readonly string $body)
            {
            }

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                return new Response(200, [], $this->body);
            }
        };
    }
}
