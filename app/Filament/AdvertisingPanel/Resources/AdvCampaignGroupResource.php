<?php

namespace App\Filament\AdvertisingPanel\Resources;

use App\Filament\AdvertisingPanel\Resources\AdvCampaignGroupResource\Pages\CreateAdvCampaignGroup;
use App\Filament\AdvertisingPanel\Resources\AdvCampaignGroupResource\Pages\EditAdvCampaignGroup;
use App\Filament\AdvertisingPanel\Resources\AdvCampaignGroupResource\Pages\ListAdvCampaignGroups;
use App\Models\Advertising\AdvCampaignGroup;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class AdvCampaignGroupResource extends Resource
{
    protected static ?string $model = AdvCampaignGroup::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = 'Dispositivos';
    protected static ?int $navigationSort = 2;
    protected static ?string $label = 'Grupo';
    protected static ?string $pluralLabel = 'Grupos de Tablets';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nombre del grupo')
                    ->required()
                    ->maxLength(80)
                    ->placeholder('Ej: Ruta Norte, Flota Completa'),

                Forms\Components\Textarea::make('description')
                    ->label('Descripción')
                    ->rows(3),

                Forms\Components\Section::make('Tablets asignadas')
                    ->schema([
                        Forms\Components\CheckboxList::make('tablets')
                            ->relationship(
                                name: 'tablets',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (\Illuminate\Database\Eloquent\Builder $query, $record) {
                                    return $query->whereDoesntHave('campaignGroups', function ($q) use ($record) {
                                        if ($record) {
                                            $q->where('adv_campaign_groups.id', '!=', $record->id);
                                        }
                                    });
                                }
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->unit_id} — {$record->name} " . ($record->isOnline() ? '🟢' : '🔴'))
                            ->columns(2)
                            ->searchable()
                            ->bulkToggleable(),
                    ]),

                Forms\Components\Section::make('Campañas asignadas')
                    ->schema([
                        Forms\Components\CheckboxList::make('campaigns')
                            ->relationship(
                                name: 'campaigns',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn ($query) => $query->whereIn('status', ['active', 'scheduled'])
                            )
                            ->columns(1)
                            ->searchable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Grupo')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tablets_count')
                    ->label('Tablets')
                    ->counts('tablets'),

                Tables\Columns\TextColumn::make('campaigns_count')
                    ->label('Campañas')
                    ->counts('campaigns'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAdvCampaignGroups::route('/'),
            'create' => CreateAdvCampaignGroup::route('/create'),
            'edit'   => EditAdvCampaignGroup::route('/{record}/edit'),
        ];
    }
}
