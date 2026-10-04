<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUsuarioRequest;
use App\Models\User;
use App\PapelUsuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UsuarioController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->papel->podeAdministrarCadastros(), 403);

        return Inertia::render('usuarios/index', [
            'usuarios' => User::query()
                ->select(['id', 'name', 'email', 'papel', 'email_verified_at', 'created_at'])
                ->orderBy('name')
                ->get(),
            'papeis' => array_map(
                fn (PapelUsuario $papel): array => ['valor' => $papel->value, 'nome' => $papel->nome()],
                PapelUsuario::cases(),
            ),
        ]);
    }

    public function store(StoreUsuarioRequest $request): RedirectResponse
    {
        $usuario = User::create(
            $request->safe()->only(['name', 'email', 'password', 'papel']),
        );

        $usuario->forceFill(['email_verified_at' => now()])->save();

        return back()->with('success', 'Usuário cadastrado com sucesso.');
    }
}
