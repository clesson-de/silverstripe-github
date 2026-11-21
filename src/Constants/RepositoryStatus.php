<?php

declare(strict_types=1);

namespace Clesson\Silverstripe\GitHub\Constants;

/**
 * Repository status constants.
 *
 * @package Clesson\Silverstripe\GitHub
 * @subpackage Constants
 */
final class RepositoryStatus
{
    public const ACTIVE = 'active';
    public const DELETED = 'deleted';

    /**
     * Returns all status options as a translatable dropdown map.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::ACTIVE  => _t(__CLASS__ . '.ACTIVE', 'Active'),
            self::DELETED => _t(__CLASS__ . '.DELETED', 'Deleted'),
        ];
    }

    /**
     * Returns the translated label for a given status value.
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

