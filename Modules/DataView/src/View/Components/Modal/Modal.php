<?php

namespace Modules\DataView\View\Components\Modal;

use Illuminate\View\Component;

class Modal extends Component
{
    public $id;
    public $buttonText;
    public $modalTitle;
    public $ajaxUrl;
    public $color;
    public $buttonClass;
    public $modalSize;
    public $closeButton;

    public function __construct(
        $buttonText = '__',
        $modalTitle = '',
        $ajaxUrl,
        $color       = '',
        $buttonClass = 'px-3 py-1 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition',
        $modalSize = 'md',
        $closeButton = true,
        $id = null
    ) {
        $this->buttonText = $buttonText;
        $this->modalTitle = $modalTitle;
        $this->ajaxUrl = $ajaxUrl;
        $this->color = $color;
        $this->buttonClass = $buttonClass;
        $this->modalSize = $this->getModalSize($modalSize);
        $this->closeButton = $closeButton;
        $this->id = $id ?? uniqid('modal-');
    }

    protected function getModalSize($size)
    {
        $sizes = [
            'sm' => 'max-w-sm',
            'md' => 'max-w-md',
            'lg' => 'max-w-lg',
            'xl' => 'max-w-xl',
            '2xl' => 'max-w-2xl',
            '3xl' => 'max-w-3xl',
            'full' => 'max-w-full',
        ];

        return $sizes[$size] ?? 'max-w-md';
    }

    public function render()
    {
        return view('dataview::components.modal.modal');
    }
}
