<?php

declare(strict_types=1);

namespace Clesson\Silverstripe\GitHub\Models;

use Clesson\Silverstripe\GitHub\Constants\RepositoryStatus;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;

/**
 * Represents a GitHub repository belonging to an account.
 *
 * @property string $PromotionTitle
 * @property string $PromotionDescription
 * @property int $PromotionImageID
 * @method Image PromotionImage()
 * @property string $Name
 * @property string $FullName
 * @property string $Description
 * @property string $HtmlUrl
 * @property string $Language
 * @property string $DefaultBranch
 * @property bool $IsFork
 * @property int $StargazersCount
 * @property int $ForksCount
 * @property int $OpenIssuesCount
 * @property string $LatestTag
 * @property bool $HasReadme
 * @property string $ReadmeUrl
 * @property int $DownloadsCount
 * @property string $Status
 * @property string $LastSyncedAt
 * @property string $GitHubCreatedAt
 * @property string $GitHubUpdatedAt
 * @property int $AccountID
 * @method GitHubAccount Account()
 * @property-read string $Title
 *
 * @package Clesson\Silverstripe\GitHub
 * @subpackage Models
 */
class GitHubRepository extends DataObject
{
    /**
     * @inheritdoc
     */
    private static string $table_name = 'GitHub_Repository';

    /**
     * @inheritdoc
     */
    private static string $default_sort = 'FullName ASC';

    /**
     * @inheritdoc
     */
    private static string $general_search_field = 'Name';

    /**
     * @inheritdoc
     */
    private static array $db = [
        'PromotionTitle'       => 'Varchar(150)',
        'PromotionDescription' => 'HTMLText',
        'Name'             => 'Varchar(100)',
        'FullName'         => 'Varchar(200)',
        'Description'      => 'Text',
        'HtmlUrl'          => 'Varchar(512)',
        'Language'          => 'Varchar(50)',
        'DefaultBranch'    => 'Varchar(100)',
        'IsFork'           => 'Boolean',
        'StargazersCount'  => 'Int',
        'ForksCount'       => 'Int',
        'OpenIssuesCount'  => 'Int',
        'LatestTag'        => 'Varchar(100)',
        'HasReadme'        => 'Boolean',
        'ReadmeUrl'        => 'Varchar(512)',
        'DownloadsCount'   => 'Int',
        'Status'           => 'Varchar(20)',
        'LastSyncedAt'     => 'DBDatetime',
        'GitHubCreatedAt'  => 'DBDatetime',
        'GitHubUpdatedAt'  => 'DBDatetime',
    ];

    /**
     * @inheritdoc
     */
    private static array $has_one = [
        'Account'        => GitHubAccount::class,
        'PromotionImage' => Image::class,
    ];

    /**
     * @inheritdoc
     */
    private static array $owns = [
        'PromotionImage',
    ];

    /**
     * @inheritdoc
     */
    private static array $indexes = [
        'FullNameUnique' => ['type' => 'unique', 'columns' => ['FullName']],
    ];

    /**
     * @inheritdoc
     */
    public function populateDefaults(): void
    {
        parent::populateDefaults();
        $this->Status = RepositoryStatus::ACTIVE;
    }

    /**
     * Sets the PromotionTitle from the repository name if left empty.
     *
     * @return void
     */
    protected function onBeforeWrite(): void
    {
        parent::onBeforeWrite();

        if (!$this->PromotionTitle && $this->FullName) {
            $slashPos = strpos($this->FullName, '/');
            $this->PromotionTitle = $slashPos !== false
                ? substr($this->FullName, $slashPos + 1)
                : $this->FullName;
        }
    }

