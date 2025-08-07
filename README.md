## TECNOLOGIAS
- PHP 8.3
- Laravel 11.0
- MySQL 8.0
- Docker
- Composer
- WSL (Windows Subsystem for Linux)

## INSTALAÇÃO
1. Clone o repositório:
```bash
git clone https://github.com/devmattheusalfaia/laravel.git
```
2. Navegue até o diretório do projeto:
```bash
cd laravel
```
3. Instale as dependências do Composer:
```bash
composer install
```
4. Copie o arquivo `.env.example` para `.env`:
```bash
cp .env.example .env
```
5. Gere a chave de aplicativo:
```bash
php artisan key:generate
```
6. Configurar o laravel Alias sail:
```bash
wsl
pwd
ls -la
chmod +x vendor/bin/sail
alias sail='./vendor/bin/sail'
```
7. Inicie o ambiente de desenvolvimento com Docker aberto:
```bash
sail up -d
```
8. Execute as migrações do banco de dados(se necessário):
```bash
sail artisan migrate
```
9. Acesse o aplicativo no navegador:
```bash
http://localhost
```
10. se precisar reiniciar o ambiente de desenvolvimento:
```bash
sail restart
```

## FILAMENT
1. Acesse o painel administrativo do Filament:
```bash
http://localhost/admin
```
2. Para criar um novo usuário administrador, execute o comando:
```bash
sail artisan make:filament-user
```

## Filament Dev commands
- Para limpar o cache do Filament:
```bash
sail artisan filament:optimize
```
- Para limpar o cache de recursos do Filament:
```bash
sail artisan filament:clear-resources-cache
```

## COMANDOS PARA CRIAÇÃO DE RECURSOS(RESOURCES)
- Criar um novo recurso:
```bash
sail artisan make:resource NomeDoRecurso
```
- Criar um novo recurso com modelo:
```bash
sail artisan make:resource NomeDoRecurso --model=NomeDoModelo
```
- Criar um novo recurso com modelo e tabela:
```bash
sail artisan make:resource NomeDoRecurso --model=NomeDoModelo --table=nome_da_tabela
```
- Criar um novo recurso com modelo, tabela e controlador:
```bash
sail artisan make:resource NomeDoRecurso --model=NomeDoModelo --table=nome_da_tabela --controller
```

## COMANDOS PARA CRIAÇÃO DE PÁGINAS(PAGES)
- Criar uma nova página:
```bash
sail artisan make:page NomeDaPagina
```
- Criar uma nova página com modelo:
```bash
sail artisan make:page NomeDaPagina --model=NomeDoModelo
```
- Criar uma nova página com modelo e tabela:
```bash
sail artisan make:page NomeDaPagina --model=NomeDoModelo --table=nome
_da_tabela
```
- Criar uma nova página com modelo, tabela e controlador:
```bash
sail artisan make:page NomeDaPagina --model=NomeDoModelo --table=nome_da_tabela --controller
```
## COMANDOS PARA CRIAÇÃO DE WIDGETS
- Criar um novo widget:
```bash
sail artisan make:widget NomeDoWidget
```
- Criar um novo widget com modelo:
```bash
sail artisan make:widget NomeDoWidget --model=NomeDoModelo
```
- Criar um novo widget com modelo e tabela:
```bash
sail artisan make:widget NomeDoWidget --model=NomeDoModelo --table=nome_da_tabela
```
- Criar um novo widget com modelo, tabela e controlador:
```bash
sail artisan make:widget NomeDoWidget --model=NomeDoModelo --table=nome_da_tabela --controller
```