<?php

declare(strict_types=1);

namespace Clesson\Silverstripe\GitHub\Forms;

use Clesson\Silverstripe\GitHub\Forms\GridField\GridFieldAction_ToggleActive;
use Clesson\Silverstripe\GitHub\Models\GitHubAccount;
use SilverStripe\Forms\GridField\GridFieldAddNewButton;
use SilverStripe\Forms\GridField\GridFieldButtonRow;
use SilverStripe\Forms\GridField\GridFieldConfig;
use SilverStripe\Forms\GridField\GridFieldDataColumns;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldEditButton;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\ORM\FieldType\DBField;

/**
 * GridField configuration for displaying GitHubAccount records.
 *
 * @package Clesson\Silverstripe\GitHub
 * @subpackage Forms
 */
class GridFieldConfig_AccountsInGitHubAdmin extends GridFieldConfig
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
        $this->addComponent(GridFieldAddNewButton::create('buttons-before-right'));
        $this->addComponent(GridFieldSortableHeader::create());
        $this->addComponent($dataColumns = GridFieldDataColumns::create());
        $this->addComponent(GridFieldAction_ToggleActive::create());
        $this->addComponent(GridFieldEditButton::create());
        $this->addComponent(GridFieldDeleteAction::create());

        $dataColumns->setDisplayFields([
            'Username' => [
                'title' => _t(GitHubAccount::class . '.USERNAME', 'Username'),
                'callback' => static function ($record): DBField {
                    return DBField::create_field('Varchar', $record->Username);
                },
            ],
            'RepositoryCount' => [
                'title' => _t(GitHubAccount::class . '.REPOSITORY_COUNT', 'Repositories'),
                'callback' => static function ($record): DBField {
                    return DBField::create_field('Int', $record->Repositories()->count());
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