    /**
     * @inheritdoc
     */
    public function fieldLabels($includerelations = true): array
    {
        $labels = parent::fieldLabels($includerelations);
        $labels['Name'] = _t(__CLASS__ . '.NAME', 'Name');
        $labels['FullName'] = _t(__CLASS__ . '.FULL_NAME', 'Full name');
        $labels['Description'] = _t(__CLASS__ . '.DESCRIPTION', 'Description');
        $labels['HtmlUrl'] = _t(__CLASS__ . '.HTML_URL', 'URL');
        $labels['Language'] = _t(__CLASS__ . '.LANGUAGE', 'Language');
        $labels['DefaultBranch'] = _t(__CLASS__ . '.DEFAULT_BRANCH', 'Default branch');
        $labels['IsFork'] = _t(__CLASS__ . '.IS_FORK', 'Fork');
        $labels['StargazersCount'] = _t(__CLASS__ . '.STARGAZERS_COUNT', 'Stars');
        $labels['ForksCount'] = _t(__CLASS__ . '.FORKS_COUNT', 'Forks');
        $labels['OpenIssuesCount'] = _t(__CLASS__ . '.OPEN_ISSUES_COUNT', 'Open issues');
        $labels['LatestTag'] = _t(__CLASS__ . '.LATEST_TAG', 'Latest tag');
        $labels['HasReadme'] = _t(__CLASS__ . '.HAS_README', 'Has README');
        $labels['ReadmeUrl'] = _t(__CLASS__ . '.README_URL', 'README URL');
        $labels['DownloadsCount'] = _t(__CLASS__ . '.DOWNLOADS_COUNT', 'Downloads');
        $labels['Status'] = _t(__CLASS__ . '.STATUS', 'Status');
        $labels['LastSyncedAt'] = _t(__CLASS__ . '.LAST_SYNCED_AT', 'Last synced');
        $labels['GitHubCreatedAt'] = _t(__CLASS__ . '.GITHUB_CREATED_AT', 'Created on GitHub');
        $labels['GitHubUpdatedAt'] = _t(__CLASS__ . '.GITHUB_UPDATED_AT', 'Updated on GitHub');
        $labels['Account'] = _t(__CLASS__ . '.ACCOUNT', 'Account');
        $labels['PromotionTitle'] = _t(__CLASS__ . '.PROMOTION_TITLE', 'Title');
        $labels['PromotionDescription'] = _t(__CLASS__ . '.PROMOTION_DESCRIPTION', 'Description');
        $labels['PromotionImage'] = _t(__CLASS__ . '.PROMOTION_IMAGE', 'Image');
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

        $fields->removeByName([
            'Name', 'FullName', 'Description', 'HtmlUrl', 'Language',
            'DefaultBranch', 'IsFork', 'StargazersCount',
            'ForksCount', 'OpenIssuesCount', 'LatestTag', 'HasReadme', 'ReadmeUrl',
            'DownloadsCount', 'Status', 'LastSyncedAt',
            'GitHubCreatedAt', 'GitHubUpdatedAt', 'AccountID',
            'PromotionTitle', 'PromotionDescription', 'PromotionImageID',
        ]);

        $this->buildPromotionTab($fields);
        $this->buildMetadataTab($fields);

        $fields->fieldByName('Root.Main')
            ->setTitle(_t(__CLASS__ . '.TAB_PROMOTION', 'Promotion'));

        return $fields;
    }

    /**
     * Adds the Promotion fields to Root.Main.
     *
     * @param FieldList $fields
     * @return void
     */
    private function buildPromotionTab(FieldList $fields): void
    {
        /** @var TextField $promotionTitleField */
        $promotionTitleField = TextField::create('PromotionTitle', $this->fieldLabel('PromotionTitle'));

        /** @var UploadField $promotionImageField */
        $promotionImageField = UploadField::create('PromotionImage', $this->fieldLabel('PromotionImage'));
        $promotionImageField->setAllowedFileCategories('image');
        $promotionImageField->setFolderName('github/promotion');

        /** @var HTMLEditorField $promotionDescriptionField */
        $promotionDescriptionField = HTMLEditorField::create('PromotionDescription', $this->fieldLabel('PromotionDescription'));
        $promotionDescriptionField->setEditorConfig('GitHubRepository_PromotionDescription_EditorConfig');

        $fields->addFieldsToTab('Root.Main', [
            $promotionTitleField,
            $promotionImageField,
            $promotionDescriptionField,
        ]);
    }

