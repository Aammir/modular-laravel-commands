# modular-laravel-commands

```sh
php artisan make:command ModularCrudCommand
```

```sh
``php artisan make:command ModularCrudRollbackCommand
```

```sh
``php artisan modular:crud Post
```

```sh
``php artisan modular:crud-rollback Post
```

## What Does This CRUD Generator Create?

This package provides Artisan commands to quickly scaffold and remove CRUD (Create, Read, Update, Delete) functionality for any model in your Laravel application.

### When you run:
```sh
php artisan modular:crud ModelName
```
It will automatically generate:

- **Model**: `app/Models/ModelName.php`
- **Migration**: `database/migrations/xxxx_xx_xx_create_model_names_table.php`
- **Controller**: `app/Http/Controllers/ModelNameController.php`
- **Request Validation**: `app/Http/Requests/StoreModelNameRequest.php`, `UpdateModelNameRequest.php`
- **Resource**: `app/Http/Resources/ModelNameResource.php`
- **Views**: `resources/views/model_names/` (index, create, edit, show, etc.)
- **Routes**: Adds resource routes to `routes/web.php` or `routes/api.php`
- **Factory**: `database/factories/ModelNameFactory.php`
- **Seeder**: `database/seeders/ModelNameSeeder.php`

### To rollback:
```sh
php artisan modular:crud-rollback ModelName
```
This will remove all files and code generated for the specified model.

---

**Note:** Replace `ModelName` with the actual name of your model (e.g., `Post`, `Product`, etc.).
