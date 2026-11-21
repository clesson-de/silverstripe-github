<?php

declare(strict_types=1);

namespace Clesson\Silverstripe\GitHub\Services;

use Clesson\Silverstripe\GitHub\Constants\RepositoryStatus;
use Clesson\Silverstripe\GitHub\Models\GitHubAccount;
use Clesson\Silverstripe\GitHub\Models\GitHubConfig;
use Clesson\Silverstripe\GitHub\Models\GitHubRepository;
use Clesson\Silverstripe\GitHub\Models\GitHubSyncLog;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBDatetime;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Service for syncing GitHub repositories from the GitHub API.
 *
 * Iterates over all active accounts, fetches their public repositories,
 * and creates or updates GitHubRepository records accordingly.
 *
 * @package Clesson\Silverstripe\GitHub
 * @subpackage Services
 */
class GitHubSyncService
{
    use Injectable;

    private const API_BASE_URL = 'https://api.github.com';
    private const RAW_BASE_URL = 'https://raw.githubusercontent.com';
    private const PER_PAGE = 100;

    /**
     * Log lines collected during a single sync run.
     *
     * @var array<int, string>
     */
    private array $logLines = [];

    /**
     * Runs the sync for all active accounts.
     *
     * @param OutputInterface|null $output Optional CLI output.
     * @return bool True on success, false if any account failed.
     */
    public function run(?OutputInterface $output = null): bool
    {
        $this->logLines = [];

        $config = GitHubConfig::currentRecord();

        if (!$config->ApiToken) {
            $this->log('WARNING: No API token configured. API requests will be rate-limited to 60/hour. Set the token in Administration > API Token.', $output);
        }

        $accounts = GitHubAccount::get()->filter('Active', true);

        if ($accounts->count() === 0) {
            $this->log('No active accounts found.', $output);
            $this->writeLog();
            return true;
        }

        $success = true;

        foreach ($accounts as $account) {
            try {
                $this->syncAccount($account, $output);
            } catch (\Throwable $e) {
                $this->log('Error syncing account "' . $account->Username . '": ' . $e->getMessage(), $output);
                $success = false;
            }
        }

        $this->writeLog();

        return $success;
    }

    /**
     * Syncs all repositories for a single account.
     *
     * @param GitHubAccount $account
     * @param OutputInterface|null $output
     * @return void
     */
    private function syncAccount(GitHubAccount $account, ?OutputInterface $output): void
    {
        $this->log('Syncing account: ' . $account->Username, $output);

        $repos = $this->fetchRepositories($account->Username);
        $this->log('  Found ' . count($repos) . ' repositories from API.', $output);

        if (count($repos) === 0) {
            $this->log('  Skipping deletion check — API returned no repositories.', $output);
            return;
        }

        $foundFullNames = [];

        foreach ($repos as $repoData) {

            $fullName = (string) ($repoData['full_name'] ?? '');
            $foundFullNames[] = $fullName;
            $action = $this->upsertRepository($account, $repoData);
            $this->log('  [' . $action . '] ' . $fullName, $output);
        }

        $this->markDeletedRepositories($account, $foundFullNames, $output);
    }

    /**
     * Fetches all repositories for a GitHub username (paginated).
     *
     * @param string $username
     * @return array<int, array<string, mixed>>
     */
    private function fetchRepositories(string $username): array
    {
        $client = $this->getClient();

        $allRepos = [];
        $page = 1;

        do {
            try {
                $response = $client->get('/users/' . urlencode($username) . '/repos', [
                    'query' => [
                        'per_page' => self::PER_PAGE,
                        'page'     => $page,
                        'type'     => 'public',
                    ],
                ]);
            } catch (GuzzleException $e) {
                Injector::inst()->get(LoggerInterface::class)->error(
                    'GitHub API request failed: ' . $e->getMessage()
                );
                break;
            }

            $body = (string) $response->getBody();
            $repos = json_decode($body, true);

            if (!is_array($repos) || count($repos) === 0) {
                break;
            }

            $allRepos = array_merge($allRepos, $repos);
            $page++;
        } while (count($repos) === self::PER_PAGE);

        return $allRepos;
    }

    /**
     * Creates or updates a GitHubRepository record from API data.
     *
     * @param GitHubAccount $account
     * @param array<string, mixed> $data
     * @return string The action performed: 'Added', 'Updated', or 'Reactivated'.
     */
    private function upsertRepository(GitHubAccount $account, array $data): string
    {
        $fullName = (string) ($data['full_name'] ?? '');

        /** @var GitHubRepository|null $repo */
        $repo = GitHubRepository::get()->filter('FullName', $fullName)->first();

        $action = 'Updated';

        if (!$repo) {
            $repo = GitHubRepository::create();
            $repo->AccountID = $account->ID;
            $action = 'Added';
        } elseif ($repo->Status === RepositoryStatus::DELETED) {
            $action = 'Reactivated';
        }

        $repo->Name            = (string) ($data['name'] ?? '');
        $repo->FullName        = $fullName;
        $repo->Description     = (string) ($data['description'] ?? '');
        $repo->HtmlUrl         = (string) ($data['html_url'] ?? '');
        $repo->Language        = (string) ($data['language'] ?? '');
        $repo->DefaultBranch   = (string) ($data['default_branch'] ?? '');
        $repo->IsFork          = (bool) ($data['fork'] ?? false);
        $repo->StargazersCount = (int) ($data['stargazers_count'] ?? 0);
        $repo->ForksCount      = (int) ($data['forks_count'] ?? 0);
        $repo->OpenIssuesCount = (int) ($data['open_issues_count'] ?? 0);
        $repo->Status          = RepositoryStatus::ACTIVE;
        $repo->LastSyncedAt    = DBDatetime::now()->Rfc2822();
        $repo->LatestTag       = $this->fetchLatestTag((string) ($data['full_name'] ?? ''));

        $hasReadme = $this->fetchHasReadme((string) ($data['full_name'] ?? ''), (string) ($data['default_branch'] ?? 'main'));
        $repo->HasReadme = $hasReadme;
        $repo->ReadmeUrl = $hasReadme
            ? (string) ($data['html_url'] ?? '') . '/blob/' . (string) ($data['default_branch'] ?? 'main') . '/README.md'
            : '';

        $repo->DownloadsCount = $this->fetchDownloadsCount((string) ($data['full_name'] ?? ''));

        if (!empty($data['created_at'])) {
            $repo->GitHubCreatedAt = date('Y-m-d H:i:s', strtotime($data['created_at']));
        }

        if (!empty($data['updated_at'])) {
            $repo->GitHubUpdatedAt = date('Y-m-d H:i:s', strtotime($data['updated_at']));
        }

        $repo->write();

        return $action;
    }

