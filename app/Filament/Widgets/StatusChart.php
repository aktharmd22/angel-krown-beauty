<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;

class StatusChart extends ChartWidget
{
    protected static ?string $heading = 'Bookings by status';

    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $statuses = ['new', 'confirmed', 'done', 'cancelled'];
        $counts = array_map(fn ($s) => Booking::where('status', $s)->count(), $statuses);

        return [
            'datasets' => [[
                'data' => $counts,
                'backgroundColor' => ['#C9A24B', '#1faf57', '#8B1A4F', '#d14343'],
                'borderWidth' => 0,
            ]],
            'labels' => ['New', 'Confirmed', 'Done', 'Cancelled'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom']],
        ];
    }
}
