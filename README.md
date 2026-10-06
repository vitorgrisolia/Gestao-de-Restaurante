# Gestão Restaurante

Sistema web para apoiar a operação de um restaurante, começando pelo salão, mesas, comandas, cardápio e registro de pedidos. O projeto usa Laravel, React, Inertia e TypeScript e está sendo desenvolvido incrementalmente a partir da análise de negócio.

> **Estado atual:** os fluxos de salão, pedidos, produção e pagamento integral com fechamento da comanda estão funcionais. Pagamentos parciais, estoque, relatórios e integração fiscal pertencem às próximas fases.

## Objetivo

Centralizar a operação do restaurante em uma aplicação responsiva que permita acompanhar mesas, abrir comandas, registrar a quantidade de pessoas, selecionar produtos e conservar os valores praticados no momento de cada pedido.

O produto completo pretende abranger salão, balcão, retirada, produção, contas, pagamentos, caixa, estoque, relatórios e integrações. Este documento separa o que já funciona do que ainda está planejado.

## Funcionalidades implementadas

### Autenticação

- login, logout e verificação de e-mail;
- recuperação e alteração de senha;
- autenticação em dois fatores e passkeys;
- edição e exclusão do perfil.

### Papéis de usuário

| Papel        | Acesso implementado atualmente                   |
| ------------ | ------------------------------------------------ |
| Proprietário | Gerencia salão, cardápio, usuários e pagamentos  |
| Gerente      | Gerencia salão, pedidos e recebe pagamentos      |
| Caixa        | Abre comandas, registra pedidos e recebe valores |
| Atendente    | Abre comandas e registra pedidos                 |
| Cozinha      | Opera o painel e atualiza o preparo dos itens    |
| Estoque      | Reservado para o futuro módulo de estoque        |

As permissões ainda são regras simples no enum `PapelUsuario`. Capacidades granulares fazem parte do roadmap.

### Salão, mesas e comandas

- cadastro e alteração de setores e mesas;
- capacidade, identificação e setor de cada mesa;
- mapa visual e responsivo do salão;
- estados livre, ocupada, reservada e aguardando pagamento;
- abertura de comanda apenas em mesa livre;
- registro do usuário, horário e quantidade de pessoas;
- transação e bloqueio para impedir duas comandas ativas na mesma mesa;
- mudança automática da mesa para ocupada;
- acesso à comanda clicando em uma mesa ocupada.

### Cardápio

- categorias com nome, descrição, ativação e ordem;
- produtos com nome, descrição, preço, imagem, disponibilidade e ordem;
- valores armazenados em centavos;
- dados demonstrativos para entradas, pratos, bebidas e sobremesas;
- models, factories, seeders e testes.

O proprietário possui uma tela administrativa para cadastrar categorias e itens, definir preço, descrição, imagem, ordem, situação e disponibilidade. Os dados demonstrativos continuam disponíveis pelos seeders.

- edição e exclusão protegida de categorias e itens pelo proprietário;

### Administração de usuários

- tela exclusiva do proprietário;
- cadastro de nome, e-mail, senha inicial e papel de acesso;
- papéis disponíveis: proprietário, gerente, caixa, atendente, cozinha e estoque;
- senha armazenada com hash;
- conta criada como verificada e pronta para entrar;
- listagem da equipe cadastrada;
- proteção das rotas mesmo quando acessadas diretamente.

### Pedidos

- tela da comanda com mesa e quantidade de pessoas;
- cardápio organizado por categoria;
- seleção de quantidade e observação por item;
- observação geral do pedido;
- cálculo visual de subtotais e total;
- envio transacional do pedido e dos itens para produção;
- bloqueio de reenvio e de alterações após o envio;
- cancelamento com motivo obrigatório, responsável e horário;
- exclusão dos itens cancelados do total da comanda;
- gravação inicial como rascunho;
- histórico dos pedidos da comanda;
- registro do usuário responsável;
- preservação histórica do nome e preço unitário;
- validação de produto existente e disponível;
- rejeição de pedido vazio ou feito em comanda fechada;
- criação transacional de pedido e itens;
- consulta do preço no servidor, ignorando preços enviados pelo navegador.

Estados já modelados:

```text
rascunho → enviado → em_preparo → pronto → entregue
                                      ↘ cancelado
```

O envio, o cancelamento e o acompanhamento da produção estão ligados à interface, com responsáveis, horários e controle de reimpressão.

