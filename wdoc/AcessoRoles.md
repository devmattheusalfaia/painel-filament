# 📋 Sistema de Controle de Acesso - Documentação Completa

## 🎯 Visão Geral

Este sistema implementa controle de acesso granular usando o pacote **Spatie Laravel Permission** integrado com **Filament**. Permite definir quem pode ver, criar, editar e deletar recursos específicos.

## 🏗️ Arquitetura do Sistema

### Componentes Principais:

1. **Permissions** (Permissões): Ações específicas (`view_users`, `create_posts`)
2. **Roles** (Funções): Grupos de permissões (`admin`, `editor`, `user`)
3. **Users** (Usuários): Recebem roles e/ou permissões diretamente
4. **Resources** (Recursos): Controlam acesso no Filament
5. **Guards/Gates**: Verificações de acesso

---

## 🔐 Como Funcionam as Regras

### Hierarquia de Acesso:
```
User → hasRole('admin') → Role tem Permission('view_users') → Acesso LIBERADO
User → hasPermission('view_users') → Acesso LIBERADO 
User → sem role/permission → Acesso NEGADO
```

### Fluxo de Verificação:
1. Usuário tenta acessar recurso
2. Sistema verifica se está autenticado
3. Verifica se tem a permissão necessária (via role ou diretamente)
4. Libera ou bloqueia acesso

---

## 🛠️ Implementação Passo a Passo

### 1. Configuração Inicial

```bash
# Instalar pacote
composer require spatie/laravel-permission

# Publicar migrations
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Executar migrations
php artisan migrate
```

### 2. Configurar Model User

```php
<?php
// app/Models/User.php

use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements FilamentUser
{
    use HasRoles;
    
    // Controla acesso ao painel administrativo
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && 
               ($this->hasRole(['admin', 'editor']) || 
                $this->can('access_panel'));
    }
}
```

### 3. Criar Permissões e Roles

```php
<?php
// database/seeders/RolePermissionSeeder.php

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Limpar cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        // 1. Criar Permissões
        $permissions = [
            // Usuários
            'view_users',
            'create_users', 
            'edit_users',
            'delete_users',
            
            // Posts (exemplo)
            'view_posts',
            'create_posts',
            'edit_posts',
            'delete_posts',
            
            // Sistema
            'access_panel',
            'manage_settings',
        ];
        
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        
        // 2. Criar Roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $editor = Role::firstOrCreate(['name' => 'editor']);  
        $user = Role::firstOrCreate(['name' => 'user']);
        
        // 3. Atribuir Permissões às Roles
        $admin->givePermissionTo($permissions); // Admin: todas
        
        $editor->givePermissionTo([
            'access_panel',
            'view_posts', 'create_posts', 'edit_posts',
            'view_users'
        ]);
        
        $user->givePermissionTo(['access_panel']); // User: apenas painel
        
        // 4. Criar Usuário Admin
        $adminUser = User::firstOrCreate(['email' => 'admin@admin.com'], [
            'name' => 'Administrator',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        
        $adminUser->assignRole('admin');
    }
}
```

### 4. Proteger Resources do Filament

```php
<?php
// app/Filament/Resources/UserResource.php

class UserResource extends Resource
{
    // Controla visibilidade no menu lateral
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::user()?->can('view_users') ?? false;
    }

    // Controles de acesso por ação
    public static function canViewAny(): bool
    {
        return Auth::user()?->can('view_users') ?? false;
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->can('create_users') ?? false;
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->can('edit_users') ?? false;
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->can('delete_users') && 
               $record->id !== Auth::id();
    }

    // Proteger formulário
    public static function form(Form $form): Form
    {
        return $form->schema([
            // Campos básicos sempre visíveis
            TextInput::make('name')->required(),
            TextInput::make('email')->required(),
            
            // Campo sensível - apenas para quem pode gerenciar
            Select::make('roles')
                ->visible(fn() => Auth::user()->can('manage_users'))
                ->relationship('roles', 'name'),
        ]);
    }

    // Proteger ações da tabela
    public static function table(Table $table): Table
    {
        return $table
            ->actions([
                EditAction::make()
                    ->visible(fn() => Auth::user()->can('edit_users')),
                DeleteAction::make()
                    ->visible(fn($record) => 
                        Auth::user()->can('delete_users') && 
                        $record->id !== Auth::id()
                    ),
            ]);
    }
}
```

### 5. Proteger Pages do Resource

```php
<?php
// app/Filament/Resources/UserResource/Pages/ListUsers.php

class ListUsers extends ListRecords
{
    public function mount(): void
    {
        // Bloquear acesso se não tiver permissão
        abort_unless(Auth::user()->can('view_users'), 403);
        parent::mount();
    }
    
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn() => Auth::user()->can('create_users')),
        ];
    }
}
```

---

## 🎨 Personalização Avançada

### 1. Permissões Condicionais

```php
// Apenas admins ou donos podem editar
public static function canEdit($record): bool
{
    $user = Auth::user();
    
    return $user->can('edit_users') || 
           ($user->can('edit_own_posts') && $record->user_id === $user->id);
}
```

### 2. Campos Dinâmicos por Permissão

```php
Forms\Components\Section::make('Configurações Avançadas')
    ->schema([
        Toggle::make('is_featured'),
        Select::make('status'),
    ])
    ->visible(fn() => Auth::user()->hasRole('admin')),
```

