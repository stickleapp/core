<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\UI\Chartlists;

use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\View;
use StickleApp\Core\Support\ClassUtils;

class Models extends Component
{
    /**
     * Create the component instance.
     *
     * @return void
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public string $modelClass,
        public ?string $heading,
        public ?string $description,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/chartlists/models');
    }

    public function chartData(): array
    {

        $modelClass = config('stickle.namespaces.models').'\\'.Str::ucfirst($this->modelClass);

        if (! class_exists($modelClass)) {
            throw new \Exception('Model not found: '.$modelClass);
        }

        if (! ClassUtils::usesTrait($modelClass, 'StickleApp\\Core\\Traits\\StickleEntity')) {
            throw new \Exception('Model does not use StickleTrait.');
        }

        return $modelClass::getStickleChartData();
    }
}
