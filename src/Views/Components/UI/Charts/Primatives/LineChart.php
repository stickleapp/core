<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\UI\Charts\Primatives;

use Illuminate\View\Component;
use Illuminate\View\View;

class LineChart extends Component
{
    /**
     * Create the component instance.
     *
     * @return void
     */
    public function __construct(
        public string $key,
        public ?array $apiData = null,
        public ?string $color = 'rgba(250, 204, 21, .7)',
        public ?string $hoverColor = 'rgba(250, 204, 21, 1)'
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/charts/primatives/line');
    }
}
