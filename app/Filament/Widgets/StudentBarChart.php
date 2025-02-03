<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use Filament\Widgets\ChartWidget;

class StudentBarChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Student Registrations';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $minHeight = '800px';


    protected function getData(): array
    {
        $monthlyData = Student::selectRaw("strftime('%m', created_at) as month, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Ensure all months are represented
        $allMonths = array_fill(1, 12, 0);
        foreach ($monthlyData as $month => $count) {
            $allMonths[(int)$month] = $count; // Convert month string to integer for correct indexing
        }

        return [
            'datasets' => [
                [
                    'label' => 'Registrations',
                    'data' => array_values($allMonths),
                    'backgroundColor' => '#36A2EB',
                    'borderColor' => '#b00000',
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
