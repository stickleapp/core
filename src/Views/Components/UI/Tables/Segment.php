<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\UI\Tables;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;

class Segment extends Component
{
    /**
     * Create the component instance.
     *
     * @return void
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public int $segmentId,
        public ?string $heading,
        public ?string $subheading,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/tables/segment');
    }

    public function endpoint(): string
    {
        return url()->query(
            $this->apiPrefix.'/segment-objects',
            [
                'segment_id' => $this->segmentId,
            ]
        );
    }
}
