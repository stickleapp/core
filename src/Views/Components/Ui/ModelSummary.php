<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\UI;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;

class ModelSummary extends Component
{
    /**
     * Create the component instance.
     *
     * @return void
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public ?string $heading = 'Model Details',
        public ?string $description = 'Selected model information and statistics',
        public ?array $model = null,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/model-summary');
    }
}
