<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permissao_id')->default(99)->index();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // Insert a default user
        DB::table('users')->insert([
            'name' => 'Admin Developer',
            'permissao_id' => 1,
            'email' => 'adm@adm.com',
            'password' => bcrypt('adm@adm.com'), // Use bcrypt to hash the password
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // Insert a default user
        DB::table('users')->insert([
            'name' => 'Alpha Developer',
            'permissao_id' => 2,
            'email' => 'admin@admin.com',
            'password' => bcrypt('admin@admin.com'), // Use bcrypt to hash the password
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // Insert a default user
        DB::table('users')->insert([
            'name' => 'Delta Developer',
            'permissao_id' => 3,
            'email' => 'dev@dev.com',
            'password' => bcrypt('dev@dev.com'), // Use bcrypt to hash the password
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // Insert a default user
        DB::table('users')->insert([
            'name' => 'Visita Developer',
            'permissao_id' => 99,
            'email' => 'vz@vz.com',
            'password' => bcrypt('vz@vz.com'), // Use bcrypt to hash the password
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
