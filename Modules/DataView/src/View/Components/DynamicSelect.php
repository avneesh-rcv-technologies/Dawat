<?php

namespace Modules\DataView\View\Components;

use Illuminate\View\Component;

class DynamicSelect extends Component
{
    public $id;
    public $class;
    public $data;
    public $selected;
    public $multiple;
    public $placeholder;

    public function __construct(
        $id = null,
        $class = null,
        $data = [],
        $selected = null,
        $multiple = false,
        $placeholder = 'Select option'
    ) {
        $this->id = $id;
        $this->class = $class;
        $this->data = $data;
        $this->selected = $selected;
        $this->multiple = filter_var($multiple, FILTER_VALIDATE_BOOLEAN);
        $this->placeholder = $placeholder;
    }


    public function render()
    {
        return view('dataview::components.dynamic-select', [
            'id' => $this->id,
            'class' => $this->class,
            'data' => $this->data,
            'selected' => $this->selected,
            'multiple' => $this->multiple,
            'placeholder' => $this->placeholder,
        ]);
    }

}
