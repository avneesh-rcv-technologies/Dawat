<?php

namespace Modules\DataView\View\Components\DataView\Partials;

use Illuminate\View\Component;

class TopControls extends Component
{
    public $title;
    public $data;
    public $is_export;
    public function __construct(array $data = [], $title = '', $is_export = false)
    {
        $this->title = $title;
        $this->data = $data;
        $this->is_export = $is_export;
    }

    public function render()
    {
        $data = $this->data;
        return view('dataview::components.dataView.partials.top-controls', compact('data'));
    }
}
