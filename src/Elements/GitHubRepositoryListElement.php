<?php

declare(strict_types=1);

namespace Clesson\Silverstripe\GitHub\Elements;

use Clesson\Silverstripe\GitHub\Constants\RepositorySortField;
use Clesson\Silverstripe\GitHub\Constants\SortDirection;
use Clesson\Silverstripe\GitHub\Models\GitHubAccount;
use Clesson\Silverstripe\GitHub\Models\GitHubRepository;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ManyManyList;
use SilverStripe\Security\Permission;

/**
 * Elemental content element that displays a filtered and sorted list
 * of GitHub repositories on the frontend.
 *
 * @package Clesson\Silverstripe\GitHub
 * @subpackage Elements
 *
 * @property string $FilterName
 * @property string $FilterLanguages
 * @property int $Limit
 * @property string $SortField
 * @property string $SortDirection
 * @method ManyManyList|GitHubAccount[] FilterAccounts()
 * @property-read DataList $FilteredRepositories
 */
class GitHubRepositoryListElement extends BaseElement
{
    /**
     * @var string
     */
    private static string $table_name = 'GitHub_RepositoryListElement';

    /**
     * @var string
     */
    private static string $singular_name = 'GitHub Repository List';

    /**
     * @var string
     */
    private static string $plural_name = 'GitHub Repository Lists';

    /**
     * @var string
     */
    private static string $class_description = 'Displays a filtered and sorted list of GitHub repositories.';

    /**
     * Disable inline editing because the element uses ListboxField
     * for multi-select which may not render correctly inline.
     *
     * @var bool
     */
    private static bool $inline_editable = false;

    /**
     * @var array
     */
    private static array $db = [
        'FilterName'      => 'Varchar(100)',
        'FilterLanguages' => 'Text',
        'Limit'           => 'Int',
        'SortField'       => 'Varchar(20)',
        'SortDirection'   => 'Varchar(4)',
    ];

    /**
     * @var array
     */
    private static array $many_many = [
        'FilterAccounts' => GitHubAccount::class,
    ];

    /**
     * Sets default values for new records.
     *
     * @return void
     */
    public function populateDefaults(): void
    {
        parent::populateDefaults();
        $this->SortField = RepositorySortField::NAME;
        $this->SortDirection = SortDirection::ASC;
    }

    /**
     * Returns translated field labels for all fields and relations.
     *
     * @param bool $includerelations
     * @return array
     */
    public function fieldLabels($includerelations = true): array
    {
        $labels = parent::fieldLabels($includerelations);
        $labels['FilterName'] = _t(__CLASS__ . '.FILTER_NAME', 'Filter by name');
        $labels['FilterLanguages'] = _t(__CLASS__ . '.FILTER_LANGUAGES', 'Filter by language');
        $labels['FilterAccounts'] = _t(__CLASS__ . '.FILTER_ACCOUNTS', 'Filter by account');
        $labels['Limit'] = _t(__CLASS__ . '.LIMIT', 'Limit');
        $labels['SortField'] = _t(__CLASS__ . '.SORT_FIELD', 'Sort by');
        $labels['SortDirection'] = _t(__CLASS__ . '.SORT_DIRECTION', 'Sort direction');

        return $labels;
    }

