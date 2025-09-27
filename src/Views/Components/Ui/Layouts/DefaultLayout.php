<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\Ui\Layouts;

use Illuminate\View\Component;
use Illuminate\View\View;
use StickleApp\Core\Support\ClassUtils;

class DefaultLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('stickle::components/ui/layouts/default-layout');
    }

    public function models(): array
    {
        return ClassUtils::getClassesWithTrait(
            config('stickle.namespaces.models'),
            \StickleApp\Core\Traits\StickleEntity::class
        );
    }
}
