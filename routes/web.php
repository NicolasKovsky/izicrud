<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\PermissoesController;

// Controllers



Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/sempermissao', function () {
    return Inertia::render('SemPermissao');
})->middleware(['auth', 'verified'])->name('sem-permissao');

Route::group([
    'middleware' => ['auth', 'verified', 'permissao:0'],
], function () {
    Route::controller(PermissoesController::class)->group(function () {
        Route::get('/permissoes', 'index')->name('permissoes.index');
        Route::get('/permissoes/create', 'create')->name('permissoes.create');
        Route::post('/permissoes', 'store')->name('permissoes.store');
        Route::get('/permissoes/{permissoes}/edit', 'edit')->name('permissoes.edit');
        Route::put('/permissoes/{permissoes}', 'update')->name('permissoes.update');
        Route::delete('/permissoes/{permissoes}', 'destroy')->name('permissoes.destroy');
        Route::get('/permissoes/atribuir', 'atribuirPermissoes')->name('permissoes.atribuir');
        Route::post('/permissoes/atribuir', 'atribuirStore')->name('permissoes.atribuir.store');
        Route::get('/permissoes/usuarios', 'listUsuarios')->name('permissoes.usuarios');
    });
});


// Rotas


require __DIR__.'/settings.php';
