<?php

namespace Modules\DataView\View\Components\DataView\Partials;

use Illuminate\View\Component;
use Illuminate\Support\Facades\Route;

class BackButton extends Component
{
    public $title;
    public $route;
    public $message;
    public $icon;

    public function __construct(
        string $route = "", 
        string $title = 'Back',
        string $icon = 'arrow_back'
    ) {
        $this->title = $title;
        $this->route = $route ?? Route::previous();
        $this->icon = $icon;
    }

    public function render()
    {
        return view('dataview::components.dataView.partials.backbutton');
    }
}