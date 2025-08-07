<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Usuários';
    protected static ?string $pluralLabel = 'Usuários';
    protected static ?string $label = 'Usuário';

    // Método auxiliar mais seguro
    protected static function checkUserPermission(string $permission): bool
    {
        try {
            return Auth::check() && Auth::user()->can($permission);
        } catch (\Exception $e) {
            return false;
        }
    }

    // Controlar visibilidade no menu - APENAS para quem tem view_users
    public static function shouldRegisterNavigation(): bool
    {
        return static::checkUserPermission('view_users');
    }

    // Controles de acesso
    public static function canViewAny(): bool
    {
        return static::checkUserPermission('view_users');
    }

    public static function canCreate(): bool
    {
        return static::checkUserPermission('create_users');
    }

    public static function canEdit($record): bool
    {
        return static::checkUserPermission('edit_users');
    }

    public static function canDelete($record): bool
    {
        if (!static::checkUserPermission('delete_users')) {
            return false;
        }
        
        if (!Auth::check() || !$record instanceof User) {
            return false;
        }
        
        return $record->id !== Auth::id();
    }

    public static function canView($record): bool
    {
        return static::checkUserPermission('view_users');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Card::make()->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrated(fn ($state): bool => filled($state))
                    ->dehydrateStateUsing(fn ($state): string => Hash::make($state))
                    ->minLength(8),

                Forms\Components\Toggle::make('is_active')
                    ->label('Usuário Ativo')
                    ->default(true),

                Forms\Components\Select::make('roles')
                    ->label('Funções')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->options(Role::all()->pluck('name', 'id'))
                    ->preload()
                    ->visible(fn (): bool => static::checkUserPermission('manage_permissions')),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Funções')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'user' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean(),

                Tables\Filters\SelectFilter::make('roles')
                    ->label('Funções')
                    ->relationship('roles', 'name')
                    ->multiple(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn () => static::checkUserPermission('view_users')),
                    
                Tables\Actions\EditAction::make()
                    ->visible(fn () => static::checkUserPermission('edit_users')),
                    
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (User $record) => 
                        static::checkUserPermission('delete_users') && 
                        $record->id !== Auth::id()
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => static::checkUserPermission('delete_users')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            // 'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}