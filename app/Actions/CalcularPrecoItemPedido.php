<?php

namespace App\Actions;

use App\TipoVenda;
use Illuminate\Validation\ValidationException;

class CalcularPrecoItemPedido
{
    public function handle(TipoVenda $tipo, int $precoReferencia, ?int $pesoGramas, bool $permiteExcesso, ?bool $cobrarExcesso, int $adicionalCentavos, string $prefixo = ''): int
    {
        if ($tipo === TipoVenda::Peso && ($pesoGramas === null || $pesoGramas < 1 || $pesoGramas > 10000)) {
            throw ValidationException::withMessages([$prefixo.'peso_gramas' => 'Informe o peso líquido por porção, entre 1 e 10000 gramas.']);
        }
        if ($tipo !== TipoVenda::Peso && $pesoGramas !== null) {
            throw ValidationException::withMessages([$prefixo.'peso_gramas' => 'Este produto não é vendido por peso.']);
        }
        if ($permiteExcesso && $cobrarExcesso === null) {
            throw ValidationException::withMessages([$prefixo.'cobrar_excesso_carne' => 'Escolha se o excesso de carne será cobrado ou não.']);
        }
        if ($cobrarExcesso && ! $permiteExcesso) {
            throw ValidationException::withMessages([$prefixo.'cobrar_excesso_carne' => 'Este produto não permite cobrança de excesso de carne.']);
        }
        if ($adicionalCentavos < 0 || $adicionalCentavos > 9999999 || ($cobrarExcesso && $adicionalCentavos === 0)) {
            throw ValidationException::withMessages([$prefixo.'adicional_carne' => 'Informe um valor válido e maior que zero para o excesso de carne.']);
        }
        if (! $cobrarExcesso && $adicionalCentavos !== 0) {
            throw ValidationException::withMessages([$prefixo.'adicional_carne' => 'Selecione a cobrança de excesso de carne para informar um adicional.']);
        }

        $precoBase = $tipo === TipoVenda::Peso
            ? intdiv($precoReferencia * (int) $pesoGramas + 500, 1000)
            : $precoReferencia;

        return $precoBase + $adicionalCentavos;
    }
}
