<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\Ui\Chartlists;

use Exception;
use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;
use StickleApp\Core\Support\ClassUtils;
use StickleApp\Core\Traits\StickleEntity;

class Models extends Component
{
    /**
     * Create the component instance.
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

    /**
     * @return array<int, array<string, mixed>>
     */
    public function chartData(): array
    {

        $modelClass = ClassUtils::resolveModelClass($this->modelClass);

        throw_unless(ClassUtils::usesTrait($modelClass, StickleEntity::class), Exception::class, 'Model does not use StickleTrait.');

        return $modelClass::getStickleChartData();
    }
}
