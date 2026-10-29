<?php
namespace Modules\Backend\View\Components\Common;

use Illuminate\View\Component;

class Button extends Component
{
    public string $as;
    public string $type;
    public string $href;
    public string $icon;
    public string $label;
    public bool $active;
    public ?string $badge;
    public ?string $id;
    public ?string $name;
    public array $calls;
    public string $class;

    public function __construct(
        string $as = 'button',        // 'button' or 'a'
        string $type = 'button',      // submit, button, etc.
        string $href = '#',
        string $icon = '',
        string $label = '',
        bool $active = false,
        string $badge = null,
        string $id = null,
        string $name = null,
        array $calls = [],
        string $class = ''
    ) {
        $this->as = $as;
        $this->type = $type;
        $this->href = $href;
        $this->icon = $icon;
        $this->label = $label;
        $this->active = $active;
        $this->badge = $badge;
        $this->id = $id;
        $this->name = $name;
        $this->calls = $calls;
        $this->class = $class;
    }

    public function render()
    {
        return view('backend::components.common.button');
    }
}