    /**
     * Marks repositories that were not found in the API response as deleted (soft delete).
     *
     * @param GitHubAccount $account
     * @param array<int, string> $foundFullNames
     * @param OutputInterface|null $output
     * @return void
     */
    private function markDeletedRepositories(GitHubAccount $account, array $foundFullNames, ?OutputInterface $output): void
    {
        $existing = GitHubRepository::get()->filter([
            'AccountID' => $account->ID,
            'Status'    => RepositoryStatus::ACTIVE,
        ]);

        foreach ($existing as $repo) {
            if (!in_array($repo->FullName, $foundFullNames, true)) {
                $repo->Status = RepositoryStatus::DELETED;
                $repo->LastSyncedAt = DBDatetime::now()->Rfc2822();
                $repo->write();
                $this->log('  [Deleted] ' . $repo->FullName, $output);
            }
        }
    }

    /**
     * Appends a line to the log buffer and optionally writes to CLI output.
     *
     * @param string $message
     * @param OutputInterface|null $output
     * @return void
     */
    private function log(string $message, ?OutputInterface $output): void
    {
        $this->logLines[] = $message;

        if ($output) {
            $output->writeln($message);
        }
    }

    /**
     * Returns a configured Guzzle HTTP client for the GitHub API.
     *
     * Uses the API token stored in GitHubConfig for authentication.
     * Without a token the API is limited to 60 requests per hour.
     *
     * @return Client
     */
    private function getClient(): Client
    {
        $headers = [
            'Accept'     => 'application/vnd.github.v3+json',
            'User-Agent' => 'SilverStripe-GitHub-Sync/1.0',
        ];

        $token = GitHubConfig::currentRecord()->ApiToken;

        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        return new Client([
            'base_uri' => self::API_BASE_URL,
            'headers'  => $headers,
        ]);
    }

    /**
     * Fetches the latest tag name for a repository.
     *
     * @param string $fullName The full repository name (e.g. "owner/repo").
     * @return string The tag name or empty string if no tags exist.
     */
    private function fetchLatestTag(string $fullName): string
    {
        if ($fullName === '') {
            return '';
        }

        $client = $this->getClient();

        try {
            $response = $client->get('/repos/' . $fullName . '/tags', [
                'query' => ['per_page' => 1],
            ]);

            $body = (string) $response->getBody();
            $tags = json_decode($body, true);

            if (is_array($tags) && !empty($tags[0]['name'])) {
                return (string) $tags[0]['name'];
            }
        } catch (GuzzleException $e) {
            // Silently ignore — tag fetching is non-critical
        }

        return '';
    }

    /**
     * Checks whether a repository has a README file via a HEAD request
     * to the raw content URL. This does not count against the API rate limit.
     *
     * @param string $fullName The full repository name (e.g. "owner/repo").
     * @param string $defaultBranch The default branch name.
     * @return bool
     */
    private function fetchHasReadme(string $fullName, string $defaultBranch): bool
    {
        if ($fullName === '') {
            return false;
        }

        $url = self::RAW_BASE_URL . '/' . $fullName . '/' . $defaultBranch . '/README.md';

        try {
            $client = new Client();
            $response = $client->head($url, [
                'http_errors' => false,
                'headers'     => ['User-Agent' => 'SilverStripe-GitHub-Sync/1.0'],
            ]);

            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            return false;
        }
    }

    /**
     * Fetches the total download count across all release assets.
     *
     * @param string $fullName The full repository name (e.g. "owner/repo").
     * @return int
     */
    private function fetchDownloadsCount(string $fullName): int
    {
        if ($fullName === '') {
            return 0;
        }

        $client = $this->getClient();
        $total = 0;
        $page = 1;

        do {
            try {
                $response = $client->get('/repos/' . $fullName . '/releases', [
                    'query' => [
                        'per_page' => self::PER_PAGE,
                        'page'     => $page,
                    ],
                ]);
            } catch (GuzzleException $e) {
                break;
            }

            $releases = json_decode((string) $response->getBody(), true);

            if (!is_array($releases) || count($releases) === 0) {
                break;
            }

            foreach ($releases as $release) {
                foreach (($release['assets'] ?? []) as $asset) {
                    $total += (int) ($asset['download_count'] ?? 0);
                }
            }

            $page++;
        } while (count($releases) === self::PER_PAGE);

        return $total;
    }

    /**
     * Writes the collected log lines to a GitHubSyncLog record.
     *
     * @return void
     */
    private function writeLog(): void
    {
        $config = GitHubConfig::currentRecord();

        $log = GitHubSyncLog::create();
        $log->Log = implode("\n", $this->logLines);
        $log->ConfigID = $config->ID;
        $log->write();
    }
}

