<?php

declare(strict_types=1);

namespace Clesson\Silverstripe\GitHub\Forms\GridField;

use Clesson\Silverstripe\GitHub\Constants\RepositoryStatus;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_DataManipulator;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Model\List\SS_List;

/**
 * GridField filter dropdown for filtering repositories by status.
 *
 * @package Clesson\Silverstripe\GitHub
 * @subpackage Forms\GridField
 */
class GridFieldFilter_RepoStatus implements GridField_HTMLProvider, GridField_DataManipulator, GridField_ActionProvider
{
    use Injectable;

    /**
     * State key used to store the selected status.
     */
    private const STATE_KEY = 'GridFieldRepoStatusFilter';

    /**
     * Returns the action names handled by this component.
     *
     * @param GridField $gridField
     * @return array<string>
     */
    public function getActions($gridField): array
    {
        return ['filterbyrepostatus'];
    }

    /**
     * Handles the filter action by storing the selected status in GridField state.
     *
     * @param GridField $gridField
     * @param string $actionName
     * @param array $arguments
     * @param array $data
     * @return void
     */
    public function handleAction(GridField $gridField, $actionName, $arguments, $data): void
    {
        if ($actionName !== 'filterbyrepostatus') {
            return;
        }

        $state = $gridField->State;
        $fieldName = static::STATE_KEY;
        $state->$fieldName = $data['gridfield_repostatus_filter'] ?? '';

        if (isset($state->GridFieldPaginator)) {
            $state->GridFieldPaginator->currentPage = 1;
        }
    }

    /**
     * Returns the HTML dropdown rendered in the before fragment.
     *
     * @param GridField $gridField
     * @return array<string, string>
     */
    public function getHTMLFragments($gridField): array
    {
        $selected = $this->getSelected($gridField);

        $allLabel = _t(__CLASS__ . '.ALL_STATUSES', 'All statuses');
        $options = ['' => $allLabel] + RepositoryStatus::options();

        /** @var DropdownField $dropdownField */
        $dropdownField = DropdownField::create('gridfield_repostatus_filter', '', $options);
        $dropdownField->setValue($selected);
        $dropdownField->addExtraClass('no-change-track dropdown');
        $dropdownField->setAttribute('style', 'min-width:150px;appearance:auto;-webkit-appearance:menulist;');

        /** @var GridField_FormAction $actionField */
        $actionField = GridField_FormAction::create(
            $gridField,
            'filterbyrepostatus',
            _t(__CLASS__ . '.LABEL', 'Status'),
            'filterbyrepostatus',
            null
        );
        $actionField->setAttribute('style', 'display:none;');
        $actionField->addExtraClass('grid-field__repostatus-filter-action');

        $dropdownField->setAttribute(
            'onchange',
            'this.closest(\'.grid-field\').querySelector(\'.grid-field__repostatus-filter-action\').click();'
        );

        $html = '<div style="display:inline-flex;align-items:center;gap:8px;padding:4px 0 8px;margin-left:16px;">'
            . $dropdownField->forTemplate()
            . $actionField->forTemplate()
            . '</div>';

        return ['before' => $html];
    }

    /**
     * Applies the status filter to the DataList.
     *
     * @param GridField $gridField
     * @param SS_List $dataList
     * @return SS_List
     */
    public function getManipulatedData(GridField $gridField, SS_List $dataList): SS_List
    {
        $selected = $this->getSelected($gridField);

        if ($selected === '') {
            return $dataList;
        }

        return $dataList->filter('Status', $selected);
    }

    /**
     * Reads the selected status from GridField state.
     *
     * @param GridField $gridField
     * @return string
     */
    private function getSelected(GridField $gridField): string
    {
        $state = $gridField->State;
        $fieldName = static::STATE_KEY;

        return (string) ($state->$fieldName ?? '');
    }
}

