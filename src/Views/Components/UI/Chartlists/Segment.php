<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\UI\Chartlists;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;
use StickleApp\Core\Support\ClassUtils;

class Segment extends Component
{
    /**
     * Create the component instance.
     *
     * @return void
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public object $segment,
        public ?string $heading,
        public ?string $description,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/chartlists/segment');
    }

    public function chartData(): array
    {

        $modelClass = config('stickle.namespaces.models').'\\'.$this->segment->model_class;

        if (! ClassUtils::usesTrait($modelClass, 'StickleApp\\Core\\Traits\\StickleEntity')) {
            return [];
        }

        return $modelClass::getStickleChartData();
    }
}
