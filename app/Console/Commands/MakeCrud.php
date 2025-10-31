<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeCrud extends Command
{

    protected $signature = 'make:crud {modelName : Nome do modelo (ex: Post)} {fields?* : Campos no formato nome:"Nome da Coluna":tipo (ex: title:"Título":string)}';

    protected $description = 'Gera um CRUD completo com modelo, controlador, views Vue, migração, rotas e item de menu no sidebar.';

    public function handle()
    {
        $modelNameArg = explode(':', $this->argument('modelName'));
        $model = $modelNameArg[0];
        $modelTitle = count($modelNameArg) > 1 ? trim($modelNameArg[1], "'\"") : $model;
        $controller = $model . 'Controller';
        $viewFolder = ucfirst($model);
        $routePrefix = strtolower($model);
        $modelLower = strtolower($model);
        $modelPluralTitle = Str::plural($modelTitle);
        $modelPluralLower = strtolower($modelPluralTitle);

        $fields = $this->parseFields($this->argument('fields'));

        $this->createModel($model, $fields);
        $this->createController($controller, $model, $viewFolder, $routePrefix, $modelLower, $modelTitle, $modelPluralTitle, $fields);
        $this->createViews($viewFolder, $routePrefix, $model, $modelLower, $modelTitle, $modelPluralTitle, $modelPluralLower, $fields);
        $this->appendControllerImport($controller);
        $this->appendRoutes($controller, $routePrefix);
        $this->appendMenuItem($modelPluralTitle, $routePrefix);
        $this->createMigration($model, $fields);

        $this->informativo($model, $controller, $viewFolder, $fields);

    }

    protected function parseFields($fieldsArg)
    {
        $fields = [];
        foreach ($fieldsArg as $fieldArg) {
            // Validate the field format
            $parts = explode(':', $fieldArg);
            if (count($parts) !== 3) {
                throw new \InvalidArgumentException("Invalid field format: {$fieldArg}. Expected format is 'name:label:type'.");
            }

            [$name, $label, $type] = $parts;
            $type = trim($type, "'\""); // Remover aspas mas manter case

            // Validação e mapeamento de tipos (case insensitive)
            $validTypes = [
                'string', 'text', 'integer', 'biginteger', 'float', 'double',
                'decimal', 'boolean', 'date', 'datetime', 'timestamp', 'json', 'email', 'moeda', 'file', 'files'
            ];

            if (!in_array(strtolower($type), $validTypes)) {
                $this->warn("Tipo '{$type}' não é válido. Tipos suportados: " . implode(', ', $validTypes));
                continue;
            }

            // Mapear tipos para os nomes corretos do Laravel
            $mappedType = match (strtolower($type)) {
                'biginteger' => 'bigInteger',
                'moeda' => 'float',
                default => strtolower($type)
            };

            $fields[] = [
                'name' => $name,
                'label' => trim($label, "'\""),
                'type' => $mappedType,
                'is_foreign' => str_starts_with($name, 'id_'), // Detecta se é chave estrangeira
                'related_model' => $this->getRelatedModelName($name), // Nome do modelo relacionado
            ];
        }
        return $fields;
    }

    protected function getRelatedModelName($fieldName)
    {
        // Extrai o nome do modelo relacionado a partir do campo id_
        return ucfirst(Str::camel(str_replace('id_', '', $fieldName)));
    }

    protected function createModel($model, $fields)
    {
        $fillable = implode(', ', array_map(fn($f) => "'{$f['name']}'", $fields));
        $casts = implode(', ', array_filter(array_map(fn($f) => match ($f['type']) {
            'files' => "'{$f['name']}' => 'array'",
            'float', 'double', 'decimal', 'moeda' => "'{$f['name']}' => 'float'",
            'date', 'datetime', 'timestamp' => "'{$f['name']}' => 'date'",
            default => null
        }, $fields)));

        $relationships = implode("\n    ", array_map(function ($f) {
            if ($f['is_foreign']) {
                $relatedModel = $f['related_model'];
                return "// TODO: Implement relationship for {$f['name']}\n    public function {$relatedModel}()\n    {\n        return \$this->belongsTo(\App\Models\{$relatedModel}::class);\n    }";
            }
            return '';
        }, $fields));

        $stub = File::get(base_path('stubs/crud.model.stub'));
        $stub = str_replace(['{{model}}', '{{fillable}}', '{{casts}}', '{{relationships}}'], [$model, $fillable, $casts, $relationships], $stub);
        File::put(app_path("Models/{$model}.php"), $stub);
    }

    protected function createController($controller, $model, $viewFolder, $routePrefix, $modelLower, $modelTitle, $modelPluralTitle, $fields)
    {
        $validationRules = implode("\n            ", array_map(fn($f) => "'{$f['name']}' => 'required|" . match ($f['type']) {
            'integer', 'bigInteger' => 'integer',
            'float', 'double', 'decimal', 'moeda' => 'numeric',
            'email' => 'email',
            'date', 'datetime', 'timestamp' => 'date',
            'boolean' => 'boolean',
            'json' => 'json',
            'file' => 'file',
            'files' => 'array',
            default => 'string'
        } . "|max:255',", $fields));

        $dropdownData = implode("\n        ", array_map(
            fn($f) => $f['is_foreign'] ? "\${$f['name']}Options = \\App\\Models\\{$f['related_model']}::where('deleted', 0)->orderBy('id', 'desc')->get()->map(function (\$item) {
                return [
                    'value' => \$item->id,
                    'label' => \$item->nome // TODO: Ajustar o campo 'nome' conforme o modelo relacionado
                ];
            });" : '',
            $fields
        ));

        $storeMethod = <<<EOT
    public function store(Request \$request)
    {
        \$data = \$request->validate([
            {$validationRules}
        ]);

        // Handle file uploads if necessary
        if (\$request->hasFile('arquivo')) {
            \$data['arquivo'] = \$request->file('arquivo')->store('uploads', 'public');
        }

        if (\$request->hasFile('arquivos')) {
            \$data['arquivos'] = array_map(
                fn(\$file) => \$file->store('uploads', 'public'),
                \$request->file('arquivos')
            );
        }

        \$model = {$model}::create(\$data);

        return redirect()->route('{$routePrefix}.index')->with('success', '{$modelTitle} criado com sucesso!');
    }
