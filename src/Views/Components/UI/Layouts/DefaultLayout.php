<?php

namespace StickleApp\Core\Views\Components\UI\Layouts;

use Illuminate\View\Component;
use Illuminate\View\View;

class DefaultLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/layouts/default');
    }
}
