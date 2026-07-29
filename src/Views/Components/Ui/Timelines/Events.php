<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\Ui\Timelines;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;
use StickleApp\Core\Views\Components\Concerns\ResolvesPolling;

class Events extends Component
{
    use ResolvesPolling;

    /**
     * Create the component instance.
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public string $channel,
        public ?string $heading = '',
        public ?string $description = '',
        public ?string $requestsEndpoint = null,
        #[Config('stickle.broadcasting.polling.interval')] int|string|null $pollInterval = null,
        #[Config('stickle.broadcasting.polling.enabled')] bool|string|int|null $pollingEnabled = null,
    ) {
        $this->resolvePolling($pollInterval, $pollingEnabled);
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/timelines/events');
    }
}