EOT;

        $fileHandling = ""; // Handle file uploads if necessary

        $updateMethod = <<<EOT
    public function update(Request \$request, {$model} \${$modelLower})
    {
        \$data = \$request->validate([
            {$validationRules}
        ]);

        // Handle file uploads if necessary
        if (\$request->hasFile('arquivo')) {
            \$data['arquivo'] = \$request->file('arquivo')->store('uploads', 'public');
        }

        if (\$request->hasFile('arquivos')) {
            \$data['arquivos'] = array_map(
                fn(\$file) => \$file->store('uploads', 'public'),
                \$request->file('arquivos')
            );
        }

        \${$modelLower}->update(\$data);

        return redirect()->route('{$routePrefix}.index')->with('success', '{$modelTitle} atualizado com sucesso!');
    }
EOT;

        $createMethod = <<<EOT
    public function create()
    {
        {$dropdownData}

        return inertia('{$viewFolder}/create', [
            'sidebarNavItems' => \$this->getSidebarNavItems()
            {$this->generateDropdownProps($fields)}
        ]);
    }
EOT;

        $editMethod = <<<EOT
    public function edit({$model} \${$modelLower})
    {
        if (\${$modelLower}->deleted) {
            return redirect()->route('{$routePrefix}.index')->with('error', '{$modelTitle} excluído.');
        }

        {$dropdownData}

        return inertia('{$viewFolder}/create', [
            'item' => \${$modelLower}->toArray(),
            'sidebarNavItems' => \$this->getSidebarNavItems()
            {$this->generateDropdownProps($fields)}
        ]);
    }
