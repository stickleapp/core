<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\Ui\Charts\Primatives;

use Illuminate\View\Component;
use Illuminate\View\View;

class Delta extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/charts/primatives/delta');
    }
}
