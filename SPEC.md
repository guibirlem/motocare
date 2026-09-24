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
- Tipos numéricos devem ser escolhidos para preservar precisão financeira.
- Campos de data devem ser definidos de forma consistente para consultas e alertas.
- Qualquer alteração estrutural requer atualização da SPEC e das migrations correspondentes.