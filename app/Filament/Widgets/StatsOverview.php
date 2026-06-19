<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $cur = config('salon.currency', 'RM');

        $bookingSpark = collect(range(6, 0))
            ->map(fn ($i) => Booking::whereDate('created_at', today()->subDays($i))->count());

        $revenueSpark = collect(range(5, 0))->map(fn ($i) => (float) Invoice::where('status', 'paid')
            ->whereYear('issue_date', now()->subMonths($i)->year)
            ->whereMonth('issue_date', now()->subMonths($i)->month)
            ->sum('total'));

        $today = Booking::whereDate('created_at', today())->count();
        $newCount = Booking::where('status', 'new')->count();
        $revenue = $revenueSpark->last();
        $unpaidCount = Invoice::where('status', 'unpaid')->count();
        $unpaidSum = Invoice::where('status', 'unpaid')->sum('total');
        $customers = Customer::count();

        return [
            Stat::make('Bookings today', $today)
                ->description($bookingSpark->sum() . ' in the last 7 days')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->chart($bookingSpark->all())
                ->color('primary'),

            Stat::make('New bookings', $newCount)
                ->description('Awaiting action')
                ->descriptionIcon('heroicon-m-bell-alert')
                ->color('warning'),

            Stat::make('Revenue this month', $cur . ' ' . number_format((float) $revenue, 0))
                ->description($customers . ' total customers')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart($revenueSpark->all())
                ->color('success'),

            Stat::make('Unpaid invoices', $cur . ' ' . number_format((float) $unpaidSum, 0))
                ->description($unpaidCount . ' invoice' . ($unpaidCount === 1 ? '' : 's') . ' outstanding')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
