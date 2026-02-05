<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\Ui\Charts;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;
use StickleApp\Core\Enums\AggregateType;
use StickleApp\Core\Enums\ChartType;
use StickleApp\Core\Enums\DataType;

class ModelRelationship extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public string $key,
        public object $model,
        public string $relationship,
        public string $attribute,
        public ChartType $chartType,
        public mixed $currentValue,
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
        return view('stickle::components/ui/charts/model-relationship');
    }

    public function endpoint(): string
    {
        return url()->query(
            $this->apiPrefix.'/model-relationship-statistics',
            [
                'model_class' => class_basename($this->model),
                'uid' => $this->model->getKey(),
                'relationship' => $this->relationship,
                'attribute' => $this->attribute,
                'date_from' => now()->subDays(30)->toDateString(),
            ]
        );
    }
}
