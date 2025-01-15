<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentlyRegisteredStudents extends BaseWidget
{
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Student::query()
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                 TextColumn::make("name"),
                 TextColumn::make("class.name"),
                 TextColumn::make("section.name"),
            ])
            ->defaultPaginationPageOption(5);
    }
}
