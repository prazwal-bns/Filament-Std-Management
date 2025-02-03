<?php

namespace App\Filament\Resources;

use App\Exports\StudentsExport;
use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Widgets\StatsOverview;
use App\Models\Classes;
use App\Models\Section;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Facades\Excel;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = "Students";

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'class.name', 'section.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Student Name' => $record->name,
            'Class Name' => $record->class->name,
            'Section Name' => $record->section->name,
        ];
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->autofocus()
                    ->required(),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required(),

                Forms\Components\Select::make('class_id')
                    ->required()
                    ->relationship('class','name')
                    // ->afterStateUpdated(fn (callable $set) => $set('section_id', null))
                    ->reactive(), 

                // Forms\Components\Select::make('section_id')
                //     ->required()
                //     ->relationship('section', 'name', fn ($query, $get) => 
                //         $query->where('class_id', $get('class_id'))
                //     )
                //     ->reactive() 
                //     ->placeholder('Select a section'),

                Select::make('section_id')
                    ->reactive()
                    ->options(function(Get $get){
                        $classId = $get('class_id');
                        if($classId){
                            return Section::where('class_id', $classId)->pluck('name', 'id')->toArray();
                        }
                    })
                    ->placeholder('Select a section'),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    // ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->sortable(),
                Tables\Columns\TextColumn::make('class.name')
                    ->sortable()
                    // ->searchable()
                    ->badge(),
                Tables\Columns\TextColumn::make('section.name')
                    // ->badge()
                    ->searchable()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('class-section-filter')
                    ->form([
                        Select::make('class_id')
                            ->label('Filter By Class')
                            ->placeholder('Select Class')
                            ->options( Classes::pluck('name', 'id')->toArray()),
                        
                        Select::make('section_id')
                            ->label('Section Name')
                            ->placeholder('Select Section')
                            ->options(function(Get $get){
                                $classId = $get('class_id');
                                if($classId){
                                    return Section::where('class_id', $classId)->pluck('name', 'id')->toArray();
                                }
                            })
                ])
            
                ->query(function(Builder $query, array $data): Builder{
                        return $query->when($data['class_id'], function($query) use ($data){
                            $query->where('class_id', $data['class_id']);
                        })
                        ->when($data['section_id'], function($query) use ($data){
                            $query->where('section_id', $data['section_id']);    
                        });
                })
            ])
            

            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('downloadPdf')
                  ->url(function(Student $student) {
                        return route('student.generate.invoice', $student);
                  })
                  ->openUrlInNewTab(),
                Action::make('qrCode')
                  ->url(function(Student $student) {
                        return static::getUrl('qrCode', ['record'=> $student]);
                  })
                  ->openUrlInNewTab()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    BulkAction::make('export')
                        ->label('Export To Excel')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function(Collection $records){
                            return Excel::download(new StudentsExport($records), 'students.xlsx');
                        }),
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
            'qrCode' => Pages\GenerateQrCode::route('/{record}/qrcode'),
        ];
    }
 
    public static function getWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }
}
