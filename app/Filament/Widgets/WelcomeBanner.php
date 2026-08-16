<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class WelcomeBanner extends Widget
{
    protected static string $view = 'filament.widgets.welcome-banner';

    protected static ?int $sort = 0; // Show on very top!

    protected int | string | array $columnSpan = 'full'; // Span all columns!
}