### Pagamento e fechamento

- resumo com número de pedidos, pessoas e valor total da comanda;
- formas de pagamento: dinheiro, Pix, cartão de débito e cartão de crédito;
- recebimento permitido somente a proprietário, gerente e caixa;
- total calculado no servidor com os preços históricos dos itens;
- itens cancelados excluídos da cobrança;
- registro do valor, forma, responsável e horário do pagamento;
- bloqueio de pagamento duplicado ou de comanda sem itens cobráveis;
- fechamento da comanda e liberação automática da mesa;
- pagamento, fechamento e liberação executados na mesma transação.

## Fluxo disponível

1. Entre no sistema e acesse **Salão e mesas**.
2. Clique em uma mesa livre.
3. Informe a quantidade de pessoas e abra a comanda.
4. Clique novamente na mesa, agora ocupada.
5. Selecione produtos, quantidades e observações.
6. Confira os respectivos valores e o total.
7. Registre o pedido.
8. O servidor consulta os preços oficiais e salva pedido e itens.
9. O pedido aparece no histórico da comanda.
10. Confira o total acumulado em **Fechamento da comanda**.
11. Selecione a forma de pagamento e confirme o recebimento.
12. O sistema registra o pagamento, fecha a comanda e libera a mesa.
13. A tela retorna automaticamente ao mapa do salão.

## Regras de negócio aplicadas

- uma mesa não pode ter duas comandas ativas;
- apenas uma mesa ativa e livre pode ser aberta;
- a comanda registra responsável, horário e quantidade de pessoas;
- somente papéis autorizados gerenciam o salão ou registram pedidos;
- um pedido deve possuir pelo menos um item;
- a quantidade de cada item deve estar entre 1 e 99;
- um produto aparece uma única vez no mesmo pedido;
- produtos indisponíveis não podem ser vendidos;
- pedidos só entram em comandas abertas;
- o navegador não controla o preço persistido;
- itens guardam nome e preço históricos;
- valores monetários são inteiros em centavos;
- abertura de comanda e criação de pedido usam transações;
- somente proprietário, gerente e caixa recebem pagamentos;
- o valor pago é calculado pelo servidor e ignora itens cancelados;
- comandas vazias, fechadas ou canceladas não podem ser pagas;
- pagamento, fechamento da comanda e liberação da mesa são atômicos;
- chaves estrangeiras protegem o histórico financeiro e operacional.

## Tecnologias

### Backend

- PHP 8.3 ou superior; ambiente atual validado com PHP 8.5;
- Laravel 13, Fortify, Wayfinder e Inertia Laravel 3;
- Eloquent ORM e SQLite local;
- PHPUnit 12, Larastan/PHPStan e Laravel Pint.

### Frontend

- React 19, TypeScript e Inertia React 3;
- Tailwind CSS 4;
- Radix UI, padrão shadcn/ui e Lucide React;
- Vite Plus/Vite 8.

### Ambiente

- Docker Desktop;
- imagem `composer:2` para PHP, Composer, Artisan e servidor;
- Node.js e npm instalados no contêiner durante o build.

## Arquitetura

```text
app/
├── Actions/          Regras transacionais: AbrirComanda, CriarPedido e FinalizarComanda
├── Http/
│   ├── Controllers/  Entrada HTTP e páginas Inertia
│   └── Requests/     Autorização, validação e mensagens
├── Models/           Entidades e relacionamentos Eloquent
├── Policies/         Autorizações do salão
└── *.php              Enums de papéis, estados e status

database/
├── factories/        Dados para testes
├── migrations/       Estrutura e integridade do banco
└── seeders/          Usuário, salão, mesas e cardápio demonstrativos

resources/js/
├── components/       Componentes compartilhados
├── layouts/          Layouts do sistema
├── pages/
│   ├── comandas/show.tsx
│   └── salao/index.tsx
├── routes/           Rotas TypeScript geradas pelo Wayfinder
└── types/            Tipos compartilhados

tests/
├── Feature/          Fluxos HTTP, segurança, persistência e models
└── Unit/             Lógica isolada
```

- **Form Requests:** autorização e validação.
- **Controllers:** coordenação HTTP e respostas Inertia.
- **Actions:** regras transacionais e concorrência.
- **Models:** casts, relações e comportamento de domínio.
- **React:** interação e apresentação; nunca define o preço persistido.

