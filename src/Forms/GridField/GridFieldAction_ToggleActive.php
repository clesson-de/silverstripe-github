<?php

declare(strict_types=1);

namespace Clesson\Silverstripe\GitHub\Forms\GridField;

use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_ColumnProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\ORM\DataObject;

/**
 * GridField component that renders a toggle button for the Active boolean field.
 *
 * Clicking the button toggles the Active flag on the record without
 * opening the detail form.
 *
 * @package Clesson\Silverstripe\GitHub
 * @subpackage Forms
 */
class GridFieldAction_ToggleActive implements GridField_ColumnProvider, GridField_ActionProvider
{
    use Injectable;

    /**
     * Adds the ToggleActive column to the GridField.
     *
     * @param GridField $gridField
     * @param array $columns
     * @return void
     */
    public function augmentColumns($gridField, &$columns): void
    {
        if (!in_array('ToggleActive', $columns, true)) {
            $columns[] = 'ToggleActive';
        }
    }

    /**
     * Returns the column metadata.
     *
     * @param GridField $gridField
     * @param string $columnName
     * @return array
     */
    public function getColumnMetadata($gridField, $columnName): array
    {
        if ($columnName === 'ToggleActive') {
            return ['title' => _t('Clesson\Silverstripe\GitHub\Models\GitHubAccount.ACTIVE', 'Active')];
        }
        return [];
    }

    /**
     * Returns the columns handled by this component.
     *
     * @param GridField $gridField
     * @return array
     */
    public function getColumnsHandled($gridField): array
    {
        return ['ToggleActive'];
    }

    /**
     * Returns column HTML attributes.
     *
     * @param GridField $gridField
     * @param DataObject $record
     * @param string $columnName
     * @return array
     */
    public function getColumnAttributes($gridField, $record, $columnName): array
    {
        return ['class' => 'grid-field__col-compact'];
    }

    /**
     * Renders the toggle button for each row.
     *
     * @param GridField $gridField
     * @param DataObject $record
     * @param string $columnName
     * @return string
     */
    public function getColumnContent($gridField, $record, $columnName): string
    {
        $icon = $record->Active
            ? '✔'
            : '✘';

        /** @var GridField_FormAction $action */
        $action = GridField_FormAction::create(
            $gridField,
            'ToggleActive_' . $record->ID,
            $icon,
            'toggleactive',
            ['RecordID' => $record->ID]
        );

        $color = $record->Active ? 'color:green;' : 'color:red;';
        $action->addExtraClass('grid-field__icon-action btn--no-text no-change-track');
        $action->setAttribute('style', $color . 'font-size:1.2em;border:none;background:none;cursor:pointer;');
        $action->setAttribute('title', _t(__CLASS__ . '.TOGGLE', 'Toggle active'));

        return (string) $action->Field();
    }

    /**
     * Returns the actions handled by this component.
     *
     * @param GridField $gridField
     * @return array
     */
    public function getActions($gridField): array
    {
        return ['toggleactive'];
    }

    /**
     * Handles the toggle action by flipping the Active flag.
     *
     * @param GridField $gridField
     * @param string $actionName
     * @param array $arguments
     * @param array $data
     * @return void
     */
    public function handleAction(GridField $gridField, $actionName, $arguments, $data): void
    {
        if ($actionName !== 'toggleactive') {
            return;
        }

        $recordID = (int) ($arguments['RecordID'] ?? 0);
        if ($recordID === 0) {
            return;
        }

        /** @var DataObject|null $record */
        $record = $gridField->getList()->byID($recordID);
        if (!$record) {
            return;
        }

        $record->Active = !$record->Active;
        $record->write();
    }
}


