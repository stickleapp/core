<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\Ui\Chartlists;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;
use StickleApp\Core\Support\ClassUtils;

class ModelRelationship extends Component
{
    /**
     * Create the component instance.
     *
     * @return void
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public object $model,
        public string $relationship,
        public ?string $heading,
        public ?string $description,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/chartlists/model-relationship');
    }

    public function chartData(): array
    {

        $modelClass = get_class($this->model);

        if (! class_exists($modelClass)) {
            throw new \Exception('Model not found');
        }

        if (! ClassUtils::usesTrait($modelClass, 'StickleApp\\Core\\Traits\\StickleEntity')) {
            throw new \Exception('Model does not use StickleTrait.');
        }

        return $modelClass::getStickleChartData();
    }
}
