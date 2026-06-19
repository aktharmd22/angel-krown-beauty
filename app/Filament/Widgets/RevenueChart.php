<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue — last 6 months';

    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected static ?string $maxHeight = '280px';

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i));

        $revenue = $months->map(fn ($m) => (float) Invoice::where('status', 'paid')
            ->whereYear('issue_date', $m->year)
            ->whereMonth('issue_date', $m->month)
            ->sum('total'));

        return [
            'datasets' => [[
                'label' => 'Revenue (' . config('salon.currency', 'RM') . ')',
                'data' => $revenue->all(),
                'borderColor' => '#C9A24B',
                'backgroundColor' => 'rgba(201,162,75,.15)',
                'fill' => true,
                'tension' => 0.35,
                'pointBackgroundColor' => '#C9A24B',
            ]],
            'labels' => $months->map(fn ($m) => $m->format('M Y'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true]],
        ];
    }
}
