# DER — MotoCare

O schema é definido pelas migrations em backend/database/migrations. Alterações aditivas de 19/10/2026: despesas.descricao (TEXT nullable) e usuarios.push_subscription (TEXT nullable, criptografado)/push_endpoint_hash (CHAR(64) nullable UNIQUE). Sem recriar tabelas existentes.

22/10/2026: migration aditiva `2026_10_22_000001_add_km_base_to_alertas_table.php` acrescenta `alertas.km_base` (UNSIGNED INTEGER nullable) para o aviso aos 90% do intervalo até km_limite. Pendentes antigos futuros partem do odômetro atual; vencidos e histórico terminal usam base zero, sem alterar status. Alertas apenas por data usam null. Regras completas em SPEC.

~~~mermaid
erDiagram
    usuarios ||--o{ postos : favorita
    postos ||--o{ posto_precos : memoriza
    postos |o--o{ despesas : referencia
    usuarios ||--o{ motocicletas : possui
    motocicletas ||--o{ manutencoes : registra
    motocicletas ||--o{ despesas : registra
    motocicletas ||--o{ alertas : acompanha
    usuarios {
        bigint id PK
        string nome
        string email UK
        string password
        string telefone
        string fcm_token "legado sem uso"
        text push_subscription "criptografado"
        string push_endpoint_hash UK
    }
    motocicletas {
        bigint id PK
        bigint usuario_id FK
        string marca
        string modelo
        smallint ano
        string placa UK
        integer km_atual
        decimal capacidade_tanque "opcional"
        decimal volume_reserva "opcional"
    }
    manutencoes {
        bigint id PK
        bigint motocicleta_id FK
        string tipo_servico
        text descricao
        decimal valor
        string status
        date data
        integer km_servico
        string natureza "preventiva/corretiva opcional"
    }
    despesas {
        bigint id PK
        bigint motocicleta_id FK
        string tipo_despesa
        text descricao
        decimal valor
        decimal litros
        decimal preco_litro
        string posto
        bigint posto_id FK "opcional; SET NULL"
        string combustivel "opcional"
        integer km_abastecimento "opcional"
        boolean tanque_cheio "opcional"
        date data
    }
    alertas {
        bigint id PK
        bigint motocicleta_id FK
        string tipo_alerta
        string nivel_urgencia
        string origem "chuva_lavagem opcional"
        integer km_limite
        integer km_base "referência do intervalo"
        date data_limite
        string status
    }
    postos {
        bigint id PK
        bigint usuario_id FK
        string nome "único por conta"
    }
    posto_precos {
        bigint id PK
        bigint posto_id FK
        string combustivel "único por posto"
        decimal preco_litro
    }
~~~

23/10/2026 — complemento aditivo: `2026_10_23_000001_add_motorcycle_insights.php`. Todos os novos campos em tabelas existentes são opcionais, sem backfill. Postos/preços têm timestamps e unicidade composta; exclusão de usuário/posto remove preços, exclusão de favorito apenas anula a referência nos gastos, preservando seu texto e valores. Não executar rollback desta extensão em banco com novos registros.

Todos os registros do domínio têm timestamps. FKs do domínio têm exclusão em cascata; excluir moto apaga seus históricos/alertas. Campos opcionais e precisão decimal: consultar [SPEC.md](SPEC.md). Tabelas auxiliares de sessão, cache, jobs e recuperação de senha seguem as migrations Laravel e não representam funcionalidades de recuperação de senha implementadas na interface.

Escopo vigente em 23/10/2026: T1/T2/T3 ativos, incluindo despesas e relatórios. A T3 reutiliza despesas e descricao já existentes; nenhuma migration nova, rollback ou exclusão de dados nesta retomada. FEATURE_FINANCEIRO=true por padrão; false permite desativação funcional sem mudar o schema. O adiamento de 19/10 foi superado pelo usuário.

19/10/2026: Web Push substitui Firebase. Migration aditiva inclui push_subscription e push_endpoint_hash em usuarios. O campo fcm_token é legado sem uso; não há migração possível de token FCM para inscrição Push API e o usuário precisa ativar o navegador novamente.
