<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;

class TopServices extends ChartWidget
{
    protected static ?string $heading = 'Top services';

    protected static ?int $sort = 4;

    protected static bool $isLazy = false;

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $rows = Booking::query()
            ->whereNotNull('service')
            ->selectRaw('service, COUNT(*) as total')
            ->groupBy('service')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return [
            'datasets' => [[
                'label' => 'Bookings',
                'data' => $rows->pluck('total')->all(),
                'backgroundColor' => 'rgba(139,26,79,.75)',
                'borderRadius' => 6,
            ]],
            'labels' => $rows->pluck('service')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
        ];
    }
}
