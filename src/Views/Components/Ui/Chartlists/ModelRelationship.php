<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\Ui\Chartlists;

use Exception;
use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;
use StickleApp\Core\Support\ClassUtils;
use StickleApp\Core\Traits\StickleEntity;

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

        $modelClass = $this->model::class;

        throw_unless(class_exists($modelClass), Exception::class, 'Model not found');

        throw_unless(ClassUtils::usesTrait($modelClass, StickleEntity::class), Exception::class, 'Model does not use StickleTrait.');

        return $modelClass::getStickleChartData();
    }
}
