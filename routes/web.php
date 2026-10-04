<?php

use App\Http\Controllers\CardapioController;
use App\Http\Controllers\CategoriaCardapioController;
use App\Http\Controllers\ComandaController;
use App\Http\Controllers\ItemCardapioController;
use App\Http\Controllers\MapaSalaoController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PedidoItemController;
use App\Http\Controllers\SetorSalaoController;
use App\Http\Controllers\UsuarioController;
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
    Route::resource('pedido-itens', PedidoItemController::class)
        ->parameters(['pedido-itens' => 'pedido_item'])
        ->only(['update', 'destroy']);
    Route::post('comandas/{comanda}/pagamentos', [PagamentoController::class, 'store'])->name('comandas.pagamentos.store');
    Route::get('cardapio', CardapioController::class)->name('cardapio.index');
    Route::post('categorias-cardapio', [CategoriaCardapioController::class, 'store'])->name('categorias-cardapio.store');
    Route::post('itens-cardapio', [ItemCardapioController::class, 'store'])->name('itens-cardapio.store');
    Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
});

require __DIR__.'/settings.php';