## Banco de dados

| Tabela                      | Responsabilidade                                   |
| --------------------------- | -------------------------------------------------- |
| `users`                     | Usuários, credenciais e papel                      |
| `setores_salao`             | Ambientes do restaurante                           |
| `mesas`                     | Capacidade, setor, estado e ativação               |
| `comandas`                  | Atendimento de uma mesa                            |
| `categorias_cardapio`       | Organização do cardápio                            |
| `itens_cardapio`            | Produtos, preços e disponibilidade                 |
| `pedidos`                   | Pedidos vinculados à comanda                       |
| `pedido_itens`              | Quantidade, preço histórico, status e cancelamento |
| `pagamentos`                | Valor, forma, responsável e horário do recebimento |
| `sessions`, `cache`, `jobs` | Infraestrutura Laravel                             |
| `passkeys`                  | Credenciais de passkey                             |

```text
SetorSalao  1 ── N Mesa
Mesa         1 ── N Comanda
Comanda      1 ── N Pedido
Comanda      1 ── N Pagamento
Pedido       1 ── N PedidoItem
Categoria    1 ── N ItemCardapio
ItemCardapio 1 ── N PedidoItem
```

## Rotas operacionais

Todas exigem autenticação e e-mail verificado.

| Método    | Caminho                          | Finalidade                 |
| --------- | -------------------------------- | -------------------------- |
| GET       | `/dashboard`                     | Visão geral                |
| GET       | `/salao`                         | Mapa do salão              |
| POST      | `/setores-salao`                 | Cadastrar setor            |
| PUT/PATCH | `/setores-salao/{setor}`         | Alterar setor              |
| POST      | `/mesas`                         | Cadastrar mesa             |
| PUT/PATCH | `/mesas/{mesa}`                  | Alterar mesa               |
| POST      | `/comandas`                      | Abrir comanda              |
| GET       | `/comandas/{comanda}`            | Tela da comanda            |
| POST      | `/comandas/{comanda}/pedidos`    | Registrar pedido           |
| POST      | `/comandas/{comanda}/pagamentos` | Pagar e fechar comanda     |
| GET       | `/cardapio`                      | Administração do cardápio  |
| POST      | `/categorias-cardapio`           | Cadastrar categoria        |
| POST      | `/itens-cardapio`                | Cadastrar item do cardápio |
| GET       | `/usuarios`                      | Administração de usuários  |
| POST      | `/usuarios`                      | Cadastrar usuário          |

## Instalação com Docker Desktop

Execute no PowerShell.

```powershell
cd C:\Users\griso\Documents\projetos\GestaoRestaurante

# Dependências PHP
docker run --rm -v "${PWD}:/app" -w /app composer:2 sh -lc "composer install"

# Ambiente e banco
Copy-Item .env.example .env
New-Item database/database.sqlite -ItemType File -Force
docker run --rm -v "${PWD}:/app" -w /app composer:2 php artisan key:generate
docker run --rm -v "${PWD}:/app" -w /app composer:2 php artisan migrate --seed

# Dependências e build frontend
docker run --rm -v "${PWD}:/app" -w /app composer:2 sh -lc "apk add --no-cache nodejs npm && npm ci && npm run build"

# Servidor: primeira execução
docker run --name gestao-restaurante-app -d -p 8000:8000 -v "${PWD}:/app" -w /app composer:2 php artisan serve --host=0.0.0.0 --port=8000
```

Se `.env` e `database/database.sqlite` já existirem, não os recrie para não perder configurações ou dados.

Próximas execuções:

```powershell
docker start gestao-restaurante-app
```

Acesse `http://localhost:8000`.

```powershell
# Parar
docker stop gestao-restaurante-app

# Logs
docker logs -f gestao-restaurante-app
```

## Usuário demonstrativo

| Campo  | Valor                           |
| ------ | ------------------------------- |
| E-mail | `proprietario@restaurante.test` |
| Senha  | `password`                      |
| Papel  | Proprietário                    |

Use essas credenciais somente em desenvolvimento.

## Dados demonstrativos

- setores **Salão principal** e **Varanda**;
- quatro mesas por setor;
- categorias **Entradas**, **Pratos**, **Bebidas** e **Sobremesas**;
- oito produtos demonstrativos;
- um usuário proprietário.

