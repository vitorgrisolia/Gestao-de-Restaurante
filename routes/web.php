<?php

use App\Http\Controllers\ComandaController;
use App\Http\Controllers\MapaSalaoController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\SetorSalaoController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::get('salao', MapaSalaoController::class)->name('salao.index');
    Route::resource('setores-salao', SetorSalaoController::class)
        ->parameters(['setores-salao' => 'setor_salao'])
        ->only(['store', 'update']);
    Route::resource('mesas', MesaController::class)->only(['store', 'update']);
    Route::post('comandas', [ComandaController::class, 'store'])->name('comandas.store');
    Route::get('comandas/{comanda}', [ComandaController::class, 'show'])->name('comandas.show');
    Route::post('comandas/{comanda}/pedidos', [PedidoController::class, 'store'])->name('comandas.pedidos.store');
});

require __DIR__.'/settings.php';
