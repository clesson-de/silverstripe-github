<?php

declare(strict_types=1);

namespace Clesson\Silverstripe\GitHub\Models;

use Clesson\Silverstripe\GitHub\Forms\GridFieldConfig_AccountsInGitHubAdmin;
use Clesson\Silverstripe\GitHub\Forms\GridFieldConfig_RepositoriesInGitHubAdmin;
use Clesson\Silverstripe\GitHub\Forms\GridFieldConfig_SyncLogsInGitHubAdmin;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\PasswordField;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TabSet;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Security\Permission;

/**
 * Central configuration record for the GitHub module.
 *
 * Managed as a single record via SingleRecordAdmin.
 *
 * @property string $ApiToken
 * @method HasManyList|GitHubAccount[] Accounts()
 * @method HasManyList|GitHubSyncLog[] SyncLogs()
 *
 * @package Clesson\Silverstripe\GitHub
 * @subpackage Models
 */
class GitHubConfig extends DataObject
{
    /**
     * @inheritdoc
     */
    private static string $table_name = 'GitHub_Config';

    /**
     * @inheritdoc
     */
    private static string $default_sort = 'Created DESC';

    /**
     * @inheritdoc
     */
    private static string $general_search_field = 'ID';

    /**
     * @inheritdoc
     */
    private static array $db = [
        'ApiToken' => 'Varchar(255)',
    ];

    /**
     * @inheritdoc
     */
    private static array $has_many = [
        'Accounts' => GitHubAccount::class . '.Config',
        'SyncLogs' => GitHubSyncLog::class . '.Config',
    ];

    /**
     * Returns the single configuration record, creating one if none exists.
     *
     * @return static
     */
    public static function currentRecord(): static
    {
        $record = static::get()->setUseCache(true)->first();
        if (!$record) {
            $record = static::create();
            $record->write();
        }
        return $record;
    }

    /**
     * @inheritdoc
     */
    public function populateDefaults(): void
    {
        parent::populateDefaults();
    }

    /**
     * Ensures that at least one GitHubConfig record exists in the database.
     *
     * @inheritdoc
     */
    public function requireDefaultRecords(): void
    {
        parent::requireDefaultRecords();

        if (static::get()->count() === 0) {
            $record = static::create();
            $record->write();
            DB::alteration_message('Created default GitHubConfig record', 'created');
        }
    }

    /**
     * @inheritdoc
     */
    public function fieldLabels($includerelations = true): array
    {
        $labels = parent::fieldLabels($includerelations);
        $labels['ApiToken'] = _t(__CLASS__ . '.API_TOKEN', 'API Token');
        $labels['Accounts'] = _t(__CLASS__ . '.ACCOUNTS', 'Accounts');
        $labels['Repositories'] = _t(__CLASS__ . '.REPOSITORIES', 'Repositories');
        $labels['Synchronization'] = _t(__CLASS__ . '.SYNCHRONIZATION', 'Synchronization');
        $labels['SyncLogs'] = _t(__CLASS__ . '.SYNC_LOGS', 'Sync log');
        $labels['Administration'] = _t(__CLASS__ . '.ADMINISTRATION', 'Administration');
        $labels['ID'] = _t('Clesson\Silverstripe\GitHub\Common.ID', 'ID');
        $labels['Created'] = _t('Clesson\Silverstripe\GitHub\Common.CREATED', 'Created');
        $labels['LastEdited'] = _t('Clesson\Silverstripe\GitHub\Common.LAST_EDITED', 'Last edited');

        return $labels;
    }

    /**
     * @inheritdoc
     */
    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();

        $fields->removeByName(['Accounts', 'SyncLogs', 'ApiToken']);

        $this->addRepositoriesTab($fields);
        $this->addSynchronizationTab($fields);
        $this->addAdministrationTab($fields);

        $fields->fieldByName('Root.Main')->setTitle($this->fieldLabel('Repositories'));

        return $fields;
    }

    /**
     * Returns a human-readable title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return _t(__CLASS__ . '.TITLE', 'GitHub Configuration');
    }

    /**
     * Only administrators may create a GitHubConfig record.
     *
     * @param \SilverStripe\Security\Member|null $member
     * @param array $context
     * @return bool
     */
    public function canCreate($member = null, $context = []): bool
    {
        return Permission::check('ADMIN', 'any', $member);
    }

    /**
     * Only administrators may edit the GitHubConfig record.
     *
     * @param \SilverStripe\Security\Member|null $member
     * @return bool
     */
    public function canEdit($member = null): bool
    {
        return Permission::check('ADMIN', 'any', $member);
    }

    /**
     * The single GitHubConfig record must not be deleted.
     *
     * @param \SilverStripe\Security\Member|null $member
     * @return bool
     */
    public function canDelete($member = null): bool
    {
        return false;
    }

    /**
     * Only administrators may view the GitHubConfig record.
     *
     * @param \SilverStripe\Security\Member|null $member
     * @return bool
     */
    public function canView($member = null): bool
    {
        return Permission::check('ADMIN', 'any', $member);
    }

    /**
     * Adds the Repositories tab (Root.Main) with all repositories across all accounts.
     *
     * @param FieldList $fields
     * @return void
     */
    private function addRepositoriesTab(FieldList $fields): void
    {
        /** @var GridField $repositoriesField */
        $repositoriesField = GridField::create(
            'RepositoriesGrid',
            '',
            GitHubRepository::get(),
            GridFieldConfig_RepositoriesInGitHubAdmin::create()
        );

        $fields->addFieldToTab('Root.Main', $repositoriesField);
    }

    /**
     * Adds the Synchronization tab with nested Accounts and Sync log sub-tabs.
     *
     * @param FieldList $fields
     * @return void
     */
    private function addSynchronizationTab(FieldList $fields): void
    {
        /** @var GridField $accountsField */
        $accountsField = GridField::create(
            'Accounts',
            '',
            $this->Accounts(),
            GridFieldConfig_AccountsInGitHubAdmin::create()
        );

        /** @var GridField $syncLogsField */
        $syncLogsField = GridField::create(
            'SyncLogsGrid',
            '',
            $this->SyncLogs(),
            GridFieldConfig_SyncLogsInGitHubAdmin::create()
        );

        $fields->addFieldsToTab('Root.' . $this->fieldLabel('Synchronization'), [
            TabSet::create('SyncTabSet',
                Tab::create(
                    'AccountsTab',
                    $this->fieldLabel('Accounts'),
                    $accountsField
                ),
                Tab::create(
                    'SyncLogsTab',
                    $this->fieldLabel('SyncLogs'),
                    $syncLogsField
                )
            ),
        ]);
    }

    /**
     * Adds the Administration tab with settings fields.
     *
     * @param FieldList $fields
     * @return void
     */
    private function addAdministrationTab(FieldList $fields): void
    {
        /** @var PasswordField $apiTokenField */
        $apiTokenField = PasswordField::create('ApiToken', $this->fieldLabel('ApiToken'));
        $apiTokenField->setDescription(
            _t(__CLASS__ . '.API_TOKEN_DESCRIPTION', 'GitHub Personal Access Token. Required for synchronization (5,000 requests/hour). Without a token the API is limited to 60 requests/hour.')
        );

        if ($this->ApiToken) {
            $apiTokenField->setAttribute('placeholder', '********');
        }

        $fields->addFieldsToTab('Root.' . $this->fieldLabel('Administration'), [
            $apiTokenField,
        ]);
    }
}

