<?php

namespace Modules\DataView\View\Components\DataView\Partials;

use Illuminate\View\Component;

class Table extends Component
{
     public $title;
    public $data;
    public function __construct(array $data = [], $title = '')
    {
        $this->title = $title;
        $this->data = $data;
    }

    public function render()
    {
        $data = $this->data;
        return view('dataview::components.dataView.partials.table', compact('data'));
    }
}
