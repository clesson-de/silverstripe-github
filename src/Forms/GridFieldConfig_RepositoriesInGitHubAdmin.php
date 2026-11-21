<?php

declare(strict_types=1);

namespace Clesson\Silverstripe\GitHub\Forms;

use Clesson\Silverstripe\GitHub\Constants\RepositoryStatus;
use Clesson\Silverstripe\GitHub\Forms\GridField\GridFieldFilter_Account;
use Clesson\Silverstripe\GitHub\Forms\GridField\GridFieldFilter_Language;
use Clesson\Silverstripe\GitHub\Forms\GridField\GridFieldFilter_RepoStatus;
use Clesson\Silverstripe\GitHub\Models\GitHubRepository;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldPageCount;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\ORM\FieldType\DBField;

/**
 * GridField configuration for displaying GitHubRepository records.
 *
 * @package Clesson\Silverstripe\GitHub
 * @subpackage Forms
 */
class GridFieldConfig_RepositoriesInGitHubAdmin extends GridFieldConfig
{
    /**
     * @param int|null $itemsPerPage
     * @param bool|null $showPagination
     * @param bool|null $showAdd
     */
    public function __construct($itemsPerPage = null, $showPagination = null, $showAdd = null)
    {
        parent::__construct();

        $this->addComponent(GridFieldButtonRow::create('before'));
        $this->addComponent(GridFieldToolbarHeader::create());
        $this->addComponent(GridFieldSortableHeader::create());
        $this->addComponent($dataColumns = GridFieldDataColumns::create());
        $this->addComponent(GridFieldEditButton::create());
        $this->addComponent(GridFieldPageCount::create('toolbar-header-right'));
        $this->addComponent(GridFieldPaginator::create($itemsPerPage));
        $this->addComponent(GridFieldFilter_Account::create());
        $this->addComponent(GridFieldFilter_Language::create());
        $this->addComponent(GridFieldFilter_RepoStatus::create());

        $dataColumns->setDisplayFields([
            'FullName' => [
                'title' => _t(GitHubRepository::class . '.FULL_NAME', 'Full name'),
                'callback' => static function ($record): DBField {
                    return DBField::create_field('Varchar', $record->FullName);
                },
            ],
            'LatestTag' => [
                'title' => _t(GitHubRepository::class . '.LATEST_TAG', 'Latest tag'),
                'callback' => static function ($record): DBField {
                    return DBField::create_field('Varchar', $record->LatestTag ?: '—');
                },
            ],
            'Language' => [
                'title' => _t(GitHubRepository::class . '.LANGUAGE', 'Language'),
                'callback' => static function ($record): DBField {
                    return DBField::create_field('Varchar', $record->Language);
                },
            ],
            'StargazersCount' => [
                'title' => _t(GitHubRepository::class . '.STARGAZERS_COUNT', 'Stars'),
                'callback' => static function ($record): DBField {
                    return DBField::create_field('Int', $record->StargazersCount);
                },
            ],
            'Status' => [
                'title' => _t(GitHubRepository::class . '.STATUS', 'Status'),
                'callback' => static function ($record): DBField {
                    $options = RepositoryStatus::options();
                    $label = $options[$record->Status] ?? $record->Status;
                    return DBField::create_field('Varchar', $label);
                },
            ],
            'DownloadsCount' => [
                'title' => _t(GitHubRepository::class . '.DOWNLOADS_COUNT', 'Downloads'),
                'callback' => static function ($record): DBField {
                    return DBField::create_field('Int', $record->DownloadsCount);
                },
            ],
            'GitHubUpdatedAt' => [
                'title' => _t(GitHubRepository::class . '.GITHUB_UPDATED_AT', 'Updated on GitHub'),
                'callback' => static function ($record): DBField {
                    return DBField::create_field('DBDatetime', $record->GitHubUpdatedAt);
                },
            ],
            'LastSyncedAt' => [
                'title' => _t(GitHubRepository::class . '.LAST_SYNCED_AT', 'Last synced'),
                'callback' => static function ($record): DBField {
                    return DBField::create_field('DBDatetime', $record->LastSyncedAt);
                },
            ],
        ]);

        $this->addComponent(GridFieldDetailForm::create(null, $showPagination, $showAdd));
        $this->extend('updateConfig');
    }
}

