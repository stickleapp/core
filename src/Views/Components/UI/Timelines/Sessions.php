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
        public ?string $heading = 'Active Sessions',
        public ?string $description = 'Users currently online',
        public ?string $channel = 'stickle.firehose',
        public ?string $activitiesEndpoint = null,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/timelines/sessions');
    }
}
