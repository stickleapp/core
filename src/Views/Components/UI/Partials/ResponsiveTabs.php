<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\UI\Partials;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;

class ResponsiveTabs extends Component
{
    public string $id;

    public array $tabs;

    public bool $hideTabs;

    public string $responsiveClass;

    public string $activeTab;

    /**
     * Create the component instance.
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        string $id,
        array $tabs,
        bool $hideTabs = false,
        string $responsiveClass = 'md',
        string $activeTab = '',
    ) {
        $this->id = $id;
        $this->tabs = $tabs;
        $this->hideTabs = $hideTabs;
        $this->responsiveClass = $responsiveClass;
        $this->activeTab = $activeTab;
    }

    public function render(): View
    {
        return view('stickle::components/ui/partials/responsive-tabs');
    }
}
