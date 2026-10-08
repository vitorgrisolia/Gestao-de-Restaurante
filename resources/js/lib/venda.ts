export type TipoVenda = 'unidade' | 'peso' | 'pessoa';

export type DadosVenda = {
    peso_gramas: string;
    cobrar_excesso_carne: boolean | null;
    adicional_carne: string;
};

export function precoPorPorcao(
    tipo: TipoVenda,
    referenciaCentavos: number,
    dados: DadosVenda,
): number {
    const peso = Number(dados.peso_gramas) || 0;
    const base =
        tipo === 'peso'
            ? Math.round((referenciaCentavos * peso) / 1000)
            : referenciaCentavos;
    const adicional = dados.cobrar_excesso_carne
        ? Math.round((Number(dados.adicional_carne) || 0) * 100)
        : 0;

    return base + adicional;
}

export function unidadePreco(tipo: TipoVenda): string {
    return tipo === 'peso' ? '/kg' : tipo === 'pessoa' ? '/pessoa' : '/unidade';
}