Comandas e pedidos não são criados automaticamente, pois representam operação real.

## Comandos úteis

```powershell
# Novas migrations
docker exec gestao-restaurante-app php artisan migrate

# Limpar caches
docker exec gestao-restaurante-app php artisan optimize:clear

# Regenerar rotas TypeScript; --with-form é obrigatório
docker exec gestao-restaurante-app php artisan wayfinder:generate --with-form --no-interaction

# Recompilar frontend
docker run --rm -v "${PWD}:/app" -w /app composer:2 sh -lc "apk add --no-cache nodejs npm && npm run build"
```

Após alterações visuais, use `Ctrl + F5` caso o navegador mantenha arquivos antigos.

## Testes e qualidade

```powershell
# Testes PHP
docker run --rm -v "${PWD}:/app" -w /app composer:2 php artisan test --compact

# Teste específico
docker run --rm -v "${PWD}:/app" -w /app composer:2 php artisan test --compact tests/Feature/PedidoControllerTest.php

# Formatação PHP
docker run --rm -v "${PWD}:/app" -w /app composer:2 sh -lc "vendor/bin/pint --dirty --format agent"

# Análise estática PHP
docker run --rm -v "${PWD}:/app" -w /app composer:2 sh -lc "vendor/bin/phpstan analyse --no-progress"

# Frontend: lint e tipos
docker run --rm -v "${PWD}:/app" -w /app composer:2 sh -lc "apk add --no-cache nodejs npm && npm run check && npm run types:check"
```

## Segurança e integridade

- CSRF, hash de senhas, verificação de e-mail, 2FA e passkeys;
- autorização por papel, Form Requests e policies existentes;
- validação server-side com mensagens em português;
- mass assignment controlado por `Fillable`;
- Eloquent e parâmetros vinculados;
- transações e `lockForUpdate()`;
- preço definido exclusivamente no servidor;
- histórico protegido por chaves estrangeiras.

Antes da produção ainda são necessários HTTPS, gestão de segredos, backups automáticos, restauração testada, monitoramento, auditoria completa, revisão LGPD e permissões granulares.

## Roadmap

### Salão, cardápio e pedidos

- [x] autenticação e papéis iniciais;
- [x] setores, mesas e mapa visual;
- [x] abertura segura da comanda com quantidade de pessoas;
- [x] categorias e itens do cardápio no domínio e banco;
- [x] registro de pedido com valores históricos;
- [x] tela de seleção, observações, totais e histórico;
- [x] administração visual para cadastrar categorias e itens;
- [x] cadastro de usuários e papéis pelo proprietário;
- [x] alterar e remover itens em rascunho;
- [x] enviar à produção e cancelar com auditoria.

### Produção

- [x] setores de cozinha e bar, com CRUD exclusivo do proprietário;
- [x] painel recebido/em preparo/pronto/entregue;
- [x] responsáveis, horários e pedidos atrasados;
- [x] reenvio e impressão sem duplicidade.

### Conta, pagamentos e caixa

- [x] subtotal, serviço, couvert, desconto e acréscimo;
- [x] divisão por pessoa, item ou valor;
- [x] múltiplos pagamentos, saldo e troco;
- [x] pagamento integral com valor calculado no servidor;
- [x] quitação e liberação automática da mesa;
- [x] abertura, fechamento e estorno de caixa.

### Estoque e gestão

- [x] ficha técnica, ingredientes e unidades;
- [x] entradas, perdas, ajustes e inventário;
- [x] baixa automática e custos;
- [x] relatórios, exportações e auditoria.

### Infraestrutura e integrações

- [ ] impressão térmica homologada _(layout térmico de 80 mm, confirmação e idempotência implementados; falta homologar no equipamento físico)_;
- [x] backup, restauração e monitoramento;
- [ ] integração fiscal após validação contábil e homologação;
- [ ] delivery, marketplaces, reservas e multiunidade em fases futuras.

## Limitações atuais

- uma empresa e uma unidade;
- sem operação offline completa;
- sem edição ou exclusão de usuários pela interface;
- sem pagamento parcial, divisão da conta, troco, desconto ou taxa de serviço;
- sem estoque, relatórios ou emissão fiscal;
- ainda não pronto para produção comercial.

## Licença

O código-base do Laravel Starter Kit usa a licença MIT. Antes de distribuição comercial, revise dependências, ativos e a licença final do produto.
