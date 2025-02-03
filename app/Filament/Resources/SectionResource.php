<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SectionResource\Pages;
use App\Filament\Resources\SectionResource\RelationManagers;
use App\Filament\Resources\SectionResource\Widgets\CreateSectionWidget;
use App\Models\Classes;
use App\Models\Section;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rules\Unique;

class SectionResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $navigationGroup = "Sections";

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }

    public static function canCreate(): bool
    {
       return false;
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('class_id')
                    ->required()
                    ->relationship('class', 'name'),
                    // ->options(fn()=> Classes::pluck('name', 'id')),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing:function(Get $get, Unique $rule){
                        return $rule->where('class_id', $get('class_id'));
                    }),
            ]);
    }


    protected function getTableHeaderActions(): array
    {
        return [
            \Filament\Tables\Actions\Action::make('addSection')
                ->label('Add Section')
                ->action(fn () => $this->dispatchBrowserEvent('toggle-create-section-widget')),
        ];
    }


    public static function table(Table $table): Table
    {
        return $table
            // ->header(function () {
            //     return view('filament.resources.sections.add-section-form');
            // })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('class.name')
                    ->badge(),
                Tables\Columns\TextColumn::make('students_count')
                    ->counts('students')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultPaginationPageOption(5)
            ->filters([
                    
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSections::route('/'),
            'create' => Pages\CreateSection::route('/create'),
            'edit' => Pages\EditSection::route('/{record}/edit'),
        ];
    }
}
