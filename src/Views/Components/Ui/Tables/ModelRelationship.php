<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\Ui\Tables;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;

class ModelRelationship extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public object $model,
        public string $relationship,
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

    public function relatedModel(): string
    {
        $relatedModel = $this->model->{$this->relationship}()->getRelated();

        return class_basename($relatedModel);
    }

    public function endpoint(): string
    {
        return url()->query(
            $this->apiPrefix.'/model-relationship',
            [
                'model_class' => class_basename($this->model),
                'object_uid' => $this->model->getKey(),
                'relationship' => $this->relationship,
            ]
        );
    }
}
