<?php

namespace StickleApp\Core\Views\Components\Demo\Layouts;

use Illuminate\View\Component;
use Illuminate\View\View;

class DefaultLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/demo/layouts/blank');
    }
}
