# Support Desk

Projeto acadêmico de central de suporte técnico desenvolvido com PHP, MySQL, HTML, CSS e JavaScript.

## Recursos
- Home focada em planos pagos
- Checkout e pagamento **simulado** (Cartão/PIX)
- Registro de pagamentos e assinaturas no MySQL
- Bloqueio de abertura de chamados sem plano ativo
- Área do cliente e painel administrativo
- Chamados, mensagens, prioridade e status
- Assumir chamados e notificações administrativas

## Banco
1. Importe o banco base se ainda não tiver criado.
2. Para uma instalação existente, execute `database/upgrade_v2.sql` se ainda não executou.
3. Execute `database/upgrade_v3.sql` para adicionar planos, assinaturas e pagamentos.

Nenhum pagamento real é processado. Os dados do cartão servem somente para a demonstração acadêmica e não são enviados a nenhuma operadora.
