<?php

namespace StickleApp\Core\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class BlankLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('STICKLE::demo/layouts/blank');
    }
}