### 3. Filtros por Role

```php
// Mostrar apenas próprios registros para users comuns
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    
    if (Auth::user()->hasRole('user')) {
        $query->where('user_id', Auth::id());
    }
    
    return $query;
}
```

### 4. Menu Contextual

```php
// app/Providers/Filament/AdminPanelProvider.php
public function panel(Panel $panel): Panel
{
    return $panel
        ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
            return $builder
                ->group('Administração', [
                    NavigationItem::make('Usuários')
                        ->icon('heroicon-o-users')
                        ->url('/admin/users')
                        ->visible(fn() => Auth::user()->can('view_users')),
                        
                    NavigationItem::make('Configurações')
                        ->icon('heroicon-o-cog')
                        ->url('/admin/settings')  
                        ->visible(fn() => Auth::user()->hasRole('admin')),
                ]);
        });
}
```

---

## 🔧 Comandos Úteis

### Gerenciar Permissões via Tinker

```bash
php artisan tinker

# Criar permissão
Permission::create(['name' => 'manage_reports']);

# Criar role
$role = Role::create(['name' => 'manager']);

# Atribuir permissão à role
$role->givePermissionTo('manage_reports');

# Atribuir role ao usuário
$user = User::find(1);
$user->assignRole('manager');

# Dar permissão diretamente
$user->givePermissionTo('special_access');

# Verificar permissões
$user->can('manage_reports');
$user->hasRole('manager');
$user->getAllPermissions();
```

### Limpar Cache de Permissões

```bash
php artisan permission:cache-reset
php artisan optimize:clear
```

---

## 🏷️ Padrões de Nomenclatura

### Permissões:
- **Formato**: `{ação}_{recurso}` 
- **Exemplos**: `view_users`, `create_posts`, `delete_comments`

### Roles:
- **Formato**: `{função}` (singular, lowercase)
- **Exemplos**: `admin`, `editor`, `manager`, `user`

### Verificações:
- **Resource**: `can{Ação}()` → `canCreate()`, `canEdit()`
- **Model**: `can('permission')` → `$user->can('edit_posts')`

---

## 🚦 Exemplos Práticos

### Exemplo 1: Blog System

```php
// Permissões
'view_posts', 'create_posts', 'edit_posts', 'delete_posts',
'view_comments', 'moderate_comments',
'manage_categories', 'publish_posts'

// Roles  
admin → todas as permissões
editor → view_posts, create_posts, edit_posts, publish_posts
author → view_posts, create_posts, edit_own_posts  
user → view_posts, create_comments
```

### Exemplo 2: E-commerce

```php  
// Permissões
'view_products', 'manage_products', 'view_orders',
'manage_orders', 'view_customers', 'manage_customers',
'view_reports', 'manage_settings'

// Roles
admin → todas
manager → manage_products, manage_orders, view_reports  
support → view_orders, view_customers
customer → view_own_orders
```

---

## 🐛 Debug e Troubleshooting  

### Verificar Estado das Permissões

```php
// Verificar se usuário tem permissão
Auth::user()->can('view_users'); // true/false

// Ver todas as permissões
Auth::user()->getAllPermissions()->pluck('name');

// Ver todas as roles  
Auth::user()->getRoleNames();

// Verificar permissões de uma role
Role::findByName('admin')->permissions->pluck('name');
```

### Logs de Debug

```php
// Adicionar no Resource para debug
public static function canViewAny(): bool
{
    $can = Auth::user()?->can('view_users') ?? false;
    \Log::info('canViewAny check', [
        'user' => Auth::id(),
        'can_view_users' => $can
    ]);
    return $can;
}
```

### Problemas Comuns

| Problema | Causa | Solução |
|----------|-------|---------|
| Menu não aparece | Falta `shouldRegisterNavigation()` | Adicionar verificação |
| 403 em páginas | Falta verificação nas Pages | Adicionar `abort_unless()` |
| Permissões não funcionam | Cache corrompido | `permission:cache-reset` |
| Usuário sem acesso | Role/permissão não atribuída | Verificar no seeder |

---

## 📚 Referências e Recursos

### Documentação Oficial:
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission)
- [Filament Resources](https://filamentphp.com/docs/panels/resources)

### Comandos de Referência:
```bash
# Listar todas as permissões
php artisan tinker
>>> Permission::all()->pluck('name');

# Listar todas as roles
>>> Role::all()->pluck('name');

# Ver usuários por role
>>> User::role('admin')->get()->pluck('name');
```

### Middleware Personalizado:
```php
// app/Http/Middleware/CheckPermission.php
public function handle($request, Closure $next, $permission)
{
    if (!Auth::user()->can($permission)) {
        abort(403, 'Acesso negado');
    }
    return $next($request);
}
```

---

## ✅ Checklist de Implementação

- [ ] Pacote instalado e configurado
- [ ] Model User com trait HasRoles  
- [ ] Seeder criado com permissões e roles
- [ ] Resources protegidos com verificações
- [ ] Pages com controle de acesso
- [ ] Menu contextual implementado  
- [ ] Campos sensíveis protegidos
- [ ] Testes de acesso realizados
- [ ] Cache de permissões limpo
- [ ] Documentação atualizada

---

**💡 Dica Final**: Sempre teste as permissões com diferentes usuários e roles para garantir que o sistema está funcionando corretamente!