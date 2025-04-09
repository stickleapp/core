<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\UI\Chartlists;

use Illuminate\Container\Attributes\Config;
use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\View;
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
        public string $class,
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

        $class = config('stickle.namespaces.models').'\\'.Str::ucfirst($this->class);

        if (! class_exists($class)) {
            throw new \Exception('Model not found');
        }

        if (! ClassUtils::usesTrait($class, 'StickleApp\\Core\\Traits\\StickleEntity')) {
            throw new \Exception('Model does not use StickleTrait.');
        }

        return $class::getStickleChartData();
    }
}
