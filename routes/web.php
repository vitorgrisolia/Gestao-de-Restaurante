<?php

use App\Http\Controllers\CaixaController;
use App\Http\Controllers\CardapioController;
use App\Http\Controllers\CategoriaCardapioController;
use App\Http\Controllers\ComandaController;
use App\Http\Controllers\ContaComandaController;
use App\Http\Controllers\EstornoPagamentoController;
use App\Http\Controllers\ImpressaoProducaoController;
use App\Http\Controllers\ItemCardapioController;
use App\Http\Controllers\MapaSalaoController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\PainelProducaoController;
use App\Http\Controllers\PedidoCancelamentoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PedidoEnvioController;
use App\Http\Controllers\PedidoItemController;
use App\Http\Controllers\PedidoItemStatusController;
use App\Http\Controllers\SetorProducaoController;
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
    Route::post('pedidos/{pedido}/envios', [PedidoEnvioController::class, 'store'])->name('pedidos.envios.store');
    Route::post('pedidos/{pedido}/cancelamentos', [PedidoCancelamentoController::class, 'store'])->name('pedidos.cancelamentos.store');
    Route::get('producao', PainelProducaoController::class)->name('producao.index');
    Route::patch('pedido-itens/{pedido_item}/status', [PedidoItemStatusController::class, 'update'])->name('pedido-itens.status.update');
    Route::post('pedidos/{pedido}/setores-producao/{setor_producao}/impressoes', [ImpressaoProducaoController::class, 'store'])->name('producao.impressoes.store');
    Route::resource('pedido-itens', PedidoItemController::class)
        ->parameters(['pedido-itens' => 'pedido_item'])
        ->only(['update', 'destroy']);
    Route::post('comandas/{comanda}/pagamentos', [PagamentoController::class, 'store'])->name('comandas.pagamentos.store');
    Route::patch('comandas/{comanda}/conta', [ContaComandaController::class, 'update'])->name('comandas.conta.update');
    Route::post('pagamentos/{pagamento}/estornos', [EstornoPagamentoController::class, 'store'])->name('pagamentos.estornos.store');
    Route::resource('caixas', CaixaController::class)->only(['index', 'store', 'update']);
    Route::get('cardapio', CardapioController::class)->name('cardapio.index');
    Route::resource('categorias-cardapio', CategoriaCardapioController::class)
        ->parameters(['categorias-cardapio' => 'categoria_cardapio'])
        ->only(['store', 'update', 'destroy']);
    Route::resource('itens-cardapio', ItemCardapioController::class)
        ->parameters(['itens-cardapio' => 'item_cardapio'])
        ->only(['store', 'update', 'destroy']);
    Route::resource('setores-producao', SetorProducaoController::class)
        ->parameters(['setores-producao' => 'setor_producao'])
        ->only(['index', 'store', 'update', 'destroy']);
    Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::post('usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
});

require __DIR__.'/settings.php';
