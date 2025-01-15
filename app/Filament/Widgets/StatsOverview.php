<?php

namespace App\Filament\Widgets;

use App\Models\Classes;
use App\Models\Section;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;


    protected function getStats(): array
    {
        return [
            Stat::make('Total Students', Student::count())
                ->description('Total Students')
                ->descriptionIcon('heroicon-m-users')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Total Classes', Classes::count())
                ->description('Total Classes')
                ->descriptionIcon('heroicon-m-building-library')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('info'),
            Stat::make('Total Sections', Section::count())  
                ->description('Total Sections')
                ->descriptionIcon('heroicon-m-square-3-stack-3d')
                ->chart([7, 2, 10, 3, 15, 4, 17])   
                ->color('danger'),
        ];
    }
}
