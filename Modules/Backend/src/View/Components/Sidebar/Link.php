<?php
namespace Modules\Backend\View\Components\Sidebar;

use Illuminate\View\Component;

class Link extends Component
{
    public string $href;
    public string $icon;
    public string $label;
    public bool $active;
    public string|null $badge;

    public function __construct(string $href = '#', string $icon = 'dashboard', string $label = '', bool $active = false, string $badge = null)
    {
        $this->href = $href;
        $this->icon = $icon;
        $this->label = $label;
        $this->active = $active;
        $this->badge = $badge;
    }

    public function render()
    {
        return view('backend::components.sidebar.link');
    }
}
