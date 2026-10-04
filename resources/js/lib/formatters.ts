const moedaBrasileira = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
});

export function formatarCentavos(valorCentavos: number): string {
    return moedaBrasileira.format(valorCentavos / 100);
}
