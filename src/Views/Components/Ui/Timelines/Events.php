<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\Ui\Timelines;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;

class Events extends Component
{
    /**
     * Create the component instance.
     *
     * @return void
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public string $channel,
        public ?string $heading = '',
        public ?string $description = '',
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/timelines/events');
    }
}
