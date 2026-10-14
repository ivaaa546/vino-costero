# Vino Costero

Sistema web para gestionar parcelas, siembras, controles agronomicos, producciones de vino, fases de produccion y catas.

## Tecnologias

- Laravel 11
- PHP 8.3
- PostgreSQL 16
- Eloquent ORM
- Blade
- Docker Compose
- Composer
- Tailwind CSS 3
- Vite
- PHP integrado (`php artisan serve`)

No se utiliza Apache. PHP y PostgreSQL se ejecutan dentro de contenedores Docker.

## Requisitos

- Docker Desktop instalado y ejecutandose.
- Docker Compose disponible.

No es necesario instalar PHP, Composer, Apache ni PostgreSQL en el equipo local.

## Ejecutar el proyecto

Desde esta carpeta:

```bash
docker-compose up -d --build
```

La aplicacion queda disponible en:

```text
http://localhost:8081/login
```

Verificar los contenedores:

```bash
docker-compose ps
```

Deberian aparecer estos servicios:

- `vino_costero-app-1`
- `vino_costero-postgres-1`
- `vino_costero-assets-1`

## Credenciales de desarrollo

```text
Correo: jacs@gmail.com
Contrasena: 12345678
```

Tambien existen usuarios de prueba para los roles de Enologo, Ingeniero Agronomo y Operador. Todos usan la contrasena `12345678`.

## Base de datos

PostgreSQL se publica en:

```text
Host: 127.0.0.1
Puerto: 5432
Base de datos: vino_costero
Usuario: vino
Contrasena: vino_secret
```

El volumen persistente se llama `vino_costero_vino_costero_postgres`.

Laravel usa migraciones y seeders ubicados en:

```text
database/migrations/
database/seeders/DatabaseSeeder.php
```

Ejecutar migraciones:

```bash
docker-compose exec app php artisan migrate
```

Ejecutar seeders:

```bash
docker-compose exec app php artisan db:seed
```

Recrear completamente la base de datos, eliminando los datos actuales:

```bash
docker-compose exec app php artisan migrate:fresh --seed
```

Usar el ultimo comando solo cuando se quiera borrar la informacion existente.

## Estructura principal

```text
app/
  Http/Controllers/       Controladores web y API
  Http/Middleware/        Middleware de roles
  Models/
    Auth/                 Usuario y roles
    Agronomia/            Parcelas, siembras y controles
    Enologia/             Produccion, fases y catas
    Support/              Modelo base compartido

database/
  migrations/             Estructura de PostgreSQL
  seeders/                Datos iniciales

resources/views/           Vistas Blade
routes/web.php             Rutas web
routes/api.php             Rutas REST y compatibilidad API
public/index.php           Entrada publica de Laravel
Dockerfile                 Imagen PHP 8.3
docker-compose.yml         Servicios PHP y PostgreSQL
```

## Desarrollo

Los archivos del proyecto estan montados dentro del contenedor. Para cambios normales en PHP, Blade, CSS o JavaScript:

1. Editar y guardar con `Ctrl + S`.
2. Recargar el navegador.

El servicio `assets` ejecuta Vite en modo watch y recompila Tailwind automaticamente cuando se guardan cambios en `resources/`.

Limpiar caches de Laravel si es necesario:

```bash
docker-compose exec app php artisan optimize:clear
```

Si cambia `composer.json`:

```bash
docker-compose exec app composer install
```

Si cambia el `Dockerfile` o una extension PHP:

```bash
docker-compose up -d --build app
```

Si cambia `package.json`:

```bash
docker-compose up -d --force-recreate assets
```

Ver logs de la aplicacion:

```bash
docker-compose logs -f app
```

## API

Las APIs se definen en:

```text
routes/api.php
```

La logica esta en:

```text
app/Http/Controllers/ApiResourceController.php
app/Http/Controllers/AuthController.php
```

Ejemplo de login:

```bash
curl -c /tmp/vino_cookies.txt -X POST http://localhost:8081/api/login \
  -H 'Accept: application/json' \
  -d 'correo=jacs@gmail.com&contrasena=12345678'
```

## Detener el proyecto

Detener contenedores sin borrar datos:

```bash
docker-compose down
```

No ejecutar `docker-compose down -v` salvo que se quieran eliminar tambien los datos persistidos de PostgreSQL.
