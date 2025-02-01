<?php

namespace StickleApp\Core\Views\Components\UI\Charts;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;

class SegmentChart extends Component
{
    /**
     * Create the component instance.
     *
     * @param  string  $message
     * @return void
     */
    public function __construct(
        #[Config('stickle.api.prefix')] protected ?string $apiPrefix,
        public string $type,
        public string|int $segment,
        public string $attribute,
        public ?string $title,
        public ?array $labels = [],
        public ?array $data = []
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/charts/segment');
    }

    public function endpoint(): string
    {
        return $this->apiPrefix.'/segments/'.$this->segment.'/history';
    }
}
