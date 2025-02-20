<?php

namespace StickleApp\Core\Views\Components\UI\Charts;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;

class SegmentChart extends Component
{
    /**
     * Create the component instance.
     * @return void
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public string $type,
        public int $segmentId,
        public string $attribute,
        public ?string $title
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
        return url()->query(
            $this->apiPrefix.'/segment-statistics',
            [
                'segment_id' => $this->segmentId,
                'attribute' => $this->attribute,
                'date_from' => now()->subDays(30)->toDateString(),
            ]
        );
    }
}
