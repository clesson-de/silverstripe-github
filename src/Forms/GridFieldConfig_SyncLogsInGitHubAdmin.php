<?php

declare(strict_types=1);

namespace Clesson\Silverstripe\GitHub\Forms;

use Clesson\Silverstripe\GitHub\Models\GitHubSyncLog;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldPageCount;
use SilverStripe\Forms\GridField\GridFieldPaginator;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Forms\GridField\GridFieldToolbarHeader;
use SilverStripe\ORM\FieldType\DBField;

/**
 * GridField configuration for displaying GitHubSyncLog records.
 *
 * @package Clesson\Silverstripe\GitHub
 * @subpackage Forms
 */
class GridFieldConfig_SyncLogsInGitHubAdmin extends GridFieldConfig
{
    /**
     * @param int|null $itemsPerPage
     * @param bool|null $showPagination
     * @param bool|null $showAdd
     */
    public function __construct($itemsPerPage = null, $showPagination = null, $showAdd = null)
    {
        parent::__construct();

        $this->addComponent(GridFieldButtonRow::create('before'));
        $this->addComponent(GridFieldToolbarHeader::create());
        $this->addComponent(GridFieldSortableHeader::create());
        $this->addComponent($dataColumns = GridFieldDataColumns::create());
        $this->addComponent(GridFieldEditButton::create());
        $this->addComponent(GridFieldDeleteAction::create());
        $this->addComponent(GridFieldPageCount::create('toolbar-header-right'));
        $this->addComponent(GridFieldPaginator::create($itemsPerPage));

        $dataColumns->setDisplayFields([
            'Log' => [
                'title' => _t(GitHubSyncLog::class . '.LOG', 'Log'),
                'callback' => static function ($record): DBField {
                    $text = mb_substr((string) $record->Log, 0, 120);
                    if (mb_strlen((string) $record->Log) > 120) {
                        $text .= '…';
                    }
                    return DBField::create_field('Varchar', $text);
                },
            ],
            'Created' => [
                'title' => _t('Clesson\Silverstripe\GitHub\Common.CREATED', 'Created'),
                'callback' => static function ($record): DBField {
                    return DBField::create_field('DBDatetime', $record->Created);
                },
            ],
        ]);

        $this->addComponent(GridFieldDetailForm::create(null, $showPagination, $showAdd));
        $this->extend('updateConfig');
    }
}

