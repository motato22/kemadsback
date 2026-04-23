<?php

namespace App\Filament\AdvertisingPanel\Resources;

use App\Filament\AdvertisingPanel\Resources\AdvAdvertiserResource\Pages\CreateAdvAdvertiser;
use App\Filament\AdvertisingPanel\Resources\AdvAdvertiserResource\Pages\EditAdvAdvertiser;
use App\Filament\AdvertisingPanel\Resources\AdvAdvertiserResource\Pages\ListAdvAdvertisers;
use App\Models\Advertising\AdvAdvertiser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdvAdvertiserResource extends Resource
{
    protected static ?string $model = AdvAdvertiser::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Publicidad';
    protected static ?int $navigationSort = 2;
    protected static ?string $label = 'Anunciante';
    protected static ?string $pluralLabel = 'Anunciantes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información fiscal')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Razón social')
                            ->required()
                            ->maxLength(120),

                        Forms\Components\TextInput::make('rfc')
                            ->label('RFC')
                            ->maxLength(13)
                            ->placeholder('Ej: XAXX010101000'),
                    ])->columns(2),

                Forms\Components\Section::make('Contacto')
                    ->schema([
                        Forms\Components\TextInput::make('contact_name')
                            ->label('Nombre del contacto')
                            ->maxLength(80),

                        Forms\Components\TextInput::make('contact_email')
                            ->label('Email')
                            ->email()
                            ->maxLength(120),

                        Forms\Components\TextInput::make('contact_phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(20),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Razón social')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('rfc')
                    ->label('RFC'),

                Tables\Columns\TextColumn::make('contact_name')
                    ->label('Contacto'),

                Tables\Columns\TextColumn::make('contact_email')
                    ->label('Email')
                    ->copyable(),

                Tables\Columns\TextColumn::make('campaigns_count')
                    ->label('Campañas')
                    ->counts('campaigns'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Solo activos'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListAdvAdvertisers::route('/'),
            'create' => CreateAdvAdvertiser::route('/create'),
            'edit'   => EditAdvAdvertiser::route('/{record}/edit'),
        ];
    }
}
