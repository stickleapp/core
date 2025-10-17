<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\Ui\Chartlists;

use Exception;
use StickleApp\Core\Traits\StickleEntity;
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

        throw_unless(class_exists($modelClass), Exception::class, 'Model not found: '.$modelClass);

        throw_unless(ClassUtils::usesTrait($modelClass, StickleEntity::class), Exception::class, 'Model does not use StickleTrait.');

        return $modelClass::getStickleChartData();
    }
}
