<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\Ui\Chartlists;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;
use StickleApp\Core\Support\ClassUtils;
use StickleApp\Core\Traits\StickleEntity;

class ModelCarousel extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public object $model,
        public ?string $heading = null,
        public ?string $description = null,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/chartlists/model-carousel');
    }

    public function chartData(): array
    {
        if (! ClassUtils::usesTrait($this->model, StickleEntity::class)) {
            return [];
        }

        return $this->model::getStickleChartData();
    }
}
