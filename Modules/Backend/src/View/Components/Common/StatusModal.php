<?php
namespace Modules\Backend\View\Components\Common;

use Illuminate\View\Component;

class StatusModal extends Component
{


    public function __construct(

    ) {

    }

    public function render()
    {
        return view('backend::components.common.status-modal');
    }
}
