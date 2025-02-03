<?php

namespace App\Filament\Resources\SectionResource\Widgets;

use App\Models\Classes;
use App\Models\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Widgets\Widget;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rules\Unique;

class CreateSectionWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.resources.section-resource.widgets.create-section-widget';

    protected int | string | array $columnSpan = 'full';
 
    public ?array $data = [];
 
    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('class_id')
                    ->required()
                    ->options(fn()=> Classes::pluck('name', 'id')),
                TextInput::make('name')
                    ->required()
                    // ->unique(ignoreRecord: true, modifyRuleUsing:function(Get $get, Unique $rule){
                    //     return $rule->where('class_id', $get('class_id'));
                    // }),
                    ->unique(
                        table: Section::class,
                        column: 'name',
                        ignoreRecord: true,
                        modifyRuleUsing: function (Get $get, Unique $rule) {
                            return $rule->where('class_id', $get('class_id'));
                        }
                    )
                    ->validationAttribute('Section Name')
            ])
            ->statePath('data');
    }

    

    public function create(): void
    {
        Section::create($this->form->getState());
        $this->form->fill();

        // Dispatch a success notification
        Notification::make()
            ->title('Section Created')
            ->body('The section has been successfully created.')
            ->success()
            ->send();

        $this->dispatch('section-created');
    }
}
