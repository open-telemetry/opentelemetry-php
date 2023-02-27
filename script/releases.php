<?php

require_once __DIR__ . "/../vendor/autoload.php";

use GuzzleHttp\Client;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Yaml\Parser;

/**
 * Search for unreleased commits against the opentelemetry subtree split repositories:
 * 1. fetch and iterate over the .gitsplit.yml files to locate subtree split targets
 * 2. find the latest release for each target, and then its release date
 * 3. find commits later than that date
 * 4. use the commits to generate release notes
 *
 * Usage:
 * GH_TOKEN=<token> DEBUG=1 php scripts/releases.php
 */
class Release
{
    //otel monorepos, containing a top-level .gitsplit.yaml
    private array $repos = [
        'open-telemetry/opentelemetry-php',
        'open-telemetry/opentelemetry-php-contrib',
    ];

    private Parser $parser;
    private ClientInterface $client;
    private static bool $debug = false;

    public function __construct()
    {
        self::$debug = getenv('DEBUG') !== false;
        $token = getenv('GH_TOKEN');
        $headers = [];
        if ($token) {
            $headers['Authorization'] = "token {$token}";
        }
        $this->client = new Client([
                'headers' => $headers,
                'http_errors' => false,
            ],
        );
        $this->parser = new Parser();
    }

    public function run(): void
    {
        $total = [];
        foreach ($this->repos as $repo) {
            self::debug('Processing repository: ' . $repo);
            $yaml = $this->get_gitsplit_yaml($repo);
            foreach ($yaml['splits'] as $split) {
                $path = $split['prefix'];
                $target = $split['target'];
                try {
                    $data = $this->process($repo, $path, $target);
                    $total[] = $data;
                } catch (Throwable $t) {
                    echo "ERROR: {$path}: {$t->getMessage()}" . PHP_EOL;
                }
            }
        }

        foreach ($total as $item) {
            if (count($item['new_commits']) > 0) {
                echo PHP_EOL . PHP_EOL;
                echo "*** {$item['org']}/{$item['repo']} ***" . PHP_EOL;
                echo "*** Last release: {$item['last_release_version']} ({$item['last_release_date']}) ***" . PHP_EOL;
                echo "## What's Changed" . PHP_EOL;
                foreach ($item['new_commits'] as $line) {
                    echo "* {$line}" . PHP_EOL;
                }
                echo PHP_EOL;
                echo "**Full Changelog**: https://github.com/{$item['org']}/{$item['repo']}/compare/{$item['last_release_version']}...a.b.c" . PHP_EOL;
                echo PHP_EOL . PHP_EOL;
            }
        }
    }

    private function process(string $main_repo, string $path, string $target): array
    {
        $parts = explode('/', str_replace(['https://${GH_TOKEN}@github.com/', '.git'], ['',''], $target));
        assert(count($parts) === 2);
        $org = $parts[0];
        $repo = $parts[1];
        self::debug("Processing: {$org}/{$repo}");

        $release = $this->get_latest_release($org, $repo);
        $commits = $this->get_unreleased_commits($main_repo, $path, $release);
        $data = [
            'org' => $org,
            'repo' => $repo,
            'upstream_repo' => $main_repo,
            'upstream_path' => $path,
            'last_release_date' => $release->created_at,
            'last_release_version' => $release->tag_name,
            'new_commits' => [],
            'contributors' => [],
        ];
        if (count($commits) > 0) {
            foreach ($commits as $commit) {
                $pr = $this->get_pull_request_for_commit($main_repo, $commit->sha);
                $data['new_commits'][] = "{$pr->title} by @{$pr->user->login} in [#{$pr->number}]({$pr->html_url})";
                $data['contributors'][$pr->user->login] = $pr->user->login;
            }
        }
        return $data;
    }

    private function get_unreleased_commits(string $main_repo, string $path, object $release): array
    {
        $release_date = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $release->published_at);

        $commits_url = "https://api.github.com/repos/{$main_repo}/commits?since=" . $release_date->format(DateTimeInterface::ATOM) . '&path=' . $path;
        self::debug($commits_url);
        $response = $this->client->get($commits_url);
        $commits = json_decode($response->getBody());
        self::debug(count($commits) . ' new commits found');

        return $commits;
    }

    private function get_latest_release(string $org, string $repo): object
    {
        $release_url = "https://api.github.com/repos/{$org}/{$repo}/releases/latest";
        self::debug($release_url);
        $response = $this->client->get($release_url);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Latest release not found for {$org}/{$repo}: " . $response->getReasonPhrase(), $response->getStatusCode());
        }
        $release = json_decode($response->getBody());
        self::debug("Latest release {$release->tag_name} on {$release->created_at}");

        return $release;
    }

    public function get_pull_request_for_commit(string $main_repo, string $sha): object
    {
        $prs_url = "https://api.github.com/repos/{$main_repo}/commits/{$sha}/pulls";
        self::debug($prs_url);
        $response = $this->client->get($prs_url);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception("PR not found for SHA: {$sha} in {$main_repo}");
        }

        $json = json_decode($response->getBody());
        if (count($json) === 0) {
            echo "[WARNING] No PR found for commit {$sha} in {$main_repo}" . PHP_EOL;
        }

        return $json[0];
    }

    private function get_gitsplit_yaml(string $repo): array
    {
        $url = "https://raw.githubusercontent.com/{$repo}/main/.gitsplit.yml";
        $response = $this->client->get($url);
        if ($response->getStatusCode() !== 200) {
            throw new \Exception("Error fetching {$url}");
        }

        return $this->parser->parse($response->getBody());
    }

    private static function debug(string $message): void
    {
        if (self::$debug) {
            echo '[DEBUG] ' . $message . PHP_EOL;
        }
    }
}

$app = new Release();
$app->run();
