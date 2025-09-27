<?php

declare(strict_types=1);

namespace StickleApp\Core\Views\Components\Ui\Partials;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;

class ResponsiveTabs extends Component
{
    /**
     * Create the component instance.
     *
     * @return void
     */
    public function __construct(
        #[Config('stickle.routes.api.prefix')] protected ?string $apiPrefix,
        public string $id,
        public array $tabs,
        public bool $hideTabs = false,
        public string $responsiveClass = 'md',
        public string $activeTab = '',
    ) {
        // dd($this);
    }

    public function render(): View
    {
        return view('stickle::components/ui/partials/responsive-tabs', [
            'id' => $this->id,
            'tabs' => $this->tabs,
            'hideTabs' => $this->hideTabs,
            'responsiveClass' => $this->responsiveClass ?? '',
            'activeTab' => $this->activeTab,
        ]);
    }
}
