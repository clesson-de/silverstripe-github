<?php

declare(strict_types=1);

namespace Clesson\Silverstripe\GitHub\Models;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;

/**
 * Log entry for a GitHub sync task execution.
 *
 * @property string $Log
 * @property int $ConfigID
 * @method GitHubConfig Config()
 * @property-read string $Title
 *
 * @package Clesson\Silverstripe\GitHub
 * @subpackage Models
 */
class GitHubSyncLog extends DataObject
{
    /**
     * @inheritdoc
     */
    private static string $table_name = 'GitHub_SyncLog';

    /**
     * @inheritdoc
     */
    private static string $default_sort = 'Created DESC';

    /**
     * @inheritdoc
     */
    private static string $general_search_field = 'Log';

    /**
     * @inheritdoc
     */
    private static array $db = [
        'Log' => 'Text',
    ];

    /**
     * @inheritdoc
     */
    private static array $has_one = [
        'Config' => GitHubConfig::class,
    ];

    /**
     * @inheritdoc
     */
    public function fieldLabels($includerelations = true): array
    {
        $labels = parent::fieldLabels($includerelations);
        $labels['Log'] = _t(__CLASS__ . '.LOG', 'Log');
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

        $fields->removeByName(['ConfigID', 'Log']);

        /** @var ReadonlyField $logField */
        $logField = ReadonlyField::create('Log', $this->fieldLabel('Log'));

        $fields->addFieldToTab('Root.Main', $logField);

        return $fields;
    }

    /**
     * Returns the display title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return _t(__CLASS__ . '.TITLE', 'Sync log from {date}', ['date' => $this->Created]);
    }

    /**
     * Sync logs are created by the task only.
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
     * Sync logs are read-only.
     *
     * @param \SilverStripe\Security\Member|null $member
     * @return bool
     */
    public function canEdit($member = null): bool
    {
        return false;
    }

    /**
     * Only administrators may delete sync logs.
     *
     * @param \SilverStripe\Security\Member|null $member
     * @return bool
     */
    public function canDelete($member = null): bool
    {
        return Permission::check('ADMIN', 'any', $member);
    }

    /**
     * Only administrators may view sync logs.
     *
     * @param \SilverStripe\Security\Member|null $member
     * @return bool
     */
    public function canView($member = null): bool
    {
        return Permission::check('ADMIN', 'any', $member);
    }
}

