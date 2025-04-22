<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\UI\Charts;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;
use StickleApp\Core\Enums\AggregateType;
use StickleApp\Core\Enums\ChartType;
use StickleApp\Core\Enums\DataType;

class Segment extends Component
{
    /**
     * Create the component instance.
     *
     * @return void
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public string $key,
        public object $segment,
        public string $attribute,
        public ChartType $chartType,
        public ?string $label,
        public ?string $description,
        public ?DataType $dataType,
        public ?AggregateType $primaryAggregate
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
                'segment_id' => $this->segment->getKey(),
                'attribute' => $this->attribute,
                'date_from' => now()->subDays(30)->toDateString(),
            ]
        );
    }
}
