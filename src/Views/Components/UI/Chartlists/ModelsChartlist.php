<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\UI\Chartlists;

use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\View;
use StickleApp\Core\Attributes\StickleAttributeMetadata;
use StickleApp\Core\Support\AttributeUtils;
use StickleApp\Core\Support\ClassUtils;

class ModelsChartlist extends Component
{
    /**
     * Create the component instance.
     *
     * @return void
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public string $model,
        public ?string $heading,
        public ?string $description,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/chartlists/models-chartlist');
    }

    public function chartData(): array
    {

        $class = config('stickle.namespaces.models').'\\'.Str::ucfirst($this->model);

        if (! class_exists($class)) {
            throw new \Exception('Model not found');
        }

        if (! ClassUtils::usesTrait($class, 'StickleApp\\Core\\Traits\\StickleEntity')) {
            throw new \Exception('Model does not use StickleTrait.');
        }

        // Get the attributes that are tracked by StickleTrait as keys with empty arrays as values
        $stickleTrackedAttributes = array_fill_keys($class::getStickleTrackedAttributes(), []);

        // Get the metadata [ 'attribute' => [ 'chartType' => 'line', 'label' => 'Attribute', 'description' => 'Description', 'dataType' => 'string', 'primaryAggregateType' => 'sum' ] ]
        $metadata = AttributeUtils::getAttributesForClass(
            $class,
            StickleAttributeMetadata::class
        );

        // Apply any metadata to this list
        $chartData = array_intersect_key($metadata, $stickleTrackedAttributes);

        return array_map(function ($attribute, $meta) {
            return [
                'key' => $attribute,
                'model' => $this->model,
                'attribute' => $attribute,
                'chartType' => $meta['chartType'] ?? 'line',
                'label' => $meta['label'] ?? Str::title(str_replace('_', ' ', $attribute)),
                'description' => $meta['description'] ?? null,
                'dataType' => $meta['dataType'] ?? null,
                'primaryAggregateType' => $meta['primaryAggregateType'] ?? null,
            ];
        }, array_keys($chartData), array_values($chartData));
    }
}
