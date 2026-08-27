<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\Ui\Chartlists;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;
use StickleApp\Core\Contracts\StickleTrackableContract;
use StickleApp\Core\Support\ClassUtils;
use StickleApp\Core\Traits\StickleEntity;

class Model extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public object $model,
        public ?string $heading,
        public ?string $description,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/chartlists/model');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function chartData(): array
    {
        if (! ClassUtils::usesTrait($this->model, StickleEntity::class)) {
            return [];
        }

        /**
         * The guard above is what makes this narrowing true. $model is declared
         * `object` so any model can be passed, and usesTrait() is the runtime
         * check that it carries the surface; this states the same fact in a
         * form the analyser can read.
         *
         * @var \Illuminate\Database\Eloquent\Model&StickleTrackableContract $model
         */
        $model = $this->model;

        return $model::getStickleChartData();
    }
}
