<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Resources\WalletResource\Pages;
use App\Filament\Resources\WalletResource\RelationManagers;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationLabel = 'Carteiras';

    protected static ?string $modelLabel = 'Carteiras';

    protected static ?string $navigationGroup = 'Administração';

    protected static ?string $slug = 'minha-carteira';

    protected static ?int $navigationSort = 1;

    /**
     * @dev @victormsalatiel
     * @return bool
     */
    public static function canAccess(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * @return bool
     */
    public static function canCreate(): bool
    {
        return false;
    }

    /**
     * @param Model $record
     * @return string
     */
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->user->name; // TODO: Change the autogenerated stub
    }

    /**
     * @return string[]
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'user.email']; // TODO: Change the autogenerated stub
    }

    /**
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Usuário')
                    ->description('Selecione um usuário')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Usuários')
                            ->placeholder('Selecione um usuário')
                            ->relationship(name: 'user', titleAttribute: 'name')
                            ->options(
                                fn($get) => User::query()
                                    ->pluck('name', 'id')
                            )
                            ->searchable()
                            ->preload()
                            ->live(),
                ]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('balance')
                            ->required()
                            ->numeric()
                            ->default(0.00),
                        Forms\Components\TextInput::make('balance_bonus')
                            ->required()
                            ->numeric()
                            ->default(0.00),
                        Forms\Components\TextInput::make('refer_rewards')
                            ->label('Saldo de Afiliado')
                            ->required()
                            ->numeric()
                            ->default(0.00),
                        Forms\Components\TextInput::make('balance_demo')
                            ->label('Saldo Influencer')
                            ->required()
                            ->numeric()
                            ->default(0.00),
                        Forms\Components\TextInput::make('balance_withdrawal')
                            ->label('Saldo Saque')
                            ->required()
                            ->numeric()
                            ->default(0.00),
                        ])->columns(5),
            ]);
    }

    /**
     * @param Table $table
     * @return Table
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuário')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Saldo')
                    ->formatStateUsing(fn (string $state): string => \Helper::amountFormatDecimal($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance_withdrawal')
                    ->label('Saldo Saq.')
                    ->formatStateUsing(fn (string $state): string => \Helper::amountFormatDecimal($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance_bonus')
                    ->label('Bônus')
                    ->formatStateUsing(fn (string $state): string => \Helper::amountFormatDecimal($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('balance_bonus_rollover')
                    ->label('Saldo B Roll.')
                    ->formatStateUsing(fn (string $state): string => \Helper::amountFormatDecimal($state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')->label('Criado a partir de'),
                        DatePicker::make('created_until')->label('Criado até'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Criado a partir de ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Criado até ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                //Tables\Actions\CreateAction::make(),
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
            'index' => \App\Filament\Admin\Resources\WalletResource\Pages\ListWallets::route('/'),
            'create' => \App\Filament\Admin\Resources\WalletResource\Pages\CreateWallet::route('/create'),
            'edit' => \App\Filament\Admin\Resources\WalletResource\Pages\EditWallet::route('/{record}/edit'),
        ];
    }
}
