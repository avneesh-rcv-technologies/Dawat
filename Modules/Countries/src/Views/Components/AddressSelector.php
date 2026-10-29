<?php

namespace Modules\Countries\Views\Components;

use Illuminate\View\Component;

class AddressSelector extends Component
{
    public string $modelClass;
    public $modelId;
    public $addressId;
    public $address;
    public bool $showAddressType;
    public bool $required;
    public string $fieldPrefix;
    public bool $ajax;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $modelClass = '',
        $modelId = null,
        $addressId = null,
        $address = null,
        $showAddressType = true,
        $required = true,
        string $fieldPrefix = 'address'
        , $ajax = false
    ) {
        $this->modelClass = $modelClass;
        $this->modelId = $modelId;
        $this->addressId = $addressId;
        $this->address = $address;
        $this->showAddressType = filter_var($showAddressType, FILTER_VALIDATE_BOOLEAN);
        $this->required = filter_var($required, FILTER_VALIDATE_BOOLEAN);
        $this->fieldPrefix = $fieldPrefix;
        $this->ajax = filter_var($ajax, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        // dd("dsahsjkd");
        // dd( $this->address);
        return view('countries::components.address-selector');
    }
}
