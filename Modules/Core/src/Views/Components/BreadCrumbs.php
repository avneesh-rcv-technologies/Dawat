<?php

namespace Modules\Core\Views\Components;

use Illuminate\View\Component;

class BreadCrumbs extends Component
{
    public $breadcrumbs;
    public $name;

    public function __construct($breadcrumbs, $name)
    {
        $this->breadcrumbs = $breadcrumbs;
        $this->name = $name;

    }
    public function render()
    {
        return view('core::components.bread-crumbs');
    }

}