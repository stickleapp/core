<?php

namespace StickleApp\Core\Views\Components\UI\Charts\Primatives;

use Illuminate\View\Component;
use Illuminate\View\View;

class LineChart extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/charts/primitives/line');
    }
}
