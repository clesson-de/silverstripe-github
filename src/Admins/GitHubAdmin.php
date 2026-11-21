<?php

declare(strict_types=1);

namespace Clesson\Silverstripe\GitHub\Admins;

use Clesson\Silverstripe\GitHub\Models\GitHubConfig;
use SilverStripe\Admin\SingleRecordAdmin;

/**
 * SingleRecordAdmin for managing GitHub accounts and repositories.
 *
 * @package Clesson\Silverstripe\GitHub
 * @subpackage Admins
 */
class GitHubAdmin extends SingleRecordAdmin
{
    /**
     * @config
     */
    private static string $url_segment = 'github';

    /**
     * @config
     */
    private static string $menu_title = 'GitHub';

    /**
     * @config
     */
    private static int $menu_priority = -1;

    /**
     * @config
     */
    private static string $menu_icon_class = 'font-icon-code';

    /**
     * @config
     */
    private static string $model_class = GitHubConfig::class;

    /**
     * @config
     */
    private static bool $restrict_to_single_record = true;

    /**
     * @config
     */
    private static bool $allow_new_record = true;
}

