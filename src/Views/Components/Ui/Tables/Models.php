<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\Ui\Tables;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;

class Models extends Component
{
    /**
     * Create the component instance.
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public string $modelClass,
        public ?string $heading,
        public ?string $subheading,
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/tables/models');
    }

    public function endpoint(): string
    {
        return url()->query(
            $this->apiPrefix.'/models'
        );
    }
}
