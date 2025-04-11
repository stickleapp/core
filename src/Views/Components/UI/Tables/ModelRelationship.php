<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\UI\Tables;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;

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
        public string $class,
        public ?string $heading,
        public ?string $subheading,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/tables/model-relationship');
    }

    public function endpoint(): string
    {
        return url()->query(
            $this->apiPrefix.'/models',
            [
                'class' => class_basename($this->class),
            ]
        );
    }
}
