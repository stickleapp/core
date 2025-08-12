<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\UI\Timelines;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;

class Sessions extends Component
{
    /**
     * Create the component instance.
     *
     * @return void
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public string $channel,
        public string $requestsEndpoint,
        public ?string $heading = null,
        public ?string $description = null,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/timelines/sessions');
    }
}
