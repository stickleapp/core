<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\UI;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;

class ModelAttributes extends Component
{
    /**
     * Create the component instance.
     *
     * @return void
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public object $model,
        public ?string $heading,
        public ?string $subheading,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/model-attributes');
    }

    public function endpoint(): string
    {
        return url()->query(
            $this->apiPrefix.'/models',
            [
                'model_class' => class_basename($this->model),
                'uid' => $this->model->getKey(),
            ]
        );
    }
}