EOT;

        $stub = File::get(base_path('stubs/crud.controller.stub'));
        $stub = str_replace(
            ['{{model}}', '{{controller}}', '{{viewFolder}}', '{{routePrefix}}', '{{modelLower}}', '{{modelTitle}}', '{{modelPluralTitle}}', '{{validationRules}}', '{{createMethod}}', '{{editMethod}}', '{{storeMethod}}', '{{updateMethod}}'],
            [$model, $controller, $viewFolder, $routePrefix, $modelLower, $modelTitle, $modelPluralTitle, $validationRules, $createMethod, $editMethod, $storeMethod, $updateMethod],
            $stub
        );
        File::put(app_path("Http/Controllers/{$controller}.php"), $stub);
    }

    private function generateDropdownProps($fields)
    {
        return implode("\n            ", array_map(
            fn($f) => $f['is_foreign'] ? ",'{$f['name']}Options' => \${$f['name']}Options" : '',
            $fields
        ));
    }

    protected function createViews($viewFolder, $routePrefix, $model, $modelLower, $modelTitle, $modelPluralTitle, $modelPluralLower, $fields)
    {
        $viewPath = resource_path("js/pages/{$viewFolder}");
        File::ensureDirectoryExists($viewPath);

        $propFields = implode('; ', array_map(fn($f) => "{$f['name']}: " . match ($f['type']) {
            'boolean' => 'boolean',
            'integer', 'bigInteger', 'float', 'double', 'decimal' => 'number',
            default => 'string',
        }, $fields));

        $formFields = implode(",\n    ", array_map(fn($f) => "{$f['name']}: props.item?.{$f['name']}.toString() || " . match ($f['type']) {
            'boolean' => 'false',
            'integer', 'bigInteger', 'float', 'double', 'decimal' => '0',
            default => "''",
        }, $fields));

        // Refatoração 1: Remover a lógica hardcoded de dropdowns
        // A lógica de props de dropdowns já é gerada no Controller,
        // e o nome da prop segue o padrão 'id_campoOptions'.
        $dropdownOptions = ''; // Não é mais necessário gerar esta string

        $formInputs = implode("\n                ", array_map(
            fn($f) => match (true) {
                // Adição da lógica para campos de chave estrangeira (id_)
                $f['is_foreign'] => $this->generateSelectComponent($f),

                $f['type'] === 'boolean' => "<div class=\"flex items-center space-x-2\">\n                    <Checkbox id=\"{$f['name']}\" v-model=\"form.{$f['name']}\" />\n                    <Label for=\"{$f['name']}\">{$f['label']}</Label>\n                </div>",
                $f['type'] === 'text' => "<div>\n                    <Label for=\"{$f['name']}\">{$f['label']}</Label>\n                    <Textarea id=\"{$f['name']}\" v-model=\"form.{$f['name']}\" placeholder=\"Digite {$f['label']}\" rows=\"4\" />\n                </div>",
                $f['type'] === 'date' || $f['type'] === 'datetime' || $f['type'] === 'timestamp' => "<div>\n                    <Label for=\"{$f['name']}\">{$f['label']}</Label>\n                    <Input id=\"{$f['name']}\" v-model=\"form.{$f['name']}\" type=\"" . ($f['type'] === 'date' ? 'date' : 'datetime-local') . "\" />\n                </div>",
                $f['type'] === 'json' => "<div>\n                    <Label for=\"{$f['name']}\">{$f['label']}</Label>\n                    <Textarea id=\"{$f['name']}\" v-model=\"form.{$f['name']}\" placeholder='Exemplo: {\"key\": \"value\"}' rows=\"4\" />\n                </div>",
                $f['type'] === 'email' => "<div>\n                    <Label for=\"{$f['name']}\">{$f['label']}</Label>\n                    <Input id=\"{$f['name']}\" v-model=\"form.{$f['name']}\" type=\"email\" placeholder=\"Digite {$f['label']}\" />\n                </div>",
                $f['type'] === 'integer' || $f['type'] === 'bigInteger' => "<div>\n                    <Label for=\"{$f['name']}\">{$f['label']}</Label>\n                    <Input id=\"{$f['name']}\" v-model.number=\"form.{$f['name']}\" type=\"number\" step=\"1\" placeholder=\"Digite {$f['label']}\" />\n                </div>",
                $f['type'] === 'float' || $f['type'] === 'double' || $f['type'] === 'decimal' => "<div>\n                    <Label for=\"{$f['name']}\">{$f['label']}</Label>\n                    <Input id=\"{$f['name']}\" v-model.number=\"form.{$f['name']}\" type=\"number\" step=\"0.01\" placeholder=\"Digite {$f['label']}\" />\n                </div>",
                $f['type'] === 'moeda' => "<div>\n                    <Label for=\"{$f['name']}\">{$f['label']}</Label>\n                    <Input id=\"{$f['name']}\" v-model.number=\"form.{$f['name']}\" type=\"text\" placeholder=\"Digite {$f['label']}\" @input=\"form.{$f['name']} = parseFloat(form.{$f['name']}.replace(/[R$\\s,]/g, '').replace('.', '').replace(',', '.'))\" />\n                </div>",
                $f['type'] === 'file' => "<div>\n                    <Label for=\"{$f['name']}\">{$f['label']}</Label>\n                    <Input id=\"{$f['name']}\" v-model=\"form.{$f['name']}\" type=\"file\" />\n                </div>",
                $f['type'] === 'files' => "<div>\n                    <Label for=\"{$f['name']}\">{$f['label']}</Label>\n                    <Input id=\"{$f['name']}\" v-model=\"form.{$f['name']}\" type=\"file\" multiple />\n                </div>",
                default => "<div>\n                    <Label for=\"{$f['name']}\">{$f['label']}</Label>\n                    <Input id=\"{$f['name']}\" v-model=\"form.{$f['name']}\" type=\"text\" placeholder=\"Digite {$f['label']}\" />\n                </div>",
            },
            $fields
        ));

        $createStub = File::get(base_path('stubs/crud.create.vue.stub'));

        $selectImports = "import {\n" .
            "  Select,\n" .
            "  SelectTrigger,\n" .
            "  SelectValue,\n" .
            "  SelectContent,\n" .
            "  SelectItem\n" .
            "} from '@/components/ui/select';\n";

        // Refatoração 2: Adicionar as props de dropdowns ao defineProps
        $dropdownProps = implode("\n    ", array_map(
            fn($f) => $f['is_foreign'] ? "{$f['name']}Options: { value: number; label: string }[];" : '',
            $fields
        ));

    //     $createStub = str_replace(
    //         '<script setup>',
    //         "<script setup>\n{$selectImports}\n\nconst props = defineProps<{\n    item?: Record<string, any>;\n    sidebarNavItems: { title: string; href: string }[];\n    // TODO: Ajustar a prop 'usuarios' para ser dinâmica conforme o model relacionado
    // usuarios: { id: number; name: string }[];\n    {$dropdownProps}\n}>();\n\n", // Removido $dropdownOptions
    //         $createStub
    //     );

        $createStub = str_replace(
            ['{{modelPluralTitle}}', '{{routePrefix}}', '{{modelPluralLower}}', '{{modelTitle}}', '{{modelLower}}', '{{propFields}}', '{{formFields}}', '{{formInputs}}', '{{dropdownProps}}'],
            [$modelPluralTitle, $routePrefix, $modelPluralLower, $modelTitle, $modelLower, $propFields, $formFields, $formInputs, $dropdownProps],
            $createStub
        );

        File::put("{$viewPath}/create.vue", $createStub);

        $selectImports = "import {\n" .
            "  Select,\n" .
            "  SelectTrigger,\n" .
            "  SelectValue,\n" .
            "  SelectContent,\n" .
            "  SelectItem\n" .
            "} from '@/components/ui/select';\n";

        $createStub = str_replace(
            '<script setup>',
            "<script setup>\n{$selectImports}\n\nconst props = defineProps<{\n    item?: Record<string, any>;\n    sidebarNavItems: { title: string; href: string }[];\n    usuarios: { id: number; name: string }[];\n}>();\n\n{$dropdownOptions}",
            $createStub
        );

        $createStub = str_replace(
            ['{{modelPluralTitle}}', '{{routePrefix}}', '{{modelPluralLower}}', '{{modelTitle}}', '{{modelLower}}', '{{propFields}}', '{{formFields}}', '{{formInputs}}'],
            [$modelPluralTitle, $routePrefix, $modelPluralLower, $modelTitle, $modelLower, $propFields, $formFields, $formInputs],
            $createStub
        );

        File::put("{$viewPath}/create.vue", $createStub);

        // Processar os campos para a tabela
        $tableHeaders = implode("\n                            ", array_map(
            fn($f) => "<TableHead class=\"cursor-pointer\" @click=\"toggleSort('{$f['name']}')\">{$f['label']}<span v-if=\"sortColumn === '{$f['name']}'\" class=\"ml-2\">{{ sortDirection === 'asc' ? '↑' : '↓' }}</span></TableHead>",
            $fields
        ));

        $tableCells = implode("\n                            ", array_map(
            fn($f) => "<TableCell>{{ item.{$f['name']} }}</TableCell>",
            $fields
        ));

        $filterConditions = implode(' || ', array_map(
            fn($f) => "(item.{$f['name']} || '').toString().toLowerCase().includes(query)",
            $fields
        ));

        $indexStub = File::get(base_path('stubs/crud.index.vue.stub'));
        $indexStub = str_replace(
            ['{{modelPluralTitle}}', '{{routePrefix}}', '{{modelPluralLower}}', '{{modelTitle}}', '{{modelLower}}', '{{tableHeaders}}', '{{tableCells}}', '{{filterConditions}}', '{{propFields}}'],
            [$modelPluralTitle, $routePrefix, $modelPluralLower, $modelTitle, $modelLower, $tableHeaders, $tableCells, $filterConditions, $propFields],
            $indexStub
        );

        File::put("{$viewPath}/index.vue", $indexStub);
    }

    /**
     * Gera o componente Select (dropdown) para campos de chave estrangeira.
     * @param array $field
     * @return string
     */
    private function generateSelectComponent(array $field): string
    {
        $propName = "{$field['name']}Options";
        $label = $field['label'];
        $fieldName = $field['name'];

        return <<<VUE
<div>
    <div>
                    <Label for="{$fieldName}">{$label}</Label>
                    <Select v-model="form.{$fieldName}">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Selecione um {$label}" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="option in (props.{$propName} || [])" :key="option.value" :value="option.value.toString()">
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
</div>
VUE;
    }

    protected function appendControllerImport($controller)
    {
        $webPath = base_path('routes/web.php');
        $webContent = File::get($webPath);
        $importStub = File::get(base_path('stubs/crud.controller.import.stub'));
        $import = str_replace('{{controller}}', $controller, $importStub);

        if (!Str::contains($webContent, $import)) {
            $controllerSection = "// Controllers\n";
            $pos = strpos($webContent, $controllerSection);
            if ($pos !== false) {
                $insertPos = $pos + strlen($controllerSection);
                $webContent = substr($webContent, 0, $insertPos) . $import . "\n" . substr($webContent, $insertPos);
                File::put($webPath, $webContent);
            } else {
                $this->warn("Seção '// Controllers' não encontrada em web.php. Adicione-a manualmente e tente novamente.");
            }
        }
    }

    protected function appendRoutes($controller, $routePrefix)
    {
        $webPath = base_path('routes/web.php');
        $webContent = File::get($webPath);
        $routeStub = File::get(base_path('stubs/crud.routes.stub'));
        $routes = str_replace(['{{controller}}', '{{routePrefix}}'], [$controller, $routePrefix], $routeStub);

        if (!Str::contains($webContent, $routes)) {
            $routeSection = "// Rotas\n";
            $pos = strpos($webContent, $routeSection);
            if ($pos !== false) {
                $insertPos = $pos + strlen($routeSection);
                $webContent = substr($webContent, 0, $insertPos) . $routes . "\n" . substr($webContent, $insertPos);
                File::put($webPath, $webContent);
            } else {
                $this->warn("Seção '// Rotas' não encontrada em web.php. Adicione-a manualmente e tente novamente.");
            }
        }
    }

    protected function appendMenuItem($modelPluralTitle, $routePrefix)
    {
        $sidebarPath = resource_path('js/components/AppSidebar.vue');
        $sidebarContent = File::get($sidebarPath);
        $menuStub = File::get(base_path('stubs/crud.menu.item.stub'));
        $menuItem = str_replace(
            ['{{modelPluralTitle}}', '{{routePrefix}}'],
            [$modelPluralTitle, $routePrefix],
            $menuStub
        );

        if (!Str::contains($sidebarContent, $menuItem)) {
            $menuSection = "// Novos Itens do Menu\n";
            $pos = strpos($sidebarContent, $menuSection);
            if ($pos !== false) {
            // Adiciona o menuItem acima do comentário
            $webContent = substr($sidebarContent, 0, $pos) . $menuItem . "\n" . substr($sidebarContent, $pos);
            File::put($sidebarPath, $webContent);
            } else {
            $this->warn("Seção '// Novos Itens do Menu' não encontrada em AppSidebar.vue. Adicione-a manualmente e tente novamente.");
            }
        }
    }

    protected function createMigration($model, $fields)
    {
        $tableName = strtolower(Str::plural($model));
        $migrationName = 'create_' . $tableName . '_table';
        $migrationPath = database_path('migrations');
        $migrationFileName = date('Y_m_d_His') . '_' . $migrationName . '.php';
        $migrationFilePath = $migrationPath . '/' . $migrationFileName;

        if (!File::exists($migrationFilePath)) {
            File::ensureDirectoryExists($migrationPath);
            $stub = File::get(base_path('stubs/crud.migration.stub'));

            $columns = implode("\n            ", array_map(function ($f) {
                // Se for chave estrangeira, usa a sintaxe foreignId()->constrained()
                if ($f['is_foreign']) {
                    $relatedTable = strtolower(Str::plural($f['related_model']));
                    // foreignId() assume que o nome da coluna é 'nome_do_campo'
                    // constrained() assume que a tabela relacionada é o plural do nome do modelo (se não for passado)
                    // Usamos constrained($relatedTable) para garantir a tabela correta
                    // Usamos cascadeOnDelete() para replicar a lógica onDelete('cascade')
                    return "\$table->foreignId('{$f['name']}')"
                        . "\n                ->constrained('{$relatedTable}')"
                        . "\n                ->cascadeOnDelete();";
                }

                // Para os demais campos, usa o mapeamento de tipos
                $columnType = match (true) {
                    $f['type'] === 'email' => 'string',
                    $f['type'] === 'moeda' => 'float', // Sugestão: usar 'decimal' para precisão
                    $f['type'] === 'file' => 'string',
                    $f['type'] === 'files' => 'json',
                    default => $f['type']
                };

                return "\$table->{$columnType}('{$f['name']}');";

            }, $fields));

            $columns .= "\n            \$table->boolean('deleted')->default(false);";
            $columns .= "\n            \$table->timestamps();";
            $stub = str_replace(['{{model}}', '{{table}}', '{{columns}}'], [$model, $tableName, $columns], $stub);
            File::put($migrationFilePath, $stub);
            $this->info("Migração criada: {$migrationFileName}");
        } else {
            $this->warn("Migração já existe: {$migrationFileName}");
        }
    }

    protected function informativo($model, $controller, $viewFolder, $fields)
    {
        $this->info("✅ CRUD para '{$model}' gerado com sucesso!");

        $this->info("📄 Arquivos criados:");
        $this->info("  • Modelo: app/Models/{$model}.php");
        $this->info("  • Controlador: app/Http/Controllers/{$controller}.php");
        $this->info("  • View de criação: resource/js/pages/{$viewFolder}/create.vue");
        $this->info("  • View de listagem: resource/js/pages/{$viewFolder}/index.vue");
        $this->info("  • Migração: database/migrations/" . date('Y_m_d_His') . "_create_" . strtolower(Str::plural($model)) . "_table.php");
        $this->info("  • Rotas adicionadas em: routes/web.php");
        $this->info("  • Item de menu adicionado em: resource/js/components/AppSidebar.vue");

        $this->info("\n📝 TODOs:");
        foreach ($fields as $field) {
            if ($field['is_foreign']) {
                $this->info("  • Configurar dropdown para o campo '{$field['name']}' no controlador e vue.");
                $this->warn("⚠️  Certifique-se de que o modelo relacionado '{$field['related_model']}' existe e está configurado corretamente.");
            }
        }

        $this->info("\n🎉 Pronto! Verifique os arquivos e ajuste conforme necessário.");
        $this->info("\nCriado por: Nicolas Slujalkovsky");
        $this->info("Starter Kit Laravel 12 + Vue - Feito para acelerar seu desenvolvimento!");
        $this->info("Conecte-se comigo: linkedin.com/in/nicolas-slujalkovsky");
    }
}
