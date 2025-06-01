<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ModularCrudCommand extends Command
{
    protected $signature = 'modular:crud {name}';
    protected $description = 'Generate a modular CRUD structure for a model in modules folder';

    public function handle()
    {
        $name = Str::studly($this->argument('name'));
        $pluralSnake = Str::snake(Str::plural($name));
        $modulePath = app_path("Modules/{$name}");
        $controllerPath = "{$modulePath}/Controllers";
        $modelPath = "{$modulePath}/Models";
        $policyPath = "{$modulePath}/Policies";
        $factoryPath = "{$modulePath}/Factories";
        $seederPath = "{$modulePath}/Seeders";
        $migrationPath = database_path('migrations');
        $viewsPath = resource_path("views/{$pluralSnake}");

        // 1. Create module directories
        foreach ([$controllerPath, $modelPath, $policyPath, $factoryPath, $seederPath] as $dir) {
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
        }

        // 2. Generate Model
        $tableName = Str::snake(Str::plural($name));
        //$modelStub = "<?php\n\nnamespace App\Modules\\{$name}\\Models;\n\nuse Illuminate\Database\Eloquent\Model;\n\nclass {$name} extends Model\n{\n    //\n}\n";
         $modelStub = "<?php\n\nnamespace App\\Modules\\{$name}\\Models;\n\nuse Illuminate\\Database\\Eloquent\\Model;\n\nclass {$name} extends Model\n{\n    protected \$table = '{$tableName}';\n    protected \$fillable = [];\n}\n";
        File::put("{$modelPath}/{$name}.php", $modelStub);

        // 3. Generate Controller
        $nameLowerPlural = Str::plural(Str::snake(strtolower($name)));
        $controllerStub = "<?php\n\nnamespace App\\Modules\\{$name}\\Controllers;\n\nuse App\\Http\\Controllers\\Controller;\n\nuse App\\Modules\\{$name}\\Models\\{$name};\nuse Illuminate\\Http\\Request;\n\nclass {$name}Controller extends Controller\n{\n    public function index()\n    {\n        \$items = {$name}::all();\n        return view('{$nameLowerPlural}.index', compact('items'));\n    }\n\n    public function create()\n    {\n        return view('{$nameLowerPlural}.create');\n    }\n\n    public function store(Request \$request)\n    {\n        // Store logic here\n    }\n\n    public function show({$nameLowerPlural} \$item)\n    {\n        return view('{$nameLowerPlural}.show', compact('item'));\n    }\n\n    public function edit({$nameLowerPlural} \$item)\n    {\n        return view('{$nameLowerPlural}.edit', compact('item'));\n    }\n\n    public function update(Request \$request, {$nameLowerPlural} \$item)\n    {\n        // Update logic here\n    }\n\n    public function destroy({$name} \$item)\n    {\n        \$item->delete();\n        return redirect()->route('{$nameLowerPlural}.index');\n    }\n}\n";
        File::put("{$controllerPath}/{$name}Controller.php", $controllerStub);

        // 4. Generate Policy
        $policyStub = "<?php\n\nnamespace App\Modules\\{$name}\\Policies;\n\nuse App\Modules\\{$name}\\Models\\{$name};\nuse App\Models\User;\n\nclass {$name}Policy\n{\n    // Policy methods here\n}\n";
        File::put("{$policyPath}/{$name}Policy.php", $policyStub);

        // 5. Generate Factory
        $factoryStub = "<?php\n\nnamespace App\Modules\\{$name}\\Factories;\n\nuse App\Modules\\{$name}\\Models\\{$name};\nuse Illuminate\Database\Eloquent\Factories\Factory;\n\nclass {$name}Factory extends Factory\n{\n    protected \$model = {$name}::class;\n\n    public function definition()\n    {\n        return [\n            //\n        ];\n    }\n}\n";
        File::put("{$factoryPath}/{$name}Factory.php", $factoryStub);

        // 6. Generate Seeder
        $seederStub = "<?php\n\nnamespace App\Modules\\{$name}\\Seeders;\n\nuse Illuminate\Database\Seeder;\n\nclass {$name}Seeder extends Seeder\n{\n    public function run()\n    {\n        //\n    }\n}\n";
        File::put("{$seederPath}/{$name}Seeder.php", $seederStub);

        // 7. Generate Migration (basic stub, you may want to improve this)
        $migrationFile = date('Y_m_d_His') . '_create_' . $pluralSnake . '_table.php';
        $migrationStub = "<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\nreturn new class extends Migration {\n    public function up()\n    {\n        Schema::create('{$pluralSnake}', function (Blueprint \$table) {\n            \$table->id();\n            \$table->timestamps();\n        });\n    }\n\n    public function down()\n    {\n        Schema::dropIfExists('{$pluralSnake}');\n    }\n};\n";
        File::put("{$migrationPath}/{$migrationFile}", $migrationStub);

        // 8. Generate CRUD Blade views
        if (!File::exists($viewsPath)) {
            File::makeDirectory($viewsPath, 0755, true);
        }
        foreach (['index', 'create', 'edit', 'show'] as $view) {
            File::put("{$viewsPath}/{$view}.blade.php", "<!-- {$view} view for {$name} -->\n");
             $this->info("Created view: {$view}.blade.php");
        }

        //////////////////
        // $views = ['index.blade.php' => '<x-layouts.app><div class="container"><h1>{{ Str::plural(\'' . $name . '\') }}</h1><a href="{{ route(\'' . Str::lower($name) .'.create\') }}" class="btn btn-primary">Create New</a><div>@foreach ($items as $item)<p>{{ $item }}</p>@endforeach</div></div></x-layouts.app>','create.blade.php' => '<x-layouts.app><div class="container"><h1>Create New ' . $name . '</h1><form method="POST" action="{{ route(\'' . Str::lower($name) . '.store\') }}">@csrf<!-- Add form fields here --></form></div></x-layouts.app>','show.blade.php' => '<x-layouts.app><div class="container"><h1>{{ $item }}</h1><!-- Add item details here --></div></x-layouts.app>','edit.blade.php' => '<x-layouts.app><div class="container"><h1>Edit ' . $name . '</h1><form method="POST" action="{{ route(\'' . Str::lower($name) . '.update\', $item) }}">@csrf @method(\'PUT\')<!-- Add form fields here --></form></div></x-layouts.app>',];
        // foreach ($views as $file => $content) {
        //     File::put("{$viewsPath}/{$file}", $content);
        //     $this->info("Created view: {$file}");
        // }
        //////////////////
        // 9. Append resource route to web.php
        $webRoutesPath = base_path('routes/web.php');
        $route = "\n// Routes for {$name}\nRoute::resource('{$pluralSnake}', App\Modules\\{$name}\Controllers\\{$name}Controller::class);";
        File::append($webRoutesPath, $route);

        $this->info("Modular CRUD for {$name} generated successfully!");
    }
}