    /**
     * Returns the CMS edit form fields.
     *
     * @return FieldList
     */
    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'FilterName',
            'FilterLanguages',
            'FilterAccounts',
            'Limit',
            'SortField',
            'SortDirection',
        ]);

        /** @var TextField $filterNameField */
        $filterNameField = TextField::create('FilterName', $this->fieldLabel('FilterName'));
        $filterNameField->setDescription(
            _t(__CLASS__ . '.FILTER_NAME_DESCRIPTION', 'Only show repositories whose name contains this text.')
        );

        /** @var ListboxField $filterAccountsField */
        $filterAccountsField = ListboxField::create(
            'FilterAccounts',
            $this->fieldLabel('FilterAccounts'),
            GitHubAccount::get()->map('ID', 'Username')->toArray()
        );

        /** @var ListboxField $filterLanguagesField */
        $filterLanguagesField = ListboxField::create(
            'FilterLanguages',
            $this->fieldLabel('FilterLanguages'),
            $this->getAvailableLanguages()
        );

        /** @var DropdownField $sortFieldField */
        $sortFieldField = DropdownField::create(
            'SortField',
            $this->fieldLabel('SortField'),
            RepositorySortField::options()
        );

        /** @var DropdownField $sortDirectionField */
        $sortDirectionField = DropdownField::create(
            'SortDirection',
            $this->fieldLabel('SortDirection'),
            SortDirection::options()
        );

        /** @var NumericField $limitField */
        $limitField = NumericField::create('Limit', $this->fieldLabel('Limit'));
        $limitField->setDescription(
            _t(__CLASS__ . '.LIMIT_DESCRIPTION', 'Maximum number of repositories to display. Leave empty or 0 for no limit.')
        );

        $fields->addFieldsToTab('Root.Main', [
            $filterAccountsField,
            $filterLanguagesField,
            $filterNameField,
            $sortFieldField,
            $sortDirectionField,
            $limitField,
        ]);

        return $fields;
    }

    /**
     * Returns the filtered and sorted list of GitHub repositories.
     * Returns an empty list if no filters are configured.
     *
     * @return DataList
     */
    public function getFilteredRepositories(): DataList
    {
        if (!$this->hasActiveFilters()) {
            return GitHubRepository::get()->filter('ID', 0);
        }

        $list = GitHubRepository::get();
        $list = $this->applyAccountFilter($list);
        $list = $this->applyLanguageFilter($list);
        $list = $this->applyNameFilter($list);
        $list = $this->applySorting($list);
        $list = $this->applyLimit($list);

        return $list;
    }

    /**
     * Checks whether at least one filter is configured.
     *
     * @return bool
     */
    private function hasActiveFilters(): bool
    {
        if ($this->FilterName) {
            return true;
        }

        if ($this->getSelectedLanguages()) {
            return true;
        }

        if ($this->isInDB() && $this->FilterAccounts()->count() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Returns a short summary text shown in the Elemental overview.
     *
     * @return string
     */
    public function getSummary(): string
    {
        $parts = [];

        if ($this->isInDB() && $this->FilterAccounts()->count() > 0) {
            $parts[] = $this->FilterAccounts()->count() . ' '
                . _t(__CLASS__ . '.ACCOUNTS_SELECTED', 'account(s)');
        }

        if ($this->FilterName) {
            $parts[] = _t(__CLASS__ . '.NAME_CONTAINS', 'Name: "{name}"', ['name' => $this->FilterName]);
        }

        if (count($parts) === 0) {
            return _t(__CLASS__ . '.ALL_REPOSITORIES', 'All repositories');
        }

        return implode(', ', $parts);
    }

    /**
     * Returns the element type label shown in the CMS.
     *
     * @return string
     */
    public function getType(): string
    {
        return _t(__CLASS__ . '.TYPE', 'GitHub Repositories');
    }

    /**
     * Anyone who can view the page may view this element.
     *
     * @param \SilverStripe\Security\Member|null $member
     * @return bool
     */
    public function canView($member = null): bool
    {
        return true;
    }

    /**
     * Only administrators may create this element.
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
     * Only administrators may edit this element.
     *
     * @param \SilverStripe\Security\Member|null $member
     * @return bool
     */
    public function canEdit($member = null): bool
    {
        return Permission::check('ADMIN', 'any', $member);
    }

    /**
     * Only administrators may delete this element.
     *
     * @param \SilverStripe\Security\Member|null $member
     * @return bool
     */
    public function canDelete($member = null): bool
    {
        return Permission::check('ADMIN', 'any', $member);
    }

    /**
     * Returns all distinct programming languages from existing repositories.
     *
     * @return array<string, string>
     */
    private function getAvailableLanguages(): array
    {
        $languages = GitHubRepository::get()
            ->exclude('Language', ['', null])
            ->columnUnique('Language');

        sort($languages);

        $options = [];
        foreach ($languages as $language) {
            $options[$language] = $language;
        }

        return $options;
    }

    /**
     * Filters the list by selected accounts.
     *
     * @param DataList $list
     * @return DataList
     */
    private function applyAccountFilter(DataList $list): DataList
    {
        if (!$this->isInDB() || $this->FilterAccounts()->count() === 0) {
            return $list;
        }

        $accountIDs = $this->FilterAccounts()->column('ID');

        return $list->filter('AccountID', $accountIDs);
    }

    /**
     * Filters the list by selected languages.
     *
     * @param DataList $list
     * @return DataList
     */
    private function applyLanguageFilter(DataList $list): DataList
    {
        $languages = $this->getSelectedLanguages();

        if (count($languages) === 0) {
            return $list;
        }

        return $list->filter('Language', $languages);
    }

    /**
     * Filters the list by name (partial match).
     *
     * @param DataList $list
     * @return DataList
     */
    private function applyNameFilter(DataList $list): DataList
    {
        if (!$this->FilterName) {
            return $list;
        }

        return $list->filter('Name:PartialMatch', $this->FilterName);
    }

    /**
     * Applies the configured sort field and direction.
     *
     * @param DataList $list
     * @return DataList
     */
    private function applySorting(DataList $list): DataList
    {
        $field = $this->SortField ?: RepositorySortField::NAME;
        $direction = $this->SortDirection ?: SortDirection::ASC;

        $allowedFields = array_keys(RepositorySortField::options());
        if (!in_array($field, $allowedFields, true)) {
            $field = RepositorySortField::NAME;
        }

        $allowedDirections = array_keys(SortDirection::options());
        if (!in_array($direction, $allowedDirections, true)) {
            $direction = SortDirection::ASC;
        }

        return $list->sort($field, $direction);
    }

    /**
     * Applies the configured result limit.
     *
     * @param DataList $list
     * @return DataList
     */
    private function applyLimit(DataList $list): DataList
    {
        if ($this->Limit > 0) {
            return $list->limit($this->Limit);
        }

        return $list;
    }

    /**
     * Parses the stored language filter into an array of language strings.
     *
     * @return array<string>
     */
    private function getSelectedLanguages(): array
    {
        if (!$this->FilterLanguages) {
            return [];
        }

        $decoded = json_decode($this->FilterLanguages, true);
        if (is_array($decoded)) {
            return array_filter($decoded);
        }

        return array_filter(array_map('trim', explode(',', $this->FilterLanguages)));
    }
}

