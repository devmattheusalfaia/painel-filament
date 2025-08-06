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
8. Acesse o aplicativo no navegador:
```
    http://localhost
```