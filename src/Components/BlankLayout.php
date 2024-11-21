<?php

namespace Dclaysmith\LaravelCascade\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class BlankLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('cascade::demo/layouts/blank');
    }
}
