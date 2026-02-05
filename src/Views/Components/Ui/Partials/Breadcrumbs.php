<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\Ui\Partials;

use Illuminate\View\Component;
use Illuminate\View\View;

class Breadcrumbs extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        public array $pages,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/partials/breadcrumbs');
    }
}
