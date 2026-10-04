export type Produto = {
    id: number;
    nome: string;
    descricao: string | null;
    preco_centavos: number;
};

export type Categoria = {
    id: number;
    nome: string;
    itens: Produto[];
};

export type ItemPedido = {
    id: number;
    nome_item: string;
    quantidade: number;
    preco_unitario_centavos: number;
    observacao: string | null;
    status: string;
};

export type Pedido = {
    id: number;
    status: string;
    criado_por: { name: string };
    itens: ItemPedido[];
};

export type ItemNovoPedido = {
    item_cardapio_id: number;
    quantidade: number;
    observacao: string;
};

export type FormaPagamento = {
    valor: string;
    nome: string;
};

export type ComandaPageProps = {
    comanda: {
        id: number;
        quantidade_pessoas: number;
        mesa: { numero: string };
    };
    categorias: Categoria[];
    pedidos: Pedido[];
    totalComandaCentavos: number;
    podeFecharComanda: boolean;
    formasPagamento: FormaPagamento[];
};
