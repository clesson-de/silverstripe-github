<?php

declare(strict_types=1);

namespace Clesson\Silverstripe\GitHub\Tasks;

use Clesson\Silverstripe\GitHub\Services\GitHubSyncService;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Task that syncs all active GitHub accounts and their repositories.
 *
 * Can be run via CLI or the CMS dev/tasks interface.
 *
 * @package Clesson\Silverstripe\GitHub
 * @subpackage Tasks
 */
class GitHubSyncTask extends BuildTask
{
    /**
     * @inheritdoc
     */
    private static string $segment = 'github-sync';

    /**
     * @config
     */
    private static ?string $cli_user_email = null;

    /**
     * @inheritdoc
     */
    protected string $title = 'GitHub Repository Sync';

    /**
     * @inheritdoc
     */
    protected static string $description = 'Syncs repositories from all active GitHub accounts.';

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (php_sapi_name() === 'cli') {
            $email = static::config()->get('cli_user_email');
            if (!$email) {
                $output->writeln('<error>cli_user_email is not configured. Cannot run task via CLI without a user context.</error>');
                return Command::FAILURE;
            }
            $member = Member::get()->find('Email', $email);
            if (!$member) {
                $output->writeln("<error>No member found for email '{$email}'. Cannot run task via CLI.</error>");
                return Command::FAILURE;
            }
            Security::setCurrentUser($member);
        }

        /** @var GitHubSyncService $service */
        $service = Injector::inst()->create(GitHubSyncService::class);

        if (!$service->run($output)) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}



