<?php

namespace StickleApp\Core\Views\Components\UI\Tables;

use Illuminate\View\Component;
use Illuminate\View\View;

class SegmentTable extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/tables/segment');
    }
}
