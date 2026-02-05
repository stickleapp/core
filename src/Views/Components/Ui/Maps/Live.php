<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\Ui\Maps;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;

class Live extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public ?string $heading = 'Live Map',
        public ?string $description = 'Real-time user activity map',
        public ?string $requestsEndpoint = null,
        public ?string $channel = null,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/maps/live');
    }
}
