<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissoes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->integer('nivel');
            $table->text('descricao');
            $table->boolean('ativo');
            $table->boolean('deleted')->default(false);
            $table->timestamps();
        });

        // Insert a default user
        DB::table('permissoes')->insert([
            'nome' => 'System',
            'nivel' => 0,
            'descricao' => 'Acesso total ao sistema e sem restrições',
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // Insert a default user
        DB::table('permissoes')->insert([
            'nome' => 'Administrador',
            'nivel' => 1,
            'descricao' => 'Acesso total ao modulos do sistema',
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // Insert a default user
        DB::table('permissoes')->insert([
            'nome' => 'Colaborador',
            'nivel' => 2,
            'descricao' => 'Acesso aos moodulos especificos para colaboradores',
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // Insert a default user
        DB::table('permissoes')->insert([
            'nome' => 'Externo',
            'nivel' => 3,
            'descricao' => 'Acesso apenas a visualização de dados',
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // Insert a default user
        DB::table('permissoes')->insert([
            'nome' => 'Visitante',
            'nivel' => 99,
            'descricao' => 'Acesso apenas a visualização de dados',
            'ativo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('permissoes');
    }
};