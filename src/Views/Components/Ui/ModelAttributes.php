<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\Ui;

use Illuminate\Container\Attributes\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use Illuminate\View\View;

class ModelAttributes extends Component
{
    /**
     * Create the component instance.
     *
     * $model stays `object` at runtime so the component stays permissive; the
     * docblock tells the analyser what every caller actually supplies.
     *
     * @param  Model  $model
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
