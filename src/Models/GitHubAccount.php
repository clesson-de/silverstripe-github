<?php

declare(strict_types=1);

namespace Clesson\Silverstripe\GitHub\Models;

use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Security\Permission;

/**
 * Represents a GitHub user or organisation account.
 *
 * @property string $Username
 * @property bool $Active
 * @property int $ConfigID
 * @method GitHubConfig Config()
 * @method HasManyList|GitHubRepository[] Repositories()
 * @property-read string $Title
 *
 * @package Clesson\Silverstripe\GitHub
 * @subpackage Models
 */
class GitHubAccount extends DataObject
{
    /**
     * @inheritdoc
     */
    private static string $table_name = 'GitHub_Account';

    /**
     * @inheritdoc
     */
    private static string $default_sort = 'Username ASC';

    /**
     * @inheritdoc
     */
    private static string $general_search_field = 'Username';

    /**
     * @inheritdoc
     */
    private static array $db = [
        'Username' => 'Varchar(100)',
        'Active'   => 'Boolean',
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
    private static array $has_many = [
        'Repositories' => GitHubRepository::class . '.Account',
    ];

    /**
     * @inheritdoc
     */
    private static array $indexes = [
        'UsernameUnique' => ['type' => 'unique', 'columns' => ['Username']],
    ];

    /**
     * @inheritdoc
     */
    public function populateDefaults(): void
    {
        parent::populateDefaults();
        $this->Active = true;
    }

    /**
     * Automatically links the account to the singleton GitHubConfig record.
     *
     * @inheritdoc
     */
    public function onBeforeWrite(): void
    {
        parent::onBeforeWrite();

        if (!$this->ConfigID) {
            $this->ConfigID = GitHubConfig::currentRecord()->ID;
        }
    }

    /**
     * @inheritdoc
     */
    public function validate(): ValidationResult
    {
        $result = parent::validate();

        if (!$this->Username) {
            $result->addError(_t(
                Form::class . '.FIELDISREQUIRED',
                '{name} is required',
                ['name' => $this->fieldLabel('Username')]
            ));
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function fieldLabels($includerelations = true): array
    {
        $labels = parent::fieldLabels($includerelations);
        $labels['Username'] = _t(__CLASS__ . '.USERNAME', 'Username');
        $labels['Active'] = _t(__CLASS__ . '.ACTIVE', 'Active');
        $labels['Repositories'] = _t(__CLASS__ . '.REPOSITORIES', 'Repositories');
        $labels['RepositoryCount'] = _t(__CLASS__ . '.REPOSITORY_COUNT', 'Repositories');
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

        $fields->removeByName(['ConfigID', 'Repositories']);

        /** @var TextField $usernameField */
        $usernameField = TextField::create('Username', $this->fieldLabel('Username'));

        /** @var CheckboxField $activeField */
        $activeField = CheckboxField::create('Active', $this->fieldLabel('Active'));

        $fields->addFieldsToTab('Root.Main', [
            $usernameField,
            $activeField,
        ]);

        return $fields;
    }

    /**
     * Returns the display title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return (string) $this->Username ?: _t(__CLASS__ . '.UNTITLED', 'New account');
    }

    /**
     * Only administrators may create accounts.
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
     * Only administrators may edit accounts.
     *
     * @param \SilverStripe\Security\Member|null $member
     * @return bool
     */
    public function canEdit($member = null): bool
    {
        return Permission::check('ADMIN', 'any', $member);
    }

    /**
     * Only administrators may delete accounts.
     *
     * @param \SilverStripe\Security\Member|null $member
     * @return bool
     */
    public function canDelete($member = null): bool
    {
        return Permission::check('ADMIN', 'any', $member);
    }

    /**
     * Only administrators may view accounts.
     *
     * @param \SilverStripe\Security\Member|null $member
     * @return bool
     */
    public function canView($member = null): bool
    {
        return Permission::check('ADMIN', 'any', $member);
    }
}

