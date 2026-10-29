<?php

namespace Modules\Core\Views\Components;

use Illuminate\View\Component;

class AdvancedSelector extends Component
{
    public $name;
    public $table;
    public $searchColumn;
    public $valueColumn;
    public $displayColumn;
    public $required;
    public $multiple;
    public $selected;
    public $placeholder;
    public $label;
    public $apiRoute;
    public $additionalColumns;
    public $filters;
    public $orderBy;
    public $orderDirection;

    public $ajax;

    /**
     * Create a new component instance.
     */
    public function __construct(
        $name = 'items',
        $table = '',
        $searchColumn = 'name',
        $valueColumn = 'id',
        $displayColumn = 'name',
        $required = false,
        $multiple = true,
        $selected = [],
        $placeholder = 'Select items...',
        $label = 'Select Items',
        $apiRoute = '',
        $additionalColumns = [],
        $filters = [],
        $orderBy = 'name',
        $orderDirection = 'asc',
        $ajax = false
    ) {


        $this->name = $name;
        $this->table = $table;
        $this->searchColumn = $searchColumn;
        $this->valueColumn = $valueColumn;
        $this->displayColumn = $displayColumn;
        $this->required = filter_var($required, FILTER_VALIDATE_BOOLEAN);
        $this->multiple = filter_var($multiple, FILTER_VALIDATE_BOOLEAN);
        $this->selected = $selected;
        $this->placeholder = $placeholder;
        $this->label = $label;
        $this->apiRoute = $apiRoute;
        $this->additionalColumns = is_array($additionalColumns) ? $additionalColumns : [];
        $this->filters = is_array($filters) ? $filters : [];
        $this->orderBy = $orderBy;
        $this->orderDirection = $orderDirection;
        $this->ajax = $ajax;

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        // dd($this->ajax);
        return view('core::components.advanced-selector');
    }
}