    /**
     * Adds the Metadata tab with all synced readonly fields.
     *
     * @param FieldList $fields
     * @return void
     */
    private function buildMetadataTab(FieldList $fields): void
    {
        /** @var ReadonlyField $fullNameField */
        $fullNameField = ReadonlyField::create('FullName', $this->fieldLabel('FullName'));

        /** @var ReadonlyField $descriptionField */
        $descriptionField = ReadonlyField::create('Description', $this->fieldLabel('Description'));

        /** @var ReadonlyField $htmlUrlField */
        $htmlUrlField = ReadonlyField::create('HtmlUrl', $this->fieldLabel('HtmlUrl'));

        /** @var ReadonlyField $languageField */
        $languageField = ReadonlyField::create('Language', $this->fieldLabel('Language'));

        /** @var ReadonlyField $defaultBranchField */
        $defaultBranchField = ReadonlyField::create('DefaultBranch', $this->fieldLabel('DefaultBranch'));

        /** @var ReadonlyField $latestTagField */
        $latestTagField = ReadonlyField::create('LatestTag', $this->fieldLabel('LatestTag'));

        /** @var ReadonlyField $hasReadmeField */
        $hasReadmeField = ReadonlyField::create('HasReadme', $this->fieldLabel('HasReadme'), $this->HasReadme ? _t(__CLASS__ . '.YES', 'Yes') : _t(__CLASS__ . '.NO', 'No'));

        /** @var ReadonlyField $readmeUrlField */
        $readmeUrlField = ReadonlyField::create('ReadmeUrl', $this->fieldLabel('ReadmeUrl'));

        /** @var ReadonlyField $downloadsCountField */
        $downloadsCountField = ReadonlyField::create('DownloadsCount', $this->fieldLabel('DownloadsCount'));

        /** @var ReadonlyField $statusField */
        $statusField = ReadonlyField::create('Status', $this->fieldLabel('Status'), RepositoryStatus::label($this->Status));

        /** @var ReadonlyField $lastSyncedAtField */
        $lastSyncedAtField = ReadonlyField::create('LastSyncedAt', $this->fieldLabel('LastSyncedAt'));

        /** @var ReadonlyField $gitHubCreatedAtField */
        $gitHubCreatedAtField = ReadonlyField::create('GitHubCreatedAt', $this->fieldLabel('GitHubCreatedAt'));

        /** @var ReadonlyField $gitHubUpdatedAtField */
        $gitHubUpdatedAtField = ReadonlyField::create('GitHubUpdatedAt', $this->fieldLabel('GitHubUpdatedAt'));

        $fields->addFieldsToTab('Root.Metadata', [
            $fullNameField,
            $descriptionField,
            $htmlUrlField,
            $languageField,
            $defaultBranchField,
            $latestTagField,
            $hasReadmeField,
            $readmeUrlField,
            $downloadsCountField,
            $statusField,
            $lastSyncedAtField,
            $gitHubCreatedAtField,
            $gitHubUpdatedAtField,
        ]);

        $fields->fieldByName('Root.Metadata')
            ->setTitle(_t(__CLASS__ . '.TAB_METADATA', 'Metadata'));
    }

    /**
     * Returns the display title.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return (string) $this->FullName ?: (string) $this->Name ?: _t(__CLASS__ . '.UNTITLED', 'New repository');
    }

    /**
     * Repositories are created by the sync task only.
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
     * Only administrators may edit repositories.
     *
     * @param \SilverStripe\Security\Member|null $member
     * @return bool
     */
    public function canEdit($member = null): bool
    {
        return Permission::check('ADMIN', 'any', $member);
    }

    /**
     * Only administrators may delete repositories.
     *
     * @param \SilverStripe\Security\Member|null $member
     * @return bool
     */
    public function canDelete($member = null): bool
    {
        return Permission::check('ADMIN', 'any', $member);
    }

    /**
     * Only administrators may view repositories.
     *
     * @param \SilverStripe\Security\Member|null $member
     * @return bool
     */
    public function canView($member = null): bool
    {
        return Permission::check('ADMIN', 'any', $member);
    }
}

