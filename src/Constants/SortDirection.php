<?php

declare(strict_types=1);

namespace Clesson\Silverstripe\GitHub\Constants;

/**
 * Sort direction constants for repository lists.
 *
 * @package Clesson\Silverstripe\GitHub
 * @subpackage Constants
 */
final class SortDirection
{
    /**
     * Ascending sort order.
     */
    public const ASC = 'ASC';

    /**
     * Descending sort order.
     */
    public const DESC = 'DESC';

    /**
     * Returns all sort direction options as a translatable dropdown map.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::ASC  => _t(__CLASS__ . '.ASC', 'Ascending'),
            self::DESC => _t(__CLASS__ . '.DESC', 'Descending'),
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

