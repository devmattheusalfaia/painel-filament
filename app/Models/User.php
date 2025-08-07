<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Determina se o usuário pode acessar o painel administrativo
     * SIMPLIFICADO - apenas verifica se está ativo
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Método mais simples - apenas verifica se está ativo
        return $this->is_active ?? true; // Se is_active for null, permite acesso
    }

    /**
     * Verifica se o usuário pode gerenciar usuários
     */
    public function canManageUsers(): bool
    {
        return $this->can('view_users');
    }

    /**
     * Verifica se é administrador
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Verifica se é usuário comum
     */
    public function isUser(): bool
    {
        return $this->hasRole('user');
    }

    // Scopes úteis
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }
}
