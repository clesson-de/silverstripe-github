<?php

declare(strict_types=1);

namespace Clesson\Silverstripe\GitHub\Elements;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;

/**
 * Elemental content element that displays a single GitHub repository
 * on the frontend. Inherits all filter and sort logic from
 * GitHubRepositoryListElement but always limits the result to one record.
 *
 * @package Clesson\Silverstripe\GitHub
 * @subpackage Elements
 *
 * @property-read DataObject|null $FilteredRepository
 */
class GitHubRepositorySingleElement extends GitHubRepositoryListElement
{
    /**
     * @var string
     */
    private static string $table_name = 'GitHub_RepositorySingleElement';

    /**
     * @var string
     */
    private static string $singular_name = 'GitHub Repository (Single)';

    /**
     * @var string
     */
    private static string $plural_name = 'GitHub Repositories (Single)';

    /**
     * @var string
     */
    private static string $class_description = 'Displays a single GitHub repository.';

    /**
     * Sets default values — limit is always 1.
     *
     * @return void
     */
    public function populateDefaults(): void
    {
        parent::populateDefaults();
        $this->Limit = 1;
    }

    /**
     * Forces limit to 1 before every write.
     *
     * @return void
     */
    protected function onBeforeWrite(): void
    {
        parent::onBeforeWrite();
        $this->Limit = 1;
    }

    /**
     * Removes the Limit field from the CMS form.
     *
     * @return FieldList
     */
    public function getCMSFields(): FieldList
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('Limit');

        return $fields;
    }

    /**
     * Returns the single filtered repository or null.
     *
     * @return DataObject|null
     */
    public function getFilteredRepository(): ?DataObject
    {
        return $this->getFilteredRepositories()->first();
    }

    /**
     * Returns a short summary text shown in the Elemental overview.
     *
     * @return string
     */
    public function getSummary(): string
    {
        $repo = $this->getFilteredRepository();

        if ($repo) {
            return $repo->FullName;
        }

        return _t(__CLASS__ . '.NO_REPOSITORY', 'No repository selected');
    }

    /**
     * Returns the element type label shown in the CMS.
     *
     * @return string
     */
    public function getType(): string
    {
        return _t(__CLASS__ . '.TYPE', 'GitHub Repository');
    }
}

