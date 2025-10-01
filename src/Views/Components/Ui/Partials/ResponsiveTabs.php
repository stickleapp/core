<?php

namespace App\View\Components;

use Illuminate\Container\Attributes\Config;
use Illuminate\View\Component;
use Illuminate\View\View;

class ExampleComponent extends Component
{
    public function __construct(
        #[Config('app.name')] protected ?string $appName,
        public string $id,
        public array $tabs,
        public bool $hideTabs = false,
        public string $someString = 'hello world'
    ) {}

    public function render(): View
    {
        return view('stickle::components/ui/partials/responsive-tabs');
    }
}
