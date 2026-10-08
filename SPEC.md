# SPEC.md - Especificação Funcional do MotoCare

## Contexto do Produto
MotoCare é uma plataforma para motociclistas que precisam controlar manutenção preventiva, revisões, despesas com combustível e reparos, além de receber alertas automáticos para reduzir risco mecânico e ansiedade no uso diário.

## Persona Validada
**Lucas "Luke" Almeida**, 21 anos, estudante de TI e estagiário.

### Perfil
- Usa moto de baixa cilindrada todos os dias para trabalhar e estudar.
- Tem rotina acelerada e baixa tolerância a falhas mecânicas inesperadas.

### Dores
1. Confiabilidade nula: depende de memória ou adesivos físicos que se perdem ou apagam.
2. Medo e ansiedade: teme ficar parado na rua por falha mecânica evitável.
3. Descontrole financeiro: perde notas fiscais e não enxerga o gasto mensal real com moto.

### Proposta Única de Valor
Oferecer tranquilidade, segurança ao pilotar e economia financeira com acompanhamento automático de quilometragem e despesas.

## Regras de Negócio
- Cada motocicleta pertence a um usuário.
- Alertas devem ser vinculados a uma motocicleta e disparados conforme quilometragem ou data.
- A manutenção deve registrar serviço, valor, status e quilometragem da execução.
- A despesa deve suportar combustível e reparos com detalhamento financeiro.
- O backend deve responder apenas JSON.

## Mapeamento de Entidades e DER

### usuarios
- `id` [PK]
- `nome`
- `email` [UNIQUE]
- `password`
- `telefone`
- `fcm_token`

### motocicletas
- `id` [PK]
- `usuario_id` [FK]
- `marca`
- `modelo`
- `ano`
- `placa` [UNIQUE]
- `km_atual`

### manutencoes
- `id` [PK]
- `motocicleta_id` [FK]
- `tipo_servico`
- `descricao`
- `valor`
- `status`
- `data`
- `km_servico`

### despesas
- `id` [PK]
- `motocicleta_id` [FK]
- `tipo_despesa`
- `valor`
- `litros`
- `preco_litro`
- `posto`
- `data`

### alertas
- `id` [PK]
- `motocicleta_id` [FK]
- `tipo_alerta`
- `nivel_urgencia`
- `km_limite`
- `data_limite`
- `status`

## Relacionamentos
- `usuarios` 1:N `motocicletas`
- `motocicletas` 1:N `manutencoes`
- `motocicletas` 1:N `despesas`
- `motocicletas` 1:N `alertas`

## Diretrizes Técnicas

- MySQL 8.0 com InnoDB e chaves estrangeiras.
- Tipos numéricos devem preservar precisão financeira.
- Campos de data devem ser definidos de forma consistente para consultas e alertas.
- Qualquer alteração estrutural requer atualização da SPEC, do DER e das migrations correspondentes.
- Implementação atual: Laravel 12.x/PHP 8.2+ no backend; Vue 3/Vite no frontend.

### T1 — Alertas por quilometragem

- Tipos: `troca_oleo` e `revisao`; limite absoluto do odômetro, positivo.
- Verificação automática a cada minuto: `km_atual * 10 >= km_limite * 9`.
- Estados: `pendente`, `notificado` após aceite do FCM e `concluido` após serviço.
- Urgência no envio: `media` antes do limite, `alta` no limite ou acima.
- Falhas e ausência de token preservam o estado pendente para nova tentativa.
- Configuração e operação: [backend/ALERTAS.md](backend/ALERTAS.md).
- Nenhuma alteração de entidades ou relacionamentos do DER nesta entrega.

### T2 — Histórico de manutenções (a implementar)

- O histórico pertence à motocicleta, que pertence ao usuário. Consultas e gravações devem respeitar esse vínculo.
- Utilizar a tabela `manutencoes` já criada na migration; `valor` é `DECIMAL(10,2)`, `data` é uma data e `km_servico` aceita nulo para serviço ainda não executado.
- Cobrir troca de óleo, kit relação, pneus e freios, registrando tipo de serviço, descrição, valor, status, data e quilometragem.
- O contrato de tipos/status e os endpoints serão definidos na implementação da T2 e registrados aqui. Os rótulos `Feito` e `Pendente` e os campos em inglês de `demo.js` são apenas a apresentação do protótipo, não um contrato de API existente.
- Datas enviadas à API devem seguir `AAAA-MM-DD`; a apresentação pode usar `DD/MM/AAAA`.
- Preservar precisão decimal de valores na API e persistência; formatação em reais pertence à interface.
- A autenticação ainda não está implementada. O acesso aos registros do próprio usuário deve ser resolvido antes de disponibilizar o histórico com dados reais.

## Estado da interface e das integrações

- Referência visual: [protótipo publicado](https://open-thumb-85089078.figma.site).
- Rotas demonstrativas: `/`, `/dashboard`, `/alertas`, `/manutencoes`, `/historico`, `/gastos`, `/relatorios`, `/garagem` e `/configuracoes`.
- Os dados atuais vêm de `frontend/src/data/demo.js`; não há persistência, autenticação, exportação real de PDF ou consumo da API nas telas.
- T2 e T3 permanecem pendentes mesmo com suas telas já presentes.
- O DER acima continua válido: não houve alteração de schema para os alertas ou para a entrega visual.
