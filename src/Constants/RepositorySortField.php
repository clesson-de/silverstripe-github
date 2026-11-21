<?php

declare(strict_types=1);

namespace Clesson\Silverstripe\GitHub\Constants;

/**
 * Sort field constants for repository lists.
 *
 * @package Clesson\Silverstripe\GitHub
 * @subpackage Constants
 */
final class RepositorySortField
{
    /**
     * Sort by repository name.
     */
    public const NAME = 'Name';

    /**
     * Sort by programming language.
     */
    public const LANGUAGE = 'Language';

    /**
     * Sort by the date the repository was last updated on GitHub.
     */
    public const GITHUB_UPDATED_AT = 'GitHubUpdatedAt';

    /**
     * Sort by the date the repository was created on GitHub.
     */
    public const GITHUB_CREATED_AT = 'GitHubCreatedAt';

    /**
     * Returns all sort field options as a translatable dropdown map.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::NAME              => _t(__CLASS__ . '.NAME', 'Name'),
            self::LANGUAGE          => _t(__CLASS__ . '.LANGUAGE', 'Language'),
            self::GITHUB_UPDATED_AT => _t(__CLASS__ . '.GITHUB_UPDATED_AT', 'Updated on GitHub'),
            self::GITHUB_CREATED_AT => _t(__CLASS__ . '.GITHUB_CREATED_AT', 'Created on GitHub'),
        ];
    }

    /**
     * Returns the translated label for a given value.
     *
     * @param mixed $value
     * @return string
     */
    public static function label(mixed $value): string
    {
        $options = self::options();

        return isset($options[$value]) ? $options[$value] : (string) $value;
    }
}

