<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\UI\Maps;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;

class Live extends Component
{
    /**
     * Create the component instance.
     *
     * @return void
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public ?string $heading = 'Live Map',
        public ?string $description = 'Real-time user activity map',
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/maps/live');
    }
}