<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class ModularCrudRollbackCommand extends Command
{
    protected $signature = 'modular:crud-rollback {name}';
    protected $description = 'Rollback a modular CRUD structure for a model in modules folder';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $pluralSnake = Str::snake(Str::plural($name));
        $modulePath = app_path("Modules/{$name}");

        // 1. Remove the entire module directory
        if (File::exists($modulePath)) {
            File::deleteDirectory($modulePath);
            $this->info("Deleted module directory: {$modulePath}");
        } else {
            $this->warn("Module directory not found: {$modulePath}");
        }

        // 2. Rollback migration(s) for this module BEFORE deleting migration files
        $migrationFiles = File::files(database_path('migrations'));
        foreach ($migrationFiles as $file) {
            if (Str::contains($file->getFilename(), Str::snake($name))) {
                // Try to rollback this migration if possible
                // This will only work if the migration is the latest batch
                Artisan::call('migrate:rollback', [
                    '--path' => 'database/migrations/' . $file->getFilename(),
                    '--force' => true,
                ]);
            }
        }

        // 3. Remove migration(s) for this module
        foreach ($migrationFiles as $file) {
            if (Str::contains($file->getFilename(), Str::snake($name))) {
                File::delete($file->getPathname());
                $this->info("Deleted migration: {$file->getFilename()}");
            }else{
                $this->warn("Migration file not found for: {$file->getFilename()}");
            }
        }

        // 4. Remove views in resources/views/{plural_snake} 
        $viewsPath = resource_path("views/{$pluralSnake}");
        if (File::exists($viewsPath)) {
            File::deleteDirectory($viewsPath);
            $this->info("Deleted views directory: {$viewsPath}");
        }else {
            $this->warn("Views directory not found: {$viewsPath}");
        }

        // 5. Remove resource route from web.php
        $webRoutesPath = base_path('routes/web.php');
        if (File::exists($webRoutesPath)) {
            $content = File::get($webRoutesPath);
            $pattern = "/\n?\/\/ Routes for {$name}\nRoute::resource\('{$pluralSnake}', App\\\\Modules\\\\{$name}\\\\Controllers\\\\{$name}Controller::class\);\n?/i";
            $content = preg_replace($pattern, '', $content);
            File::put($webRoutesPath, $content);
            $this->info("Removed routes from web.php");
        }else {
            $this->warn("web.php not found: {$webRoutesPath}");
        }
        

        $this->info("Modular CRUD for {$name} rolled back successfully!");
    }